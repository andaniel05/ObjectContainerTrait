<?php

namespace Andaniel05\ObjectContainerTrait\Exception;

class NotAllowedTypeException extends \Exception
{
    public function __construct(string $allowedType, string $currentType)
    {
        parent::__construct(
            sprintf('El tipo permitido es "%s" y se está intentando insertar el tipo "%s"', $allowedType, $currentType)
        );
    }
}