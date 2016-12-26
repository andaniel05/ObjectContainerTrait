<?php

namespace Andaniel05\ObjectContainerTrait\Exception\Config;

class SingularNameNotSpecifiedException extends \Exception
{
    public function __construct()
    {
        parent::__construct('No se ha especificado el nombre en singular de los elementos que se van a almacenar.');
    }
}