<?php 
namespace rezozero\monitor\view;

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
 * @file CLIOutput.class.php
 * @copyright REZO ZERO 2013
 * @author Ambroise Maupate
 */

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
		'totalTime'=>8,
		'crawlCount'=>5,
		'successCount'=>5,
		'code'=>5,
		'failCount'=>5,
		'status'=>6,
		'cms_version'=>18
	);
	
	function __construct()
	{
		$this->header();
		$this->footer();

		$this->settings = array();
		$this->settings['screen'] = array();

		$this->settings['screen']['width'] = exec('tput cols');
		$this->settings['screen']['height'] = exec('tput lines');

		system("clear");
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
			$col = 0;
			$linecolor = 'green';
			$bckcolor = null;

			if ($line == 2) {
				$linecolor = 'white';
				$bckcolor = 'green';
			}
			else if ($crawler['status'] != 'Online') {
				$bckcolor = 'red';
				$linecolor = 'white';
			}
			else if ($crawler['time'] > 2) {
				$bckcolor = 'yellow';
				$linecolor = 'black';
			}

			if ($line > 2 && $line%2 == 0) {
				$linecolor = 'light_green';
			}


			foreach ($crawler as $key => $value) {

				/*
				 * If not in column width donot display
				 */
				if (!in_array($key, array_keys(static::$columnWidth))) {
					continue;
				}

				if ($line > 2) {
					switch ($key ) {
						case 'status':
							if ($value == \rezozero\monitor\engine\Crawler::STATUS_ONLINE) {
								$value = _('Online');
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
		return $this->header.$this->content.$this->footer."\n\n";
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

class Colors {
	private $foreground_colors = array();
	private $background_colors = array();

	public function __construct() {
		// Set up shell colors
		$this->foreground_colors['black'] = '0;30';
		$this->foreground_colors['dark_gray'] = '1;30';
		$this->foreground_colors['blue'] = '0;34';
		$this->foreground_colors['light_blue'] = '1;34';
		$this->foreground_colors['green'] = '0;32';
		$this->foreground_colors['light_green'] = '1;32';
		$this->foreground_colors['cyan'] = '0;36';
		$this->foreground_colors['light_cyan'] = '1;36';
		$this->foreground_colors['red'] = '0;31';
		$this->foreground_colors['light_red'] = '1;31';
		$this->foreground_colors['purple'] = '0;35';
		$this->foreground_colors['light_purple'] = '1;35';
		$this->foreground_colors['brown'] = '0;33';
		$this->foreground_colors['yellow'] = '1;33';
		$this->foreground_colors['light_gray'] = '0;37';
		$this->foreground_colors['white'] = '1;37';

		$this->background_colors['black'] = '40';
		$this->background_colors['red'] = '41';
		$this->background_colors['green'] = '42';
		$this->background_colors['yellow'] = '43';
		$this->background_colors['blue'] = '44';
		$this->background_colors['magenta'] = '45';
		$this->background_colors['cyan'] = '46';
		$this->background_colors['light_gray'] = '47';
	}

	// Returns colored string
	public function getColoredString($string, $foreground_color = null, $background_color = null) {
		$colored_string = "";

		// Check if given foreground color found
		if (isset($this->foreground_colors[$foreground_color])) {
			$colored_string .= "\033[" . $this->foreground_colors[$foreground_color] . "m";
		}
		// Check if given background color found
		if (isset($this->background_colors[$background_color])) {
			$colored_string .= "\033[" . $this->background_colors[$background_color] . "m";
		}

		// Add string and end coloring
		$colored_string .=  $string . "\033[0m";

		return $colored_string;
	}

	// Returns all foreground color names
	public function getForegroundColors() {
		return array_keys($this->foreground_colors);
	}

	// Returns all background color names
	public function getBackgroundColors() {
		return array_keys($this->background_colors);
	}
}

 ?>