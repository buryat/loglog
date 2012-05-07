<?php
/**
 * @author      Vadim Semenov <i@sedictor.ru>
 * @date        5/6/12
 * @time        10:29 PM
 * @description LogLog algorithm implementation
 * @url         http://algo.inria.fr/flajolet/Publications/DuFl03-LNCS.pdf
 */

function generateWords($count) {
    $result = array();

    while ($count > 0) {
        $word = '';
        for ($j = 0; $j < rand(1, 8); $j++) { // from 'a' to 'abcdefgh'
            $word .= chr(rand(97, 122)); // a-z
        }

        for ($i = 0; $i < rand(1, 100); $i++) {
            $result[] = $word;
            $count--;
        }
    }

    return $result;
}

function cardinality($arr) {
    $arr = array_count_values($arr);
    return count($arr);
}

class LogLog {
    const HASH_LENGTH = 32; // in bites
    const HASH_K = 5; // HASH_LENGTH = 2 ^ HASH_K
    const ALPHA = 0.77308249784697296; // (Gamma(-1/32) * (2^(-1/32) - 1) / ln2)^(-32)

    /**
     * Jenkins hash function
     *
     * @url http://en.wikipedia.org/wiki/Jenkins_hash_function
     *
     * @static
     * @param $str
     * @return int Hash
     */
    private static function hash($str) {
        $hash = 0;

        for ($i = 0, $l = strlen($str); $i < $l; $i++) {
            $hash += ord($str[$i]);
            $hash += $hash << 10;
            $hash ^= $hash >> 6;
        }

        $hash += $hash << 3;
        $hash ^= $hash >> 6;
        $hash += $hash << 16;

        return $hash;
    }

    /**
     * Offset of first 1-bit
     *
     * @example 00010 => 4
     *
     * @static
     * @param int $bites
     * @return int
     */
    private static function scan1($bites) {
        if ($bites == 0) {
            return self::HASH_LENGTH - self::HASH_K;
        }

        $offset = floor(log($bites, 2));
        $offset = self::HASH_LENGTH - self::HASH_K - $offset;

        return $offset;
    }

    /**
     * @static
     *
     * @param string $bites
     * @param int    $start >=1
     * @param int    $end   <= HASH_LENGTH
     *
     * @return int slice of $bites
     */
    private static function getBites($bites, $start, $end) {
        $r = $bites >> (self::HASH_LENGTH - $end);
        $r = $r & (pow(2, $end - $start + 1) - 1);

        return $r;
    }

    /**
     * @static
     * @param $arr
     * @return int Number of unique items in $arr
     */
    public static function count($arr) {
        $M = array();

        foreach ($arr as $v) {
            $h = self::hash($v);
            $j = self::getBites($h, 1, self::HASH_K) + 1;
            $k = self::getBites($h, self::HASH_K + 1, self::HASH_LENGTH);
            $k = self::scan1($k);

            if (!isset($M[$j]) || $M[$j] < $k) {
                $M[$j] = $k;
            }
        }

        $E = 0;
        foreach ($M as $m) {
            $E += $m;
        }
        $E /= self::HASH_LENGTH;
        $E = self::ALPHA * self::HASH_LENGTH * pow(2, $E);

        return floor($E);
    }
}

$words = generateWords(10000);
echo "Number of words\n" . count($words) . "\n";

echo "------\nPrecision\n";

$s = microtime(1);
$m = memory_get_usage(1);
echo cardinality($words) . "\n";
echo 'time: ' . (microtime(1) - $s) . " sec\n";
echo 'mem: ' . (memory_get_usage(1) - $m) . "\n";

echo "------\nLogLog\n";

$s = microtime(1);
$m = memory_get_usage(1);
echo LogLog::count($words) . "\n";
echo 'time: ' . (microtime(1) - $s) . " sec\n";
echo 'mem: ' . (memory_get_usage(1) - $m) . "\n";