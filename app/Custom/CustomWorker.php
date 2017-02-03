<?php

namespace App\Custom;


abstract class CustomWorker {

    abstract public function run($requestData, ...$params);
}