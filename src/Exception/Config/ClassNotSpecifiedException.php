<?php

namespace Andaniel05\ObjectContainerTrait\Exception\Config;

class ClassNotSpecifiedException extends \Exception
{
    public function __construct()
    {
        parent::__construct('No se ha especificado la clase.');
    }
}