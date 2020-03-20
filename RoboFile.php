<?php // all tasks are defined in RoboFile.php
include('.robo/vendor/autoload.php');

use JmartzGmbh\RoboConfig;

class RoboFile {
    use RoboConfig;

    public function generateProductionConfig(){
        $this->configSave([
                'host' => '',
                'user' => '',
                'version' => date('d-m-y-H-i'),
                'domain' => 'anton.jmartz.gmbh',
            ]);
    }
}