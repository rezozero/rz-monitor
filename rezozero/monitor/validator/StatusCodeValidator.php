<?php
/**
 * Copyright REZO ZERO 2015
 *
 *
 *
 * @file StatusCodeValidator.php
 * @copyright REZO ZERO 2015
 * @author Ambroise Maupate
 */
namespace rezozero\monitor\validator;

use rezozero\monitor\exception\WebsiteDownException;

class StatusCodeValidator implements ValidatorInterface
{
    protected $statusCode;

    public function __construct($statusCode)
    {
        $this->statusCode = (int) $statusCode;
    }

    /**
     * @return void
     * @throws WebsiteDownException
     */
    public function validate()
    {
        if ($this->statusCode < 200 ||
            $this->statusCode > 301) {
            throw new WebsiteDownException('Website status code is higher than 301');
        }
    }
}
