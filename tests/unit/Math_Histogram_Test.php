<?php







class Math_Histogram_Test extends \Codeception\TestCase\Test
{
    // create a boring array
    public $vals = array(
            1.5,2,3,4,0,3.2,0.1,0,0,5,3,2,3,4,1,2,4,5,1,3,2,4,5,2,3,4,1,2,
            1.5,2,3,4,0,3.2,0.1,0,0,5,3,2,3,4,1,2,4,5,1,3,2,4,5,2,3,4,1,2,
            1.5,2,3,4,0,3.2,0.1,0,0,5,3,2,3,4,1,2,4,5,1,3,2,4,5,2,3,4,1,2
        );

    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        // create an instance
        $this->h = new \HuasoFoundries\Histogram\Histogram();
    }

    protected function _after()
    {
        unset($this->h);
    }

    // tests
    public function testcummulative()
    {
        // let's do a cummulative histogram
        $this->h->setType(\HuasoFoundries\Histogram\AbstractHistogram::HISTOGRAM_CUMMULATIVE);
        $this->h->setData($this->vals);
        $this->h->calculate();
        print_r($this->h->getHistogramInfo());
        print_r($this->h->getBins(\HuasoFoundries\Histogram\AbstractHistogram::HISTOGRAM_HI_BINS));
        echo $this->h->printHistogram();
        echo "\n=====\n";
    }

    // tests
    public function testbigdataset()
    {
        // let us read a bigger data set:
        $data = array();
        foreach (file(__DIR__."/../_data/ex_histogram.data") as $item) {
            $data[] = floatval(trim($item));
        }

        // let's do a simple histogram
        $this->h->setType(\HuasoFoundries\Histogram\AbstractHistogram::HISTOGRAM_SIMPLE);
        // and set new bin options
        $this->h->setBinOptions(20, 1.7, 2.7);
        // then set a the big data set
        $this->h->setData($data);

        // and calculate using full stats
        $this->h->calculate(\HuasoFoundries\Math\Stats::STATS_FULL);
        print_r($this->h->getHistogramInfo());
        print_r($this->h->getBins(\HuasoFoundries\Histogram\AbstractHistogram::HISTOGRAM_MID_BINS));
        echo $this->h->printHistogram(\HuasoFoundries\Histogram\AbstractHistogram::HISTOGRAM_MID_BINS);
    }
}
