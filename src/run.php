<?php

include('Trigger.php');
include('Collector.php');

if(count($argv) == 2 && $argv[1] == 'collect'){
    $collector = new \Anton\Collector();
    $collector->run();
}
elseif(count($argv) == 4 && $argv[1] == 'trigger'){
    $trigger = new \Anton\Trigger();
    $trigger->setProject($argv[2]);
    $trigger->setBranch($argv[3]);
    $trigger->run();
}
else{
    echo 'Anton Help. usage:'.PHP_EOL;
    echo 'to collect Anton config do -> ';
    echo './anton.sh collect'.PHP_EOL;
    echo 'to trigger Anton build do -> ';
    echo './anton.sh trigger [project] [branch]'.PHP_EOL;
}