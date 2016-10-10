<?php
/**
 * Created by PhpStorm.
 * User: Dcide
 * Date: 30/08/2016
 * Time: 13:47
 */

namespace Worker;

use Eden\Core\Exception;
use GearDaemon\Worker;

class EternalWorker extends Worker {

    protected function registerCallbacks()
    {
        $this->GearmanWorker->addFunction('EternalWorker', [$this, 'doJob']);
    }

    public function doJob(\GearmanJob $Job){
        $this->countJob();
        $workload = $Job->workload();
        $params = unserialize($workload);
        $cwd = $params['wd'];
        $command = "php -q";
        $args = "async_jobs.php";

        while(true) {
        	exec("cd {$cwd} && {$command} {$args} >> {$this->outputPath}EternalWorker.output.log 2>&1 &", $out, $var);
	        sleep(60);
			$Job->sendComplete("Complete!");
        }

    }
}