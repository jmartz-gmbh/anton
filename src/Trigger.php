<?php

namespace Anton;

class Trigger {
    public string $project = '';

    public string $branch = '';

    public function run(){
        echo 'Hallo';
    }
}