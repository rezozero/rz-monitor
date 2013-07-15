<?php 
namespace rezozero\monitor\engine;

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
 * @file Collector.class.php
 * @copyright REZO ZERO 2013
 * @author Ambroise Maupate
 */


class Collector
{
	private $urls;
	private $statuses;
	private $parsers;

	private $multiHandle;

	public function __construct( $confURL )
	{	
		
		$this->statuses = array(
			0 => array(
				'url'=>			_('Website'),
				'time'=>		_('Time'),
				'connect_time'=>_('Connexion time'),
				'avg'=>			_('AVG'),
				'totalTime'=>	_('Total'),
				'crawlCount'=>	_('Crawls'),
				'code'=>		_('Code'),
				'successCount'=>_('Success'),
				'failCount'=>	_('Fail'),
				'status'=>		_('Status'),
				'lastest'=>		_('Latest crawl'),
				'cms_version'=>	_('CMS version'),
				'effective_url'=>_('Effective URL')
			)
		);

		$this->multiHandle = curl_multi_init();
		$this->parsers = array();

		if (file_exists(BASE_FOLDER.'/conf/'.$confURL)) 
		{
			$content = file_get_contents(BASE_FOLDER.'/conf/'.$confURL);

			$this->urls = json_decode($content, true);

			if ($this->urls !== null && is_array($this->urls)) 
			{
				foreach ($this->urls as $key => $value) 
				{
					$this->parsers[$key] = new \rezozero\monitor\engine\Crawler( $value );

					// Add parser curl handle to the multiCurl
					curl_multi_add_handle($this->multiHandle, $this->parsers[$key]->getCurlHandle()); 


					/*$this->statuses[] = $this->parsers[$key]->getVariables();

					unset($this->parsers[$key]);*/
				}
				$this->execRequests();
				$this->parseAll();
			}
		}
	}

	public function execRequests()
	{	
		$running = NULL; 

		/*
		 * execute requests
		 */
		do {
			//usleep(10);
			curl_multi_exec($this->multiHandle, $running);
		} while ($running > 0);
	}

	public function parseAll()
	{
		foreach ($this->parsers as $key => $parser) {
			$parser->parse();
			$this->statuses[] = $parser->getVariables();
		
			curl_multi_remove_handle($this->multiHandle, $parser->getCurlHandle());

			unset($this->parsers[$key]);
		}

		curl_multi_close($this->multiHandle); 
	}

	public function getStatuses()
	{
		return $this->statuses;
	}
}

 ?>