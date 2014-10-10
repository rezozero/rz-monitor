<?php
/*
 * Copyright REZO ZERO 2014
 *
 *
 *
 * @file CLIMonitor.php
 * @copyright REZO ZERO 2014
 * @author Ambroise Maupate
 */
namespace rezozero\monitor\kernel;

use \rezozero\monitor\view;
use \rezozero\monitor\engine\Collector;
use \rezozero\monitor\view\CLIOutput;
use \rezozero\monitor\engine\PersistedData;

class CLIMonitor
{
	private $output;
	private $colors;
	private $collector;
	private $data;

	function __construct(&$CONF, PersistedData &$data)
	{
		$this->output = new view\CLIOutput();
		$this->colors = new view\Colors();
		$this->data = $data;

		CLIOutput::echoAT(
			0,
			0,
			$this->colors->getColoredString(
				_('Please wait for RZ Monitor to crawl your websites'),
				'white',
				'black'
			)
		);

		$this->collector = new Collector('sites.json', $CONF, $this->data);

		$this->output->parseArray($this->collector->getStatuses());
		system("clear");
		echo $this->output->output();

		$this->output->flushContent();
	}
}
