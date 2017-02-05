<?php

namespace Hibot\Bot\Actions;


abstract class Action
{

    protected $data;

    protected $request;

    public function __construct($data, array $request)
    {
        $this->data = $data;
        $this->request = $request;
    }

    abstract public function run();

    /*
    public function respond() {
        //
    }
    */
}