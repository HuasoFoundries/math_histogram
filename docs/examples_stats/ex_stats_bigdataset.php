<?php
    //require_once 'Math/Stats.php';
   require_once __DIR__ . '/../../vendor/autoload.php';


    $data = array();
    $fp   = fopen(__DIR__."/../../tests/_data/bigcummulativedata.dat", "r");
    

    
    while ((list($val, $count) = fgetcsv($fp, 80)) !== false) {
        $data[$val] = $count;
    }

    $s = new \PEAR\Math\Stats();
    $s->setData($data, \PEAR\Math\Stats::STATS_DATA_CUMMULATIVE);
    

    try {
        $stats = $s ->calcFull();

        //print_r($stats);
        echo "Using Math_Stats:\n"
        . "mean = {$stats['mean']}\n"
        . "SEmean = {$stats['std_error_of_mean']}\n"
        . "std dev = {$stats['stdev']}\n"
        . "quartile(25)) = {$stats['quartiles'][25]}\n"
        . "median = {$stats['median']}\n"
        . "quartile(75) = {$stats['quartiles'][75]}\n"
        . "min = {$stats['min']}\n"
        . "max = {$stats['max']}\n"
        . "count = {$stats['count']}\n\n"
        . "Using SPSS:\n"
        . "mean = 2.8627\n"
        . "SEmean = 0.0005\n"
        . "std dev = 1.0630\n"
        . "quartile(25) = 2\n"
        . "median = 3\n"
        . "quartile(75) = 4\n"
        . "min = 1\n"
        . "max = 41\n"
        . "count = 4255666\n";
        
        //Name,Mean,SEMean,StDev,Q1,Median,Q3,Min,Max,N
      //SPSS,2.8627,0.0005,1.0630,2,3,4,1,41,4255666
    } catch (\Exception $e) {
        echo "\n\t Exception: ".$e->getMessage()."\n\n";
    }
