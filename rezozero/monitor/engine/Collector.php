<?php
/*
 * Copyright REZO ZERO 2014
 *
 *
 *
 * @file Collector.php
 * @copyright REZO ZERO 2014
 * @author Ambroise Maupate
 */
namespace rezozero\monitor\engine;

use \rezozero\monitor\engine\Crawler;
use \rezozero\monitor\engine\PersistedData;

class Collector
{
	private $urls;
	private $statuses;
	private $parsers;
	private $conf;
	private $data;

	private $multiHandle;

	public function __construct($confURL, &$CONF, PersistedData &$data)
	{
		$this->data = $data;
		$this->conf = $CONF;
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
				foreach ($this->urls['sites'] as $key => $value)
				{
					$this->parsers[$key] = new Crawler($value, $this->conf, $this->data);
					// Add parser curl handle to the multiCurl
					curl_multi_add_handle($this->multiHandle, $this->parsers[$key]->getCurlHandle());
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
			usleep(10);
			curl_multi_exec($this->multiHandle, $running);
		} while ($running > 0);
	}

	public function parseAll()
	{
		foreach ($this->parsers as $key => $parser) {
			$parser->parse();
			$urlData = $parser->getPersistableData();
			$this->statuses[] = $urlData;
			$this->data->setSiteData($parser->getUrl(), $urlData);

			curl_multi_remove_handle($this->multiHandle, $parser->getCurlHandle());
		}

		curl_multi_close($this->multiHandle);

		$this->data->writeData();
	}

	public function getStatuses()
	{
		usort($this->statuses, function($a, $b) {
			if ( is_string($a['status']) ) {
				return -1;
			}
			else if ( is_string($b['status']) ) {
				return 1;
			}
			else {

				if ($a['status'] == $b['status']) {
			        return 0;
			    }

			    return ($a['status'] < $b['status']) ? 1 : -1 ;
			}

		});

		return $this->statuses;
	}
}
