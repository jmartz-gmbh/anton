<?php

namespace Anton;

class Helper{

    public $config = [];

    public function __construct(){
        $this->loadConfig();
    }

    public function loadConfig(){
        $filename = 'storage/anton.json';
        if(file_exists($filename)){
            $file = file_get_contents($filename);
            $this->config = json_decode($file, true);
        }
    }

    public function getConfig(){
        return $this->config;
    }

    public function getProjectConfig(string $project){
        return $this->config[$project];
    }
}