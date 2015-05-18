<?php
/**
 * Copyright REZO ZERO 2015
 *
 *
 *
 *
 * @file ParserInterface.php
 * @copyright REZO ZERO 2015
 * @author Ambroise Maupate
 */
namespace rezozero\monitor\parser;

interface ParserInterface
{
    public function parse($data, array &$storage);
}