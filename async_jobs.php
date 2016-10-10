<?php
$gmclient= new GearmanClient();
$gmclient->addServer();

    $console_path = realpath("path/to/where/command/will/execute");
    $params = [
        "wd"=> $console_path,
        "command" => "./cake",
	    "force" => false
        ];

    $cron_jobs = [
        "SampleWorker",
    ];

    foreach ($cron_jobs as $job){
       $job_handle[] = $gmclient->doLowBackground($job, serialize($params));
        if ($gmclient->returnCode() != GEARMAN_SUCCESS)
        {
            echo $job. " - Problens executing cron!";
        }
        sleep(5);
    }

    echo "done!\n";