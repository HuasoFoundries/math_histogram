# Math_Histogram

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/HuasoFoundries/math_histogram/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/HuasoFoundries/math_histogram/?branch=master) [![Build Status](https://scrutinizer-ci.com/g/HuasoFoundries/math_histogram/badges/build.png?b=master)](https://scrutinizer-ci.com/g/HuasoFoundries/math_histogram/build-status/master)



This Package is a mix of of PEAR's [Math_Stats](http://pear.php.net/package/Math_Stats) and [Math_Histogram](http://pear.php.net/package/Math_Histogram)

To test, run:

```
make install
make test
```


### Math Stats

Package to calculate statistical parameters of numerical arrays
of data. The data can be in a simple numerical array, or in a 
cummulative numerical array. A cummulative array, has the value
as the index and the number of repeats as the value for the
array item, e.g. $data = [ 3 => 4, 2.3 => 5, 1.25 => 6, 0.5 => 3].
Nulls can be rejected, ignored or handled as zero values.


### Math Histogram

Package: Math_Histogram

These classes can be used to calculate histogram distributions
of data sets.

The Math_Histogram class computes histograms from unidimensional
data sets. These are also known as 2D historgrams, and can be
computed in the regular binned frequency or as cummulative frequency.

Similarly, the Math_Histogram3D and Math_Histogram4D classes compute
distributions from bi- and tri-dimensional data sets (respectively),
both in regular frequency and cummulative frequency modes.

This package requires the Math_Stats package, so if you do not have it
installed, you need to do:

	pear install Math_Stats

before you can start using Math_Histogram in your scripts.

--- Jesus M. Castagnetto
