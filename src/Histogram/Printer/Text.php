<?php
namespace HuasoFoundries\Histogram\Printer;

/**
 * Class to print text representations of  a Math_Histogram object
 *
 * @author  Jesus M. Castagnetto <jmcastagnetto@php.net>
 * @version 0.9.1beta
 * @access  public
 * @package Math_Histogram
 */
class Text extends Common
{

    /**
     * Returns a string representation of a Histogram plot
     *
     * @access public
     * @return string|PEAR_Error A string on succcess, a \PEAR_Error otherwise
     */
    public function generateOutput()
    {
        if (is_null($this->_hist)) {
            throw new \PEAR_Exception('Math_Histogram object has not been set');
        }
        if (!$this->_hist->isCalculated()) {
            if (isset($this->_options['histogramStatsMode'])) {
                $this->_hist->calculate($this->_options['histogramStatsMode']);
            } else {
                $this->_hist->calculate();
            }
        }

        if (isset($this->_options['histogramBinMode'])) {
            $binmode = $this->_options['histogramBinMode'];
        } else {
            $binmode = \HuasoFoundries\Histogram\Histogram::HISTOGRAM_HI_BINS;
        }
        $bins    = $this->_hist->getBins($binmode);
        $binopts = $this->_hist->getBinOptions();
        $hdata   = $this->_hist->getHistogramData();
        $data    = $this->_hist->getData();
        $fmt     = "%-4.3f (%-4d) |%s\n";
        $maxfreq = max(array_values($bins));
        $total   = count($hdata);
        $out     = ($this->_hist->_type == \HuasoFoundries\Histogram\Histogram::HISTOGRAM_CUMMULATIVE) ? "Cummulative Frequency" : "Histogram";
        $out .= "\n\tNumber of bins: {$binopts['nbins']}\n";
        $out .= "\tPlot range: [{$binopts['rangeLow']}, {$binopts['rangeHigh']}]\n";
        $out .= "\tData range: [" . min($hdata) . ", " . max($hdata) . "]\n";
        $out .= "\tOriginal data range: [" . min($data) . ", " . max($data) . "]\n";
        $out .= "BIN (FREQUENCY) ASCII_BAR (%)\n";
        foreach ($bins as $bin => $freq) {
            $out .= sprintf($fmt, $bin, $freq, $this->_bar($freq, $maxfreq, $total));
        }
        if ($this->_options['outputStats']) {
            $out .= "\n --- Histogram Statistics ---\n";
            $out .= $this->_printStats($this->_hist->getHistogramDataStats());
        }
        return $out;
    }

    /**
     * Prints out a graphic representation of a Histogram
     *
     * @access public
     * @return boolean|PEAR_Error TRUE on success, a \PEAR_Error otherwise
     */
    public function printOutput()
    {
        $plot = $this->generateOutput($hist);
        if (\PEAR::isError($plot)) {
            return $plot;
        } else {
            if ($this->_options['useHTTPHeaders']) {
                header('Content-type: text/plain');
            }
            echo $plot;
        }
    }

    /**
     * Static method to print out a graphic representation of a Histogram
     *
     * @static
     * @access public
     * @param object Math_Histogram $hist A Math_Histogram instance
     * @param array $options An array of options for the printer object
     * @return boolean|PEAR_Error TRUE on success, a \PEAR_Error otherwise
     */
    public function printHistogram(&$hist, $options = array())
    {
        $printer = new \HuasoFoundries\Histogram\Printer\Text();
        return \HuasoFoundries\Histogram\Printer\Common::_doStaticPrint($printer, $hist, $options);
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
        $fact  = floatval(($maxfreq > 40) ? 40 / $maxfreq : 1);
        $niter = round($freq * $fact);
        $out   = "";
        for ($i = 0; $i < $niter; $i++) {
            $out .= "*";
        }

        return $out . sprintf(" (%.1f%%)", $freq / $total * 100);
    }

    /**
     * Prints the histogram data statistics
     *
     * @access private
     * @param array $stats Associative array of statistics
     * @param optional string $prefix Prefix to use when printing out statistics
     * @return string
     */
    public function _printStats($stats, $prefix = '')
    {
        $out = '';
        foreach ($stats as $name => $value) {
            if (is_array($value)) {
                $out .= $prefix . $name . ":\n";
                $out .= $this->_printStats($value, $prefix . "\t");
            } else {
                $out .= "{$prefix}{$name}: $value\n";
            }
        }
        return $out;
    }
}

// vim: ts=4:sw=4:et:
// vim6: fdl=1:
