<?php 
namespace rezozero\monitor\kernel;

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
 * @file Router.class.php
 * @copyright REZO ZERO 2013
 * @author Ambroise Maupate
 */


abstract class Router
{
	/**
	 * Parse query string and current url to get each url tokens in a single array
	 * 
	 * @return array URL tokens
	 */
	public static function parseQueryString()
	{
		//Remove request parameters:
		list($path) = explode('?', $_SERVER['REQUEST_URI']);

		//print_r($path);
		//echo(strlen(dirname($_SERVER['SCRIPT_NAME']))+1);
		//echo(dirname($_SERVER['SCRIPT_NAME']));

		//Remove script path:
		if (strlen(dirname($_SERVER['SCRIPT_NAME'])) == 1) {
			$path = substr($path, strlen(dirname($_SERVER['SCRIPT_NAME'])));
		}
		else {
			$path = substr($path, strlen(dirname($_SERVER['SCRIPT_NAME']))+1);
		}

		return (explode('/', $path));
	}
}

 ?>