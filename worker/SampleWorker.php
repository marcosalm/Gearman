<?php
/**
 * Created by PhpStorm.
 * User: Dcide
 * Date: 30/08/2016
 * Time: 13:47
 */

namespace Worker;

use GearDaemon\Worker;

class SampleWorker extends Worker {

	var $jid;

	protected function registerCallbacks()
    {
        $this->GearmanWorker->addFunction('SampleWorker', [$this, 'doJob']);
    }

    public function doJob(\GearmanJob $Job){
        $this->countJob();
        $workload_size = $Job->workloadSize();
        $workload = $Job->workload();
        $params = unserialize($workload);
        $this->jid = $params['job_id'];
        $cwd = $params['wd'];

        $command = $params['command'];
        $args = $params['args'];


	    $time= date('Y/m/d H:i:s', strtotime("now"));
		
	    echo "[Job {$this->jid} STARTED at .................{$time}\n\n";

	    $res = $this->PsExecute("cd {$cwd} && {$command} {$args}",400);

	    if(!$res){
			//If timeout, do error treatment here 
			
	    }
	    $time= date('Y/m/d H:i:s', strtotime("now"));
	    echo "\n[Job {$this->jid} ENDED at .................{$time}\n\n";
        $Job->sendComplete("completo");

    }

	function PsExecute($command, $timeout = 60, $sleep = 2) {
		// First, execute the process, get the process ID

		$pid = $this->PsExec($command);

		if( $pid === false )
			return false;

		$cur = 0;
		// Second, loop for $timeout seconds checking if process is running
		while( $cur < $timeout ) {
			sleep($sleep);
			$cur += $sleep;
			// If process is no longer running, return true;

			echo "\n ---- $cur ------ \n";

			if( !$this->PsExists($pid) )
				return true; // Process must have exited, success!
		}

		// If process is still running after timeout, kill the process and return false
		$this->PsKill($pid);
		return false;
	}

	function PsExec($commandJob) {

		$command = $commandJob." > /dev/null >> {$this->outputPath}SampleWorker.output.log 2>&1 & echo $!";
		exec($command ,$op);
		$pid = (int)$op[0];

		if($pid!="") return $pid;

		return false;
	}

	function PsExists($pid) {

		exec("ps aux | grep $pid 2>&1", $output);

		while( list(,$row) = each($output) ) {

			$row_array = explode(" ", $row);
			$check_pid = $row_array[0];

			if($pid == $check_pid) {
				return true;
			}

		}
		return false;
	}

	function PsKill($pid) {
		exec("kill -9 $pid", $output);
	}
}