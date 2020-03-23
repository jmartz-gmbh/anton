<?php

namespace Anton;

class Trigger {
    public $project = '';

    public $branch = '';

    public $build = 'failed';

    public function run(string $project, string $pipeline){
        $filename = 'anton.json';

        try {
            if(file_exists($filename)){
                $file = file_get_contents($filename);
    
                $config = json_decode($file, true);
                if(!empty($config[$project])){
                    $projectConfig = $config[$project];
                    $workdir = 'workspace/projects/'.$project;
    
                    if(file_exists($workdir) && is_dir($workdir)){
                        if(!empty($projectConfig['pipelines'][$pipeline])){
                            $branch = $projectConfig['pipelines'][$pipeline];   
                            exec('cd '.$workdir.' && git checkout '.$branch);
        
                            $commits = exec('cd '.$workdir.' && git rev-list --count '.$branch);
                            exec('cd '.$workdir.' && git pull');
        
                            $steps = [];
        
                            try {
                                if(count($projectConfig['steps']) > 0){
                                    exec('cd '.$workdir.' && mkdir -p .anton/log');
                                    foreach($projectConfig['steps'] as $key => $value){
                                        $steps[$key] = [];
                                        $steps[$key]['log'] = [];
                                        $logfile = '.anton/log/'.$key.'.log';                    
                                        exec('cd '.$workdir.' && robo '.$value . ' 2>&1 | tee '.$logfile);
                                        
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
                                    }
                                }
                            }
                            catch (\Exception $e){
                                die($e->getMessage());
                            }
                            
                            // check if success
                            exec('cd '.$workdir.' && robo check:build 2>&1 | tee .anton/log/check.log');
        
                            $check = file_get_contents($workdir. '/.anton/log/check.log');
                            
                            if($check === 'success'){
                                exec('cd '.$workdir.' && rm -rf .anton/log');
                                exec('rm -rf '.$workdir);
                            }
                            else{
                                throw new \Exception('Step failed. ('.$key.')');
                            }
                        }
                        else{
                            throw new \Exception('Pipeline unknown. ('.$pipeline.')');
                        }
                    }
                    else{
                        throw new \Exception('Workdir doesnt exits.');
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
            echo 'Build successfull';
            
        } catch(\Exception $e){
            echo $e->getMessage().PHP_EOL;
            exit(0);
        }
    }
}