<?php
namespace Anton;
include('Validator.php');

class Collector
{
    public function __construct(){
        $this->validator = new \Anton\Validator();
    }

    public function getLog(string $project, string $name)
    {
        $filename = $this->folders['config']. '/'.$this->folders['tmp'].'/'.$project.'/' . $name . '.log';
        if (file_exists($filename)) {
            $log = file_get_contents($filename);
            $log = trim($log);
            $log = trim($log, PHP_EOL);
    
            return $log;
        }
    
        return [];
    }

    public function getProjectConfig(string $name, string $type)
    {
        $filename = $this->folders['config']. '/'.$this->folders['tmp'].'/' .$name. '/.anton/'.$type.'.json';
        if (file_exists($filename)) {
            $file = file_get_contents($filename);
            $pipelines = (array) json_decode($file, true);
            if (count($pipelines) > 0) {
                return $pipelines;
            }
        }
        return false;
    }

    public function getConfig(string $name)
    {
        $filename = $this->folders['config']. '/projects.json';
        if (file_exists($filename)) {
            $file = file_get_contents($filename);
            $data = json_decode($file, true);

            if(!empty($data[$name])){
                return $data[$name];
            }
        }
    
        return [];
    }

    public function cloneProjectRepo(string $project,array $tmp){
        $folder = $this->folders['config'].'/'.$this->folders['tmp'].'/'.$project;
        $goDir = 'cd '.$this->folders['config'].'/'.$this->folders['tmp'];
        $cloneRepo = 'git clone '.$tmp['config']['repo'];

        if (!file_exists($folder) && !is_dir($folder)) {
            exec($goDir . ' && ' . $cloneRepo . ' 2>&1 | tee '. $tmp['name'] .'/config.log');
        }
    }

    public $folders = [
        'config' => 'workspace',
        'tmp' => 'projects'
    ];

    public $filename = [
        'config' => 'config'
    ];

    public function run()
    {
        try{
            $projects = $this->getJsonFileArray($this->folders['config'].'/config.json');
            if ($projects) {
                foreach ($projects as $key => $project) {
                    $tmp = $this->generateTmpConfig($project);
                    $this->cloneProjectRepo($project, $tmp);
                    $config = $this->getJsonFileArray($this->folders['config']. '/projects.json');
                    if ($config[$project]) {    
                        $anton[$project] = $this->generateProjectConfig($project,$config[$project]);
                    }
                }
            }

            $save = $this->saveAntonConfig($anton);
            if(!$save){
                echo 'Collect Data Successfull.'.PHP_EOL;
            }
            else{
                echo 'Collect Data Failed.'.PHP_EOL;
            }
        } catch (\Exception $e){
            $this->somethingWentWrong();
            echo 'Collect Data Failed.'.PHP_EOL;
        }
    }

    public function generateTmpConfig(string $project){
        $tmp = [];
        $tmp['name'] = $project;
        $tmp['config'] = $this->getConfig($project);

        return $tmp;
    }

    public function somethingWentWrong(){
        echo 'something went wrong';
    }

    public function getJsonFileArray(string $filename):?array{
        if (file_exists($filename)) {
            $file = file_get_contents($filename);
            return json_decode($file, true);
        }

        return false;
    }

    public function generateProjectConfig(string $project, array $config){
        // @todo merge configs together in one file
        $data = [];
        $data['project'] = $config;
        $data['pipelines'] = $this->getProjectConfig($project, 'pipelines');
        $data['servers'] = $this->getProjectConfig($project, 'servers');
        $data['steps'] = $this->getProjectConfig($project, 'steps');

        return $data;
    }

    public function saveAntonConfig(array $anton){
        $this->validator->validate($anton);
        if(!$this->validator->hasErrors()){
            file_put_contents('anton.json', json_encode($anton, JSON_UNESCAPED_SLASHES));
            return false;
        }

        return true;
    }
}
