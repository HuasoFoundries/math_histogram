<?php
namespace HuasoFoundries\Histogram\Printer;

/**
 * Base class for histogram printer objects
 *
 * @author  Jesus M. Castagnetto <jmcastagnetto@php.net>
 * @version 0.9.1beta
 * @access  public
 * @package Histogram
 */
class Common
{

    // properties

    /**
     * An associative array of options for the printer object
     *
     * @access private
     * @var array
     */
    public $_options;

    /**
     * The Histogram object
     *
     * @access private
     * @var object Histogram
     * @see Histogram
     */
    public $_hist;

    //

    /**
     * Constructor
     *
     * @access  public
     * @param   optional array $options     An associative array of printer options
     * @param   optional object Histogram $hist    A Histogram object
     * @return  object  Histogram_Printer_Common
     */
    public function __construct($hist = null, $options = null)
    {
        $this->setHistogram($hist);
        $this->setOptions($options);
    }

    /**
     * Sets the printer options
     *
     * @access  public
     * @param   array $options     An associative array of printer options
     *              Common options:
     *              'useHTTPHeaders' (default = false), whether to output HTTP headers when using printOutput()
     *              'outputStatistics' (default = false), whether to include histogram statistics when generating the output
     * @return  boolean TRUE on success, FALSE otherwise
     */
    public function setOptions($options)
    {
        if (!is_array($options)) {
            $this->_options = null;
            return false;
        } else {
            $this->_options = $options;
            return true;
        }
        if (!array_key_exists('useHTTPHeaders', $this->_options)) {
            $this->_options['useHTTPHeaders'] = false;
        }
        if (!array_key_exists('outputStatistics', $this->_options)) {
            $this->_options['outputStatistics'] = false;
        }
    }

    /**
     * Sets the Histogram object to plot
     *
     * @access  public
     * @param   object Histogram $hist A Histogram instance
     * @return  boolean TRUE on success, FALSE otherwise
     */
    public function setHistogram(&$hist)
    {
        if (\HuasoFoundries\Histogram\Histogram::isValidHistogram($hist)) {
            $this->_hist = &$hist;
            return true;
        } else {
            $this->_hist = null;
            return false;
        }
    }

    // override this method in child classes
    /**
     * Returns a (binary safe) string representation of a Histogram plot
     *
     * @access public
     * @return string|PEAR_Error A string on succcess, a \PEAR_Error otherwise
     */
    public function generateOutput()
    {
        throw new \PEAR_Exception('Unimplemented method');
    }

    // override this method in child classes
    /**
     * Prints out a graphic representation of a Histogram
     *
     * @access public
     * @return boolean|PEAR_Error TRUE on success, a \PEAR_Error otherwise
     */
    public function printOutput()
    {
        throw new \PEAR_Exception('Unimplemented method');
    }

    // override this method in child classes
    /**
     * Static method to print out a graphic representation of a Histogram
     *
     * @static
     * @access public
     * @param object Histogram $hist A Histogram instance
     * @param array $options An array of options for the printer object
     * @return boolean|PEAR_Error TRUE on success, a \PEAR_Error otherwise
     */
    public function printHistogram(&$hist, $options = array())
    {
        throw new \PEAR_Exception('Unimplemented method');
    }

    /**
     * Utility method to do static printing
     *
     * @static
     * @access private
     * @param object $printer An instance of a Histogram_Printer_* class
     * @param object Histogram $hist A Histogram instance
     * @param array $options An array of options for the printer object
     * @return boolean|PEAR_Error TRUE on success, a \PEAR_Error otherwise
     */
    public function _doStaticPrint(&$printer, &$hist, $options)
    {
        if (!$printer->setHistogram($hist)) {
            throw new \PEAR_Exception('Not a valid Histogram object');
        }
        if (!$printer->setOptions($options)) {
            throw new \PEAR_Exception('Expecting an associative array of options');
        }
        // try to plot, clean up object, and return
        $err = $printer->printOutput();
        unset($printer);
        return $err;
    }
}

// vim: ts=4:sw=4:et:
// vim6: fdl=1:
