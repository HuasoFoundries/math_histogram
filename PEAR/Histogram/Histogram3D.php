<?php
namespace PEAR\Histogram;

/**
 * Class to generate 3D histograms from bi-dimensional numeric
 * arrays.
 *
 * Originally this class was part of NumPHP (Numeric PHP package)
 *
 * @author  Jesus M. Castagnetto <jmcastagnetto@php.net>
 * @version 0.9.1beta
 * @access  public
 * @package Math_Histogram
 */
class Histogram3D extends AbstractHistogram
{

    /**
     * Constructor for Math_Histogram3D
     *
     * @access  public
     * @param   optional    int $type   one of HISTOGRAM_SIMPLE or HISTOGRAM_CUMMULATIVE
     * @param   optional    array $binOptions   an array of options for binning the data
     * @return  object  Math_Histogram3D
     *
     * @see setBinOptions()
     * @see Math_AbstractHistogram::setType()
     * @see Math_AbstractHistogram
     */

    public function __construct($type = self::HISTOGRAM_SIMPLE, $binOptions = "")
    {

        $this->setType($type);
        try {
            $this->setBinOptions($binOptions);
        } catch (\PEAR_Exception $e) {
            // Falling back to default options
        }
    }

/**
 * Sets the binning options. Overrides parent's method.
 *
 * @access  public
 * @param   array $binOptions  an array of options for binning the data
 * @return  void
 */

    public function setBinOptions($binOptions)
    {

        if ($this->_validBinOptions($binOptions)) {
            return parent::setBinOptions($binOptions);
        } else {
            throw new \PEAR_Exception("incorrect options array");
        }

    }

    /**
     * Sets the data to be processed. The data will be validated to
     * be a simple bi-dimensional numerical array
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
        if (!$this->_validData($data)) {
            throw new \PEAR_Exception("array of numeric coordinates expected");
        }

        $this->_data = $data;
        list($xMin, $xMax) = $this->_getMinMax('x');
        list($yMin, $yMax) = $this->_getMinMax('y');
        if (is_null($this->_rangeLow)) {
            $this->_rangeLow = array('x' => $xMin, 'y' => $yMin);
        }

        if (is_null($this->_rangeHigh)) {
            $this->_rangeHigh = array('x' => $xMax, 'y' => $yMax);
        }

        if (is_null($this->_nbins)) {
            $this->_nbins = array('x' => 10, 'y' => 10);
        }

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
    public function calculate($statsMode = \PEAR\Math\Stats::STATS_BASIC)
    {

        $this->_bins = array();
        $this->_stats = array('x' => new \PEAR\Math\Stats(), 'y' => new \PEAR\Math\Stats());
        $this->_statsMode = $statsMode;
        $deltaX = ($this->_rangeHigh['x'] - $this->_rangeLow['x']) / $this->_nbins['x'];
        $deltaY = ($this->_rangeHigh['y'] - $this->_rangeLow['y']) / $this->_nbins['y'];
        $data = $this->_histogramData();
        //$dataX = $this->_data['x'];
        //$dataY = $this->_data['y'];
        $dataX = $data['x'];
        $dataY = $data['y'];
        $ignoreList = array();
        $cumm = 0;
        $nData = count($dataX);
        for ($i = 0; $i < $this->_nbins['x']; $i++) {
            $loXBin = $this->_rangeLow['x'] + $i * $deltaX;
            $hiXBin = $loXBin + $deltaX;
            $xBin = array('low' => $loXBin, 'high' => $hiXBin,
                'mid' => ($hiXBin + $loXBin) / 2);
            for ($j = 0; $j < $this->_nbins['y']; $j++) {
                $loYBin = $this->_rangeLow['y'] + $j * $deltaY;
                $hiYBin = $loYBin + $deltaY;
                $yBin = array('low' => $loYBin, 'high' => $hiYBin,
                    'mid' => ($hiYBin + $loYBin) / 2);
                $bin = array('x' => $xBin, 'y' => $yBin);
                $freq = 0;
                for ($k = 0; $k < $nData; $k++) {
                    if (!empty($ignoreList) && in_array($k, $ignoreList)) {
                        continue;
                    }

                    $valueX = $dataX[$k];
                    $valueY = $dataY[$k];
                    $inRangeX = $inRangeY = false;
                    if ($i == 0) {
                        $inRangeX = ($loXBin <= $valueX && $hiXBin >= $valueX);
                    } else {
                        $inRangeX = ($loXBin < $valueX && $hiXBin >= $valueX);
                    }

                    if ($j == 0) {
                        $inRangeY = ($loYBin <= $valueY && $hiYBin >= $valueY);
                    } else {
                        $inRangeY = ($loYBin < $valueY && $hiYBin >= $valueY);
                    }

                    if ($inRangeX && $inRangeY) {
                        $freq++;
                        $cumm++;
                        $ignoreList[] = $k;
                    }
                }
                if ($this->_type == self::HISTOGRAM_CUMMULATIVE) {
                    if ($freq > 0) {
                        $bin['count'] = $freq + $cumm - 1;
                    } else {
                        $bin['count'] = 0;
                    }

                } else {
                    $bin['count'] = $freq;
                }
                $bin['xbin'] = $i;
                $bin['ybin'] = $j;
                $this->_bins[] = $bin;
            }
        }
    }

    /**
     * Returns the statistics for the data set
     *
     * @access  public
     * @return  mixed   an associative array on success, a \PEAR_Error object otherwise
     */
    public function getDataStats()
    {

        if (empty($this->_bins)) {
            throw new \PEAR_Exception("histogram has not been calculated");
        }

        $this->_stats['x']->setData($this->_data['x']);
        $this->_stats['y']->setData($this->_data['y']);
        return array('x' => $this->_stats['x']->calc($this->_statsMode),
            'y' => $this->_stats['y']->calc($this->_statsMode));
    }

    /**
     * Returns the statistics for the data set, filtered using the bin ranges
     *
     * @access  public
     * @return  mixed   an associative array on success, a \PEAR_Error object otherwise
     */
    public function getHistogramDataStats()
    {

        if (empty($this->_bins)) {
            throw new \PEAR_Exception("histogram has not been calculated");
        }

        $data = $this->_histogramData();
        $this->_stats['x']->setData($data['x']);
        $this->_stats['y']->setData($data['y']);
        return array('x' => $this->_stats['x']->calc($this->_statsMode),
            'y' => $this->_stats['y']->calc($this->_statsMode));
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
        try {
            $bins = $this->getBins($mode);    
        } catch (\PEAR_Exception $e) {
            return $bins;
        }
        

        $nbins = count($bins);
        $out = array("# x_bin{$separator}y_bin{$separator}frequency");
        for ($i = 0; $i < $nbins; $i++) {
            $out[] = implode($separator, $bins[$i]);
        }

        return implode("\n", $out) . "\n";
    }

    /**
     * Returns the minimum and maximum of the given unidimensional numeric
     * array
     *
     * @access  private
     * @param   array   $elem
     * @return  array   of values: array(min, max)
     */
    public function _getMinMax($elem)
    {

        return array(min($this->_data[$elem]), max($this->_data[$elem]));
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
            $tmp['x'] = $bin['x'][$map[$mode]];
            $tmp['y'] = $bin['y'][$map[$mode]];
            $tmp['count'] = $bin['count'];
            $filtered[] = $tmp;
        }
        return $filtered;
    }

    /**
     * Checks that the array of options passed is valid
     * Options array should have the form:
     *
     * $opt = array ('low'=>array('x'=>10, 'y'=>10),
     *               'high'=>array(...),
     *               'nbins'=>array(...));
     *
     * @access  private
     * @return  boolean
     */
    public function _validBinOptions($binOptions)
    {

        $barray = (is_array($binOptions)
            && is_array($binOptions['low'])
            && is_array($binOptions['high'])
            && is_array($binOptions['nbins']));
        if(!$barray) {
            return false;
        }
        $low = $binOptions['low'];
        $high = $binOptions['high'];
        $nbins = $binOptions['nbins'];
        $blow = (isset($low['x']) && isset($low['y'])
            && is_numeric($low['x']) && is_numeric($low['y']));
        $bhigh = (isset($high['x']) && isset($high['y'])
            && is_numeric($high['x']) && is_numeric($high['y']));
        $bnbins = (isset($nbins['x']) && isset($nbins['y'])
            && is_numeric($nbins['x']) && is_numeric($nbins['y']));
        return ($blow && $bhigh && $bnbins);
    }

    /**
     * Checks that the data passed is bi-dimensional numeric array
     * of the form:
     *
     * $data = array ('x'=>array(...), 'y'=>array(...));
     *
     * It also checks that: count($data['x']) == count($data['y'])
     *
     * @access  private
     * @return  boolean
     */
    public function _validData($data)
    {

        if (is_array($data) && is_array($data['x']) && is_array($data['y'])) {
            $n = count($data['x']);
            if (count($data) == 2 && $n == count($data['y'])) {
                for ($i = 0; $i < $n; $i++) {
                    if (!is_numeric($data['x'][$i]) || !is_numeric($data['y'][$i])) {
                        return false;
                    }
                }

                // if everything checks out
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Returns an array of data contained within the ranges for the
     * histogram calculation. Overrides the empty implementation in
     * Math_AbstractHistogram::_histogramData()
     *
     * @access  private
     * @return  array
     */
    public function _histogramData()
    {

        if ($this->_rangeLow['x'] == min($this->_data['x'])
            && $this->_rangeHigh['x'] == max($this->_data['x'])
            && $this->_rangeLow['y'] == min($this->_data['y'])
            && $this->_rangeHigh['y'] == max($this->_data['y'])) {
            return $this->_data;
        }

        $data = array();
        $ndata = count($this->_data['x']);
        for ($i = 0; $i < $ndata; $i++) {
            $x = $this->_data['x'][$i];
            $y = $this->_data['y'][$i];
            $inRangeX = ($this->_rangeLow['x'] <= $x && $this->_rangeHigh['x'] >= $x);
            $inRangeY = ($this->_rangeLow['y'] <= $y && $this->_rangeHigh['y'] >= $y);
            if ($inRangeX && $inRangeY) {
                $data['x'][] = $x;
                $data['y'][] = $y;
            } else {
                continue;
            }
        }
        return $data;
    }

}

// vim: ts=4:sw=4:et:
// vim6: fdl=1:
