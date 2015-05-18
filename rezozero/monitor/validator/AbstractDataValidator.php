<?php
/**
 * Copyright REZO ZERO 2015
 *
 *
 *
 * @file AbstractDataValidator.php
 * @copyright REZO ZERO 2015
 * @author Ambroise Maupate
 */
namespace rezozero\monitor\validator;

abstract class AbstractDataValidator implements ValidatorInterface
{
    protected $data;

    public function __construct($data)
    {
        $this->data = trim($data);
    }
}
