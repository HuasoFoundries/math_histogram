<?php
namespace Histogram;

/**
 * Abstract class defining common properties and methods for
 * the other histogram classes
 *
 * Originally this class was part of NumPHP (Numeric PHP package)
 *
 * @author  Jesus M. Castagnetto <jmcastagnetto@php.net>
 * @version 0.9.1beta
 * @access  public
 * @package Math_Histogram
 */

class AbstractHistogram
{

    const HISTOGRAM_ALL_BINS = 1;
    const HISTOGRAM_MID_BINS = 2;
    const HISTOGRAM_LO_BINS = 3;
    const HISTOGRAM_HI_BINS = 4;

    const HISTOGRAM_SIMPLE = 1;
    const HISTOGRAM_CUMMULATIVE = 2;

    // properties

    /**
     * The Math_Stats object
     *
     * @access  private
     * @var object  Math_Stats
     * @see Math_Stats
     */
    public $_stats = null;
    /**
     * Mode for the calculation of statistics
     *
     * @access  private
     * @var int one of STATS_BASIC or STATS_FULL
     * @see Math_Stats
     */
    public $_statsMode;
    /**
     * Array of bins
     *
     * @access  private
     * @var array
     */
    public $_bins = [];
    /**
     * Number(s) of bins to use in calculation
     *
     * @access  private
     * @var mixed
     */
    public $_nbins;
    /**
     * The lowest value(s) to be used when generating the bins
     *
     * @access  private
     * @var mixed
     */
    public $_rangeLow;
    /**
     * The highest value(s) to be used when generating the bins
     *
     * @access  private
     * @var mixed
     */
    public $_rangeHigh;
    /**
     * The data set
     *
     * @access  private
     * @var array
     * @see $_rangeLow
     * @see $_rangeHigh
     */
    public $_data = null;

    /**
     * Constructor
     * @param   optional    float   $rangeHigh   highest value to use for bin frequency calculation
     * @return  object  Math_Histogram
     *
     * @see setType()
     * @see setBinOptions()
     */
    public function __construct($type = self::HISTOGRAM_SIMPLE)
    {

        $this->setType($type);
    }

    /**
     * Sets the type of histogram to compute
     *
     * @access  public
     * @param   int $type one of HISTOGRAM_SIMPLE or HISTOGRAM_CUMMULATIVE
     * @return  mixed   boolean true on success, a PEAR_Error object otherwise
     */
    public function setType($type)
    {

        if ($type == self::HISTOGRAM_SIMPLE || $type == self::HISTOGRAM_CUMMULATIVE) {
            $this->_type = $type;
            return true;
        } else {
            return PEAR::raiseError("wrong histogram type requested");
        }
    }

    /**
     * Sets the binning options
     *
     * @access  public
     * @param   array   $binOptions associative array of bin options
     * @return  mixed   true on succcess, a PEAR_Error object otherwise
     */
    public function setBinOptions($binOptions)
    {

        if (!is_array($binOptions)) {
            return PEAR::raiseError("incorrect options array");
        }

        $this->_rangeLow = $binOptions["low"];
        $this->_rangeHigh = $binOptions["high"];
        $this->_nbins = $binOptions["nbins"];
        return true;
    }

    /**
     * Abstract method to set data. Needs to be implemented in each subclass
     *
     * @access  public
     * @param   array   $data
     */
    public function setData($data)
    {

    }

    /**
     * Returns the array of data set using setData()
     *
     * @access  public
     * @return  mixed   a numerical array on success, a PEAR_Error object otherwise
     *
     * @see setData()
     */
    public function getData()
    {

        if (is_null($this->_data)) {
            return PEAR::raiseError("data has not been set");
        } else {
            return $this->_data;
        }

    }

    /**
     * Returns the array of data used to calculate the histogram,
     * i.e. the data that was inside the range specified for the
     * histogram bins
     *
     * @access  public
     * @return  mixed   a numerical array on success, a PEAR_Error object otherwise
     *
     * @see setData()
     */
    public function getHistogramData()
    {

        if (is_null($this->_data)) {
            return PEAR::raiseError("data has not been set");
        } else {
            return $this->_histogramData();
        }

    }

    /**
     * Returns bins and frequencies for the histogram data set
     *
     * @access  public
     * @param   optional    int $mode   one of HISTOGRAM_ALL_BINS, HISTOGRAM_LO_BINS, HISTOGRAM_MID_BINS, or HISTOGRAM_HI_BINS
     * @return  mixed   an associative array on success, a PEAR_Error object otherwise
     */
    public function getBins($mode = self::HISTOGRAM_ALL_BINS)
    {

        if (empty($this->_bins)) {
            return PEAR::raiseError("histogram has not been calculated");
        }

        switch ($mode) {
            case self::HISTOGRAM_ALL_BINS:
                return $this->_bins;
                break;
            case self::HISTOGRAM_MID_BINS:
            case self::HISTOGRAM_LO_BINS:
            case self::HISTOGRAM_HI_BINS:
                return $this->_filterBins($mode);
                break;
            default:
                return PEAR::raiseError("incorrect mode for bins");
        }
    }

    /**
     * Returns the statistics for the data set and the histogram bins and
     * frequencies
     *
     * @access  public
     * @return  mixed   an associative array on success, a PEAR_Error object otherwise
     */
    public function getHistogramInfo()
    {

        if (!empty($this->_nbins)) {
            $data_stats = $this->getDataStats();
            $getHistogramDataStats = $this->getHistogramDataStats();
            //\Util\Helpers::prdie('getHistogramInfo after $getHistogramDataStats');

            $info = [
                "type" => ($this->_type == self::HISTOGRAM_CUMMULATIVE) ?
                "cummulative frequency" : "histogram",
                "data_stats" => $data_stats,
                "hist_data_stats" => $getHistogramDataStats,
                "bins" => $this->_bins,
                "nbins" => $this->_nbins,
                "range" => [
                    "low" => $this->_rangeLow,
                    "high" => $this->_rangeHigh,
                ],
            ];
            return $info;
        } else {
            return PEAR::raiseError("histogram has not been calculated");
        }
    }

    /**
     * Resets the values of several private properties
     *
     * @access  private
     * @return  void
     */
    public function _clear()
    {

        $this->_stats = null;
        $this->_statsMode = null;
        $this->_data = null;
        $this->_orig = [];
        $this->_bins = [];
    }

    /**
     * Abstract method that returns an array of data contained within the
     * range for the histogram calculation
     * Each subclass must implement this method
     *
     * @access  private
     * @return  array
     */
    public function _histogramData()
    {

        return [];
    }

}
// vim: ts=4:sw=4:et:
// vim6: fdl=1:
