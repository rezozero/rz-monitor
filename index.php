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
use \rezozero\monitor\view;
use \rezozero\monitor\kernel\Router;
use \rezozero\monitor\engine\Collector;
use \rezozero\monitor\view\CLIOutput;


define('BASE_FOLDER', dirname(__FILE__));
include_once(BASE_FOLDER.'/autoload.php');

/*
 * Conf
 */
$confFile = file_get_contents(BASE_FOLDER.'/conf/conf.json');
$CONF = json_decode($confFile, true);

$tokens = Router::parseQueryString();

/*
 * Command line utility with infinite crawl loop
 */
if(defined('STDIN') ) {

	$output = new view\CLIOutput();
	$colors = new view\Colors();

	CLIOutput::echoAT(0,0,
		$colors->getColoredString('Please wait for RZ Monitor to crawl your websites', 'white', 'black'));

	while (true) {
		# infinite loop
		$collector = new Collector('sites.json');

		$output->parseArray($collector->getStatuses());
		system("clear");
		echo $output->output();

		$output->flushContent();

		printf(_('Next crawl in %d seconds')."\n\r", (int)$CONF['delay']);
		
		unset($collector);

		sleep((int)$CONF['delay']);
	}
}
/*
 * Simple table view for Panic StatusBoard™ iOS app
 *
 * Just call yourdomain.com/table
 */
else if (isset($tokens[0]) && $tokens[0] == 'table') {
	$collector = new Collector('sites.json');
	$output = new view\TableOutput();
	$output->parseArray($collector->getStatuses());
	echo $output->output();
	  
	exit();
}
/*
 * HTML view for internet browsers
 */
else {
	$collector = new Collector('sites.json');
	$output = new view\HTMLOutput();
	$output->parseArray($collector->getStatuses());
	echo $output->output();
	  

	exit();
}


?>