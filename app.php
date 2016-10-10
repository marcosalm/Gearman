<?php
/**
 * A simple demo application to showing a Worker that execute all the time.
 *
 * This is a simple cli script allowing to start/stop/restart/monitor gearman workers.
 * Please see the inline comments for additional information.
 */
if (empty($argv)) {
    exit('Script can only be run in cli mode.' . PHP_EOL);
}
if (empty($argv[1])) {
    exit('No action given. Valid actions are: start|stop|restart|keepalive|status' . PHP_EOL);
}


require 'vendor/autoload.php';

if (!defined('ROOT')) {
    define('ROOT', __DIR__);
}
/**
 *  Create a new GearmanManager instance.
 */
$gearman = new GearDaemon\GearmanManager;

/**
 * Set gearman credentials and path information.
 */
$gearman->setGearmanCredentials('127.0.0.1', 4730);
// Log files for each worker-group go into this folder
$gearman->setLogPath(__DIR__ . '/logs/');
// Folder containing worker pid files
$gearman->setRunPath(__DIR__ . '/run/');
// Folder containig you actual workers
$gearman->setWorkerPath(__DIR__ . '/worker/');

/**
 * Configure your workers.
 */
$gearman->setWorkerConfig(
    [
        'SampleWorker' => [
            // The workers classname including namespace:
            'classname' => 'Worker\SampleWorker',
            // Workers filename. Worker has to be placed insite the "worker path" defined above:
            'filename' => 'SampleWorker.php',
            // Defines how many instances of this worker will be started:
            'instances' => 1,
        ],
        'EternalWorker' => [
            // The workers classname including namespace:
            'classname' => 'Worker\EternalWorker',
            // Workers filename. Worker has to be placed insite the "worker path" defined above:
            'filename' => 'EternalWorker.php',
            // Defines how many instances of this worker will be started:
            'instances' => 1,
        ]
    ]
);

/**
 * Now use Angela to manage your workers.
 */
$action = $argv[1];
switch ($action) {

    // Starts worker processes as defined in your worker configuration:
    case 'start':
        echo "Starting workers...\t";
        echo (($gearman->start() === true) ? '[OK]' : '[FAILED]') . PHP_EOL;
        break;

    // Starts worker process (Passing working name):
    case 'start_worker':
        echo "Starting {$argv[2]}...\t";
        echo (($gearman->start($argv[2]) === true) ? '[OK]' : '[FAILED]') . PHP_EOL;
        break;

    // Stops worker processes:
    case 'stop':
        echo "Stopping workers...\t";
        echo (($gearman->stop() === true) ? '[OK]' : '[FAILED]') . PHP_EOL;
        break;

    // Stop worker process (Passing working name):
    case 'stop_worker':
        echo "Stopping {$argv[2]}...\t";
        echo (($gearman->stopWorker($argv[2]) === true) ? '[OK]' : '[FAILED]') . PHP_EOL;
        break;

    // Restarts worker processes:
    case 'restart':
        echo "Restarting workers...\t";
        echo (($gearman->restart() === true) ? '[OK]' : '[FAILED]') . PHP_EOL;
        break;

    // Restars processes if frozen or crashed so you always have your configured number of processes:
    case 'keepalive':
        $gearman->keepalive();
        break;

    // Checks status of your workers:
    case 'status':
        $response = $gearman->status();
        if (empty($response)) {
            echo "No workers running." . PHP_EOL;
            exit;
        }
        echo "\n### Currently active workers:\n\n";
        foreach ($response as $workerName => $workerData) {

            if(isset($argv[2])){
                if(strpos($workerName,$argv[2])!==false) {
                    if ($workerData === false) {
                        $responseString = 'not responding';
                    } else {
                        $responseString = 'Ping: ' . round($workerData['ping'], 4) . "s\t Jobs: " . $workerData['jobs_total'] .
                            ' (' . $workerData['avg_jobs_min'] . '/min) ';
                    }
                    echo $workerName . ":\t\t[" . $responseString . "]" . PHP_EOL;
                }

            }else{
                if ($workerData=== false) {
                    $responseString = 'not responding';
                } else {
                    $responseString = 'Ping: ' . round($workerData['ping'], 4)."s\t Jobs: " . $workerData['jobs_total'] .
                        ' ('. $workerData['avg_jobs_min'] . '/min) ';
                }
                echo $workerName . ":\t\t[" . $responseString . "]" . PHP_EOL;
            }

        }
        break;
    case "check_server":
        echo "Server status...\t";
        echo (($gearman->managerIsRunning() === true) ? '[UP]' : '[DOWN]') . PHP_EOL;
        break;
    default:
        exit('Invalid action. Valid actions are: start|stop|restart|keepalive' . PHP_EOL);
        break;
}
