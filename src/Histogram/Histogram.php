<?php
namespace HuasoFoundries\Histogram;

class Histogram extends AbstractHistogram
{

    /**
     * Constructor
     *
     * @access  public
     * @param   optional    int $type   one of HISTOGRAM_SIMPLE or HISTOGRAM_CUMMULATIVE
     * @param   optional    int $nbins  number of bins to use
     * @param   optional    float   $rangeLow   lowest value to use for bin frequency calculation
     * @param   optional    float   $rangeHigh   highest value to use for bin frequency calculation
     * @return  object  Histogram
     *
     * @see setBinOptions()
     * @see Math_AbstractHistogram::setType()
     * @see Math_AbstractHistogram
     */
    public function __construct($type = self::HISTOGRAM_SIMPLE, $nbins = -1, $rangeLow = null, $rangeHigh = null)
    {

        $this->setType($type);
        $this->setBinOptions($nbins, $rangeLow, $rangeHigh);
    }

    /**
     * Sets the binning options. Overrides parent's method.
     *
     * @access  public
     * @param   int $nbins  the number of bins to use for computing the histogram
     * @param   optional    float   $rangeLow   lowest value to use for bin frequency calculation
     * @param   optional    float   $rangeHigh   highest value to use for bin frequency calculation
     * @return  void
     */
    public function setBinOptions($nbins, $rangeLow = null, $rangeHigh = null)
    {

        $this->_nbins = (is_int($nbins) && $nbins > 2) ? $nbins : 10;
        $this->_rangeLow = $rangeLow;
        $this->_rangeHigh = $rangeHigh;
    }

    /**
     * Returns an associative array with the bin options
     *
     * @access public
     * @return array Associative array of bin options:
     *                  array(  'nbins'=>$nbins,
     *                          'rangeLow'=>$rangeLow,
     *                          'rangeHigh'=>$rangeHigh);
     */
    public function getBinOptions()
    {

        return array(
            'nbins' => $this->_nbins,
            'rangeLow' => $this->_rangeLow,
            'rangeHigh' => $this->_rangeHigh,
        );
    }

    /**
     * Sets the data to be processed. The data will be validated to
     * be a simple uni-dimensional numerical array
     *
     * @access  public
     * @param   array   $data   the numeric array
     * @return  mixed   boolean true on success, a \PEAR_Error object otherwise
     *
     * @see _clear()
     * @see Math_AbstractHistogram::getData()
     * @see Math_AbstractHistogram
     * @see getHistogramData()
     */
    public function setData($data)
    {

        $this->_clear();
        if (!is_array($data)) {
            throw new \PEAR_Exception("array of numeric data expected");
        }

        foreach ($data as $item) {
            if (!is_numeric($item)) {
                throw new \PEAR_Exception("non-numeric item in array");
            }
        }

        $this->_data = $data;
        if (is_null($this->_rangeLow)) {
            $this->_rangeLow = min($this->_data);
        }

        if (is_null($this->_rangeHigh)) {
            $this->_rangeHigh = max($this->_data);
        }

        sort($this->_data);
        return true;
    }

    /**
     * Calculates the histogram bins and frequencies
     *
     * @access  public
     * @param   optional    $statsMode  calculate basic statistics (STATS_BASIC) or full (STATS_FULL)
     * @return  mixed   boolean true on success, a \PEAR_Error object otherwise
     *
     * @see Math_Stats
     */
    public function calculate($statsMode = \HuasoFoundries\Math\Stats::STATS_BASIC)
    {

        $this->_stats = new \HuasoFoundries\Math\Stats();

        $this->_statsMode = $statsMode;
        $delta = ($this->_rangeHigh - $this->_rangeLow) / $this->_nbins;
        $lastpos = 0;
        $cumm = 0;
        $data = $this->_histogramData();
        $ndata = count($data);
        $ignoreList = array();

        for ($i = 0; $i < $this->_nbins; $i++) {
            $loBin = $this->_rangeLow + $i * $delta;
            $hiBin = $loBin + $delta;
            $this->_bins[$i]["low"] = $loBin;
            $this->_bins[$i]["high"] = $hiBin;
            $this->_bins[$i]["mid"] = ($hiBin + $loBin) / 2;
            if ($this->_type == self::HISTOGRAM_CUMMULATIVE) {
                $this->_bins[$i]["count"] = $cumm;
            } else {
                $this->_bins[$i]["count"] = 0;
            }

            for ($j = 0; $j < $ndata; $j++) {
                if (!empty($ignoreList) && in_array($j, $ignoreList)) {
                    continue;
                }

                if ($j == 0) {
                    $inRange = ($loBin <= $data[$j] && $hiBin >= $data[$j]);
                } else {
                    $inRange = ($loBin < $data[$j] && $hiBin >= $data[$j]);
                }

                if ($inRange) {
                    $this->_bins[$i]["count"]++;
                    if ($this->_type == self::HISTOGRAM_CUMMULATIVE) {
                        $cumm++;
                    }

                    $ignoreList[] = $j;
                }
            }
        }
        return true;
    }

    /**
     * Returns the statistics for the data set
     *
     * @access  public
     * @return  mixed   an associative array on success, a \PEAR_Error object otherwise
     */
    public function getDataStats()
    {

        if ($this->isCalculated()) {
            $this->_stats->setData($this->_data);

            return $this->_stats->calc($this->_statsMode);
        } else {
            throw new \PEAR_Exception("histogram has not been calculated");
        }
    }

    /**
     * Returns the statistics for the data set, filtered using the bin range
     *
     * @access  public
     * @return  mixed   an associative array on success, a \PEAR_Error object otherwise
     */
    public function getHistogramDataStats()
    {

        if ($this->isCalculated()) {
            $this->_stats->setData($this->_histogramData());
            return $this->_stats->calc($this->_statsMode);
        } else {
            throw new \PEAR_Exception("histogram has not been calculated");
        }
    }

    /**
     * Returns the bins and frequencies calculated using the given
     * bin mode and separator
     *
     * @access  public
     * @param   int $mode   one of HISTOGRAM_LO_BINS, HISTOGRAM_MID_BINS (default), or HISTOGRAM_HI_BINS
     * @param   string  $separator  the separator, default ", "
     * @return  mixed  a string on success, a \PEAR_Error object otherwise
     */
    public function toSeparated($mode = self::HISTOGRAM_MID_BINS, $separator = ", ")
    {

        $bins = $this->getBins($mode);
        if (\PEAR::isError($bins)) {
            return $bins;
        }

        $nbins = count($bins);
        $out = array("# bin{$separator}frequency");
        foreach ($bins as $bin => $freq) {
            $out[] = "{$bin}{$separator}{$freq}";
        }

        return implode("\n", $out) . "\n";
    }

    /**
     * Static method to check that an object is a Histogram instance
     *
     * @static
     * @access public
     * @param  object Histogram $hist An instance of the Histogram class
     * @return boolean TRUE on success, FALSE otherwise
     */
    public static function isValidHistogram(&$hist)
    {

        return (is_object($hist) && is_a($hist, '\HuasoFoundries\Histogram\Histogram'));
    }

    /**
     * Method to interrogate if a histogram has been calculated
     *
     * @access public
     * @return boolean TRUE if the histogram was calculated, FALSE otherwise
     * @see calculate()
     */
    public function isCalculated()
    {

        return !empty($this->_bins);
    }

    /**
     * Generates a plot using the appropriate printer object
     *
     * @param object $printer A Histogram_Printer_* object
     * @return string|PEAR_Error A string on success, a \PEAR_Error otherwise
     */
    public function generatePlot(&$printer)
    {

        if (is_object($printer) && is_a($printer, '\HuasoFoundries\Histogram\Printer\Common')) {
            $printer->setHistogram($this);
            return $printer->generateOutput();
        } else {
            throw new \PEAR_Exception('Invalid object, expecting a \HuasoFoundries\Histogram\Printer\* instance');
        }
    }

    /**
     * Prints a simple ASCII representation of the histogram
     *
     * @deprecated
     * @access  public
     * @param   optional    int $mode   one of HISTOGRAM_LO_BINS, HISTOGRAM_MID_BINS, or HISTOGRAM_HI_BINS (default)
     * @return  mixed   a string on success, a \PEAR_Error object otherwise
     */
    public function printHistogram($mode = self::HISTOGRAM_HI_BINS)
    {

        if (!$this->isCalculated()) {
            throw new \PEAR_Exception("histogram has not been calculated");
        }
        $out = ($this->_type == self::HISTOGRAM_CUMMULATIVE) ? "Cummulative Frequency" : "Histogram";
        $out .= "\n\tNumber of bins: " . $this->_nbins . "\n";
        $out .= "\tPlot range: [" . $this->_rangeLow . ", " . $this->_rangeHigh . "]\n";
        $hdata = $this->_histogramData();
        $out .= "\tData range: [" . min($hdata) . ", " . max($hdata) . "]\n";
        $out .= "\tOriginal data range: [" . min($this->_data) . ", " . max($this->_data) . "]\n";
        $out .= "BIN (FREQUENCY) ASCII_BAR (%)\n";
        $fmt = "%-4.3f (%-4d) |%s\n";
        $bins = $this->_filterBins($mode);
        $maxfreq = max(array_values($bins));
        $total = count($this->_data);
        foreach ($bins as $bin => $freq) {
            $out .= sprintf($fmt, $bin, $freq, $this->_bar($freq, $maxfreq, $total));
        }

        return $out;
    }

    /**
     * Prints a simple ASCII bar
     *
     * @access  private
     * @param   int $freq   the frequency
     * @param   int $maxfreq    the maximum frequency
     * @param   int $total  the total count
     * @return  string
     */
    public function _bar($freq, $maxfreq, $total)
    {

        $fact = floatval(($maxfreq > 40) ? 40 / $maxfreq : 1);
        $niter = round($freq * $fact);
        $out = "";
        for ($i = 0; $i < $niter; $i++) {
            $out .= "*";
        }

        return $out . sprintf(" (%.1f%%)", $freq / $total * 100);

    }

    /**
     * Returns a subset of the bins array by bin value type
     *
     * @access  private
     * @param   int $mode one of HISTOGRAM_MID_BINS, HISTOGRAM_LO_BINS or HISTOGRAM_HI_BINS
     * @return  array
     */
    public function _filterBins($mode)
    {

        $map = array(
            self::HISTOGRAM_MID_BINS => "mid",
            self::HISTOGRAM_LO_BINS => "low",
            self::HISTOGRAM_HI_BINS => "high",
        );
        $filtered = array();
        foreach ($this->_bins as $bin) {
            $filtered["{$bin[$map[$mode]]}"] = $bin["count"];
        }

        return $filtered;
    }

    /**
     * Returns an array of data contained within the range for the
     * histogram calculation. Overrides the empty implementation in
     * Math_AbstractHistogram::_histogramData()
     *
     * @access  private
     * @return  array
     */
    public function _histogramData()
    {

        $data = array();
        foreach ($this->_data as $val) {
            if ($val < $this->_rangeLow || $val > $this->_rangeHigh) {
                continue;
            } else {
                $data[] = $val;
            }
        }

        return $data;
    }

}

// vim: ts=4:sw=4:et:
// vim6: fdl=1:
