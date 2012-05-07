<?php
/**
 * @author      Vadim Semenov <i@sedictor.ru>
 * @date        5/6/12
 * @time        10:48 PM
 * @description HyperLogLog algorithm implementation
 * @url         http://algo.inria.fr/flajolet/Publications/FlFuGaMe07.pdfs
 */

function generateWords($count) {
    $result = array();

    while ($count > 0) {
        $word = '';
        for ($j = 0; $j < rand(1, 8); $j++) { // from 1char to 8chars
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

class HyperLogLog {
    const HASH_LENGTH = 32; // bites
    const HASH_K = 5; // HASH_LENGTH = 2 ^ HASH_K
    const ALPHA = 0.697122946; // 1 / (32 * integral(0,inf)( (log2(1+1/(1+x)))^32 dx))

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
            $w = self::getBites($h, self::HASH_K + 1, self::HASH_LENGTH);
            $w = self::scan1($w);

            if (!isset($M[$j]) || $M[$j] < $w) {
                $M[$j] = $w;
            }
        }

        $Z = 0;
        for ($i = 1; $i <= self::HASH_LENGTH; $i++) {
            if (isset($M[$i]) && $M[$i] != 0) {
                $Z += 1 / pow(2, $M[$i]);
            } else {
                $Z += 1;
            }
        }
        $Z = self::ALPHA * self::HASH_LENGTH * self::HASH_LENGTH / $Z;

        return floor($Z);
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
echo HyperLogLog::count($words) . "\n";
echo 'time: ' . (microtime(1) - $s) . " sec\n";
echo 'mem: ' . (memory_get_usage(1) - $m) . "\n";