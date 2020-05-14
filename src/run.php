<?php
require_once('vendor/autoload.php');

if(count($argv) == 2 && $argv[1] == 'collect'){
    $collector = new \Anton\Collector();
    $collector->run('all');
}
elseif(count($argv) == 3 && $argv[1] == 'collect'){
    $collector = new \Anton\Collector();
    $collector->run($argv[2]);
}
elseif(count($argv) == 2 && $argv[1] == 'jobber'){
    $jobber = new \Anton\Jobber();
    $jobber->run();
}
elseif(count($argv) == 4 && $argv[1] == 'trigger'){
    $collector = new \Anton\Collector();
    $collector->run($argv[2]);

    $trigger = new \Anton\Build($argv[2],$argv[3]);
    $trigger->run();
}
else{
    echo 'Anton Help. usage:'.PHP_EOL;
    echo 'to collect Anton config do -> ';
    echo './anton.sh collect'.PHP_EOL;
    echo 'to execute Jobs in queue do -> ';
    echo './anton.sh jobber'.PHP_EOL;
    echo 'to trigger Anton build do -> ';
    echo './anton.sh trigger [project] [branch]'.PHP_EOL;
}