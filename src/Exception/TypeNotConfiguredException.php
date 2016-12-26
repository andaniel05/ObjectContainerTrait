<?php

namespace Andaniel05\ObjectContainerTrait\Exception;

class TypeNotConfiguredException extends \Exception
{
    public function __construct(string $type)
    {
        parent::__construct("El contenedor no está configurado para soportar el tipo $type.");
    }
}