<?php
namespace Anton;

class Jobber {

    public function run(){
        // trigger new jobs

        $filename = 'storage/jobber.json';
        if(!file_exists($filename)){
            file_put_contents($filename, '[]');
        }

        $jobs = $this->getJobs();

        if(count($jobs) > 0){
            foreach ($jobs as $key => $value) {
                $this->executeJob($value['name'], $value['pipeline']);
            }
        }
        else{
            echo 'No jobs found. '.PHP_EOL;
        }        

        echo 'Jobber run. '.PHP_EOL;
    }

    public function executeJob(){
        exec('./anton.sh trigger ms-tracking production');
    }

    public function getJobs(){
        $filename = 'storage/jobber.json';

        if(file_exists($filename)){
            $json = file_get_contents($filename);
            $data = json_decode($json, true);
            if(!empty($data)){
                return $data;
            }
        }
        else{
            \file_put_contents($filename, '{}');
        }

        return [];
    }

}