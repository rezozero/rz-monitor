<?php
/*
 * Copyright REZO ZERO 2014
 *
 *
 *
 * @file CLIOutput.php
 * @copyright REZO ZERO 2014
 * @author Ambroise Maupate
 */
namespace rezozero\monitor\view;

use rezozero\monitor\view;
use rezozero\monitor\engine;

class CLIOutput
{
	private $header = "";
	private $footer = "";
	private $content = "";
	private $settings;

	private static $columnWidth = array(
		'url'=>60,
		'time'=>8,
		'avg'=>8,
		// 'totalTime'=>8,
		'crawlCount'=>5,
		'successCount'=>5,
		'code'=>5,
		'failCount'=>5,
		'status'=>6,
		'cms_version'=>18
	);

	function __construct()
	{
		system("clear");

		$this->header();
		$this->footer();

		$this->settings = array();
		$this->settings['screen'] = array();

		$this->settings['screen']['width'] = exec('tput cols');
		$this->settings['screen']['height'] = exec('tput lines');

	}

	public function header()
	{
		$colors = new Colors();

		ob_start();
		static::echoAT(0,0,$colors->getColoredString('################## REZO ZERO Monitor '.date('Y-m-d H:i:s').' ##################', 'white', 'black'));

		$this->header = ob_get_clean();
		return $this->header;
	}

	public function footer()
	{
		ob_start();

		$this->footer = ob_get_clean();
		return $this->footer;
	}

	public function content( $html )
	{
		$this->content .= $html;
		return $this->content;
	}

	public function parseArray( $arr )
	{
		$line = 2;
		$colors = new Colors();

		foreach ($arr as $ckey => $crawler) {
			$addedCol = 0;
			$this->content("\n# ");

			krsort($crawler);
			$linecolor = 'green';
			$bckcolor = null;

			if ($line > 2 && $line%2 == 0) {
				$linecolor = 'light_green';
			}

			if ($line == 2) {
				$linecolor = 'white';
				$bckcolor = 'green';
			}
			else if ($crawler['status'] != \rezozero\monitor\engine\Crawler::STATUS_ONLINE) {
				$bckcolor = 'red';
				$linecolor = 'white';
			}
			else if ($crawler['time'] > 2) {
				$bckcolor = 'yellow';
				$linecolor = 'black';
			}



			foreach ($crawler as $key => $value) {

				/*
				 * If not in column width donot display
				 */
				if (!in_array($key, array_keys(static::$columnWidth))) {
					continue;
				}

				if ($line > 2) {
					switch (strtolower($key)) {
						case 'status':
							if ($value == \rezozero\monitor\engine\Crawler::STATUS_ONLINE) {
								$value = _('Online');
							}
							else if ($value == \rezozero\monitor\engine\Crawler::STATUS_DOWN) {
								$value = _('Down');
							}
							else {
								$value = _('Failed');
							}
							break;
						case 'time':
							$value = sprintf('%.3fs', (float)$value);
							break;
						case 'totalTime':
							$value = sprintf('%.3fs', (float)$value);
							break;
						case 'avg':
							$value = sprintf('%.3fs', (float)$value);
							break;
						case 'url':
							$value = str_replace("http://", "", $value);
							$value = str_replace("www.", "", $value);
							$value = str_replace(".com", "", $value);
							$value = str_replace(".net", "", $value);
							$value = str_replace(".org", "", $value);
							$value = str_replace(".eu", "", $value);
							$value = str_replace(".fr", "", $value);
							break;

						default:
							# code...
							break;
					}
				}

				$this->content("\033[".($line*1).";".($addedCol)."H".$colors->getColoredString($value, $linecolor, $bckcolor));
				$addedCol += static::$columnWidth[$key];


				$this->content("\033[".($line*1).";".($addedCol)."H"." | ");
				$addedCol += 3;
			}

			$line++;
		}
	}

	public function output()
	{
		return $this->header().$this->content.$this->footer()."\n\n";
	}

	public function flushContent()
	{
		$this->content = '';
		return true;
	}

	public static function echoAT($Row,$Col,$prompt="") {
	    // Display prompt at specific screen coords
	    echo "\033[".$Row.";".$Col."H".$prompt;
	}
	public function cleanScreen()
	{
		for ($i=0; $i < $this->settings['screen']['width']; $i++) {
			for ($j=0; $j < $this->settings['screen']['height']; $j++) {
				static::echoAT($i, $j, ' ');
			}
		}
	}
}
