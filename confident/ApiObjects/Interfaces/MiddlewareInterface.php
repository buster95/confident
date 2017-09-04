<?php

namespace Confident\ApiObjects\Interfaces;

interface MiddlewareInterface
{
    public function setStatusOnFail($status);

    public function setMessageOnFail($message);
    
    public function showFailMessage();

    public function run();
}
