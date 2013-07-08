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
 * @file index.php
 * @copyright REZO ZERO 2013
 * @author Ambroise Maupate
 */

define('BASE_FOLDER', dirname(__FILE__));

include_once(BASE_FOLDER.'/autoload.php');

/*
 * Conf
 */
$confFile = file_get_contents(BASE_FOLDER.'/conf/conf.json');
$CONF = json_decode($confFile, true);


if(defined('STDIN') ) {

	while (true) {
		# infinite loop
		$collector = new \rezozero\monitor\engine\Collector('sites.json');

		$output = new \rezozero\monitor\view\CLIOutput();
		$output->parseArray($collector->getStatuses());
		echo $output->output();
		  
		sleep((int)$CONF['delay']);
	}
}
else {
	$collector = new \rezozero\monitor\engine\Collector('sites.json');
	$output = new \rezozero\monitor\view\HTMLOutput();
	$output->parseArray($collector->getStatuses());
	echo $output->output();
	  

	exit();
}

?>