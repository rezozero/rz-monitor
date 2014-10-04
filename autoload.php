<?php
/**
 * Copyright REZO ZERO 2013
 *
 * This work is licensed under a Creative Commons Attribution-NonCommercial-NoDerivs 3.0 Unported License.
 *
 * Ce(tte) œuvre est mise à disposition selon les termes
 * de la Licence Creative Commons Attribution - Pas d’Utilisation Commerciale - Pas de Modification 3.0 France.
 *
 * To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-nd/3.0/
 * or send a letter to Creative Commons, 444 Castro Street, Suite 900, Mountain View, California, 94041, USA.
 *
 *
 * @file vendor_autoload.php
 * @copyright REZO ZERO 2013
 * @author Ambroise Maupate
 */


function vendor_autoload( $class )
{
	$root = dirname(__FILE__);

	$parsedPath = $root.'/'.str_replace('\\', '/', $class).'.class.php';

	if (file_exists($parsedPath)) {

		include_once $parsedPath;
	}

}
spl_autoload_register('vendor_autoload');
