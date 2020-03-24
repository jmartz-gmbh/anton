<?php

namespace Anton;

class Trigger {
    public $project = '';
    public $pipeline = '';

    public $build = [
        'status' => 'failed',
        'number' => null
    ];

    public $log = [
        'items' => []
    ];

    public function addLog(string $message){
        $filename = 'storage/builds/'.$this->project.'.log';
        
        if($this->build['number'] === null){
            // @todo create build item
        }

        $this->log['items'][] = $message;
        $this->saveBuildLog();
    }

    public function saveBuildLog(){
        // @todo get build log
        // @todo update item


        // @bug? 2 jobs edit log at same time, lock file ?
        file_put_contents('storage/builds/'.$this->project.'.log', \json_encode($this->log));
    }

    public function run(string $project, string $pipeline){
        $filename = 'storage/anton.json';
        // @todo add build log

        try {
            if(file_exists($filename)){
                $file = file_get_contents($filename);
    
                $config = json_decode($file, true);
                if(!empty($config[$project])){
                    $projectConfig = $config[$project];
                    $workdir = 'workspace/projects/'.$project;

                    if(file_exists($workdir) && is_dir($workdir)){
                        if(!empty($projectConfig['pipelines'][$pipeline])){
                            $this->project = $project;
                            $this->pipeline = $pipeline;
                            $this->addLog('Build started '.time());

                            $branch = $projectConfig['pipelines'][$pipeline];   
                            exec('cd '.$workdir.' && git checkout '.$branch. ' 2>&1');
        
                            // @todo add commits to log file for builds
                            $commits = exec('cd '.$workdir.' && git rev-list --count '.$branch);
                            exec('cd '.$workdir.' && git pull'. ' 2>&1');
        
                            $steps = [];
                            $logfolder = 'storage/logs/'.$project;
                            exec('mkdir -p '.$logfolder);
        
                            try {
                                if(count($projectConfig['steps']) > 0){
                                     foreach($projectConfig['steps'] as $key => $value){
                                        // @todo use step key for logile
                                        $steps[$key] = [];
                                        $steps[$key]['log'] = [];
                                        $logfile = '../../../'.$logfolder.'/'.$key.'.log';
                                        // @todo different steps by branch? robo detect branch ?              
                                        exec('cd '.$workdir.' && robo '.$value['command'] . ' 2>&1 | tee '.$logfile);
                                        
                                        $steps[$key]['log']['exists'] = file_exists($workdir.'/'.$logfile);
                        
                                        if($steps[$key]['log']['exists']){
                                            $steps[$key]['log']['text'] = trim(trim(file_get_contents($workdir.'/'.$logfile), PHP_EOL));
                        
                                            $steps[$key]['log']['exit'] = strpos($steps[$key]['log']['text'], 'Exit code ') !== false;
                                            $steps[$key]['log']['exception'] = strpos($steps[$key]['log']['text'], '[error]') !== false;
                                            // @todo catch ssh errors ?
        
                                            if($steps[$key]['log']['exception']){
                                                throw new \Exception('Exception thrown while deployment.');
                                            }
        
                                            if($steps[$key]['log']['exit']){
                                                throw new \Exception('Exit while deployment.');
                                            }
                                        }
                                        else{
                                            throw new \Exception('Log not created. ('.$workdir.'/'.$logfile.')');
                                        }

                                        // @todo save step log
                                    }
                                }
                            }
                            catch (\Exception $e){
                                die($e->getMessage());
                            }
                            
                            exec('cd '.$workdir.' && robo check:build 2>&1 | tee ../../../storage/logs/'.$project.'/status.log');
        
                            $check = file_get_contents($logfolder.'/status.log');

                            $check = trim(trim($check, PHP_EOL));
                            
                            if($check !== 'success'){
                                throw new \Exception('Step failed. ('.$key.')');
                            }
                            else{
                                // @todo mark build as success
                                exec('rm -rf storage/logs/'.$project);
                            }
                        }
                        else{
                            throw new \Exception('Pipeline unknown. ('.$pipeline.')');
                        }
                    }
                    else{
                        throw new \Exception('Workdir doesnt exists.'.PHP_EOL);
                    }
                }
                else{
                    throw new \Exception('Project unknown. ('.$project.')');
                }
            }
            else{
                throw new \Exception('Anton.json missing.');
            }

            $this->build = 'success';
            echo 'Build successfull.'.PHP_EOL.PHP_EOL;
            
        } catch(\Exception $e){
            echo $e->getMessage().PHP_EOL;
            exit(0);
        }
    }
}