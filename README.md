# LogLog and HyperLogLog algorithms implementation

These two algorithms calculate the cardinality of the data set (i.e. number of distinct elements in the data set).

HyperLogLog is further development of LogLog. They both work very fast and use small amount of memory, but they are prediction algorithms, so there is always error.

## Fields of application

Any task which requires fast calculation of unique items in huge data set with limited memory usage and tolerant to non 100% precision.

## Idea

The main idea is to map all elements in the set with hash function and divide all elements by offset of 1bit in hashed value.

For example, hash function returns fixed-length values `m = 8bits`, param `2^k = m`, so `k = 3`

`elements[0] → hash(elements[0]) = 00010101`

`elements[1] → hash(elements[1]) = 01000110`

Set `M = [0, 0, 0, 0, 0, 0, 0, 0], M.length = m`

First `k` bits of hashed value will be the index (bucket) in `M`, and get offset of a first 1bit in others bits of hashed value.

So, for `elements[0]` `index = 0` and `scan1 = 1`, `elements[1]` `index = 2` and `scan1 = 3`.

Then `M[index] = max(M[index], scan1)`

After all you have M with smallest hashes for each bucket.

Then you need to somehow summarize elements in M and multiply it on coefficients which depends on hash length. And the result will be estimation of cardinality of the data set.

As you can see, algorithms use small amount of memory because they keep only `M` while execution and `M` has fixed length itself.
Also it's clear that range of hash values must be greater than number of unique elements in the data set, otherwise some elements will have same hash.

### Sources

1. Marianne Durand and Philippe Flajolet. Loglog Counting of Large Cardinalities. G. Di Battista and U. Zwick (Eds.): ESA 2003, LNCS 2832, pp. 605–617, 2003.
[http://algo.inria.fr/flajolet/Publications/DuFl03-LNCS.pdf](http://algo.inria.fr/flajolet/Publications/DuFl03-LNCS.pdf)

2. Olivier Gandouet and Alain Jean-Marie. LogLog counting for the estimation of IP trafﬁc. Fourth Colloquium on Mathematics and Computer Science, DMTCS proc. AG, pp. 119–128, 2006. 
[http://mathinfo06.iecn.u-nancy.fr/papers/dmAG119-128.pdf](http://mathinfo06.iecn.u-nancy.fr/papers/dmAG119-128.pdf)

3. Philippe Flajolet, Éric Fusy, Olivier Gandouet and Frédéric Meunier. HyperLogLog: the analysis of a near-optimal cardinality estimation algorithm. 2007 Conference on Analysis of Algorithms, DMTCS proc. AH, pp. 127–146, 2007.
[http://algo.inria.fr/flajolet/Publications/FlFuGaMe07.pdf](http://algo.inria.fr/flajolet/Publications/FlFuGaMe07.pdf)