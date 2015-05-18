<?php
/**
 * Copyright REZO ZERO 2015
 *
 *
 *
 * @file ValidatorInterface.php
 * @copyright REZO ZERO 2015
 * @author Ambroise Maupate
 */
namespace rezozero\monitor\validator;

interface ValidatorInterface
{
    /**
     * @return void
     * @throws WebsiteDownException
     */
    public function validate();
}