<?php
/**
 * Copyright REZO ZERO 2015
 *
 *
 *
 * @file EmptyDataValidator.php
 * @copyright REZO ZERO 2015
 * @author Ambroise Maupate
 */
namespace rezozero\monitor\validator;

use rezozero\monitor\exception\WebsiteDownException;

class EmptyDataValidator extends AbstractDataValidator
{
    /**
     * @return void
     * @throws WebsiteDownException
     */
    public function validate()
    {
        if ($this->data === '') {
            throw new WebsiteDownException('Website data is empty');
        }
    }
}
