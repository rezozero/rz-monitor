<?php
/**
 * Copyright REZO ZERO 2015
 *
 *
 *
 * @file GeneratorParser.php
 * @copyright REZO ZERO 2015
 * @author Ambroise Maupate
 */
namespace rezozero\monitor\parser;

class GeneratorParser implements ParserInterface
{
    public function parse($data, array &$storage)
    {
        $cmsVersion = array();
        if(preg_match("/\<meta name\=\"generator\" content\=\"([^\"]+)\"/", $data, $cmsVersion) > 0) {
            $storage['cms_version'] = $cmsVersion[1];
        }
    }
}
