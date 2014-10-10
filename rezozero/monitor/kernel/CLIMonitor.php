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
 * @file CLIMonitor.class.php
 * @copyright REZO ZERO 2013
 * @author Ambroise Maupate
 */

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
