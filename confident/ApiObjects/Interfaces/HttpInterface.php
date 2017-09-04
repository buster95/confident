<?php

namespace Confident\ApiObjects\Interfaces;

interface HttpInterface
{
    public function setStatus($int = 200);
    public function setHeader($key, $value = '');
}
