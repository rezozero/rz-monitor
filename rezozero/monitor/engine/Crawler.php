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
 *
 * @file Crawler.class.php
 * @copyright REZO ZERO 2013
 * @author Ambroise Maupate
 */
namespace rezozero\monitor\engine;

use \rezozero\monitor\engine\PersistedData;

/**
 * Crawl a single website.
 */
class Crawler
{
	private $url;
	private $data;
	private $time;
	private $conf;
	private $variables;
	private $persistedData;

	private $curlHandle;

	const STATUS_ONLINE = 0;
	const STATUS_FAILED = 1;
	const STATUS_DOWN = 2;

	const HTTP_OK = 200;
	const HTTP_REDIRECT = 300;
	const HTTP_PERMANENT_REDIRECT = 301;

	function __construct($url, &$CONF, PersistedData &$persistedData)
	{
		$this->url = $url;
		$this->persistedData = $persistedData->getSiteData($this->url);

		$this->variables = array();
		$this->conf = $CONF;

		$this->variables['url'] = $url;
		$this->variables['cms_version'] = "";
		$this->variables['time'] = "";

		$this->initRequest();
	}

	/**
	 * Parse downloaded data to find HTTP Code and Meta generator
	 * @return void
	 */
	public function parse()
	{
		$this->getInfos();

		if ($this->variables['code'] >= static::HTTP_OK &&
			$this->variables['code'] <= static::HTTP_PERMANENT_REDIRECT) {
			/*
			 * Site is up
			 */
			$this->variables['status'] = static::STATUS_ONLINE;

			if (null !== $this->persistedData &&
					$this->persistedData['status'] == static::STATUS_DOWN) {
				/*
				 * If site was DOWN, we notify it's UP again.
				 */
				$this->notifyUp();
			}

			/*
			 * Parse generator data to get CMS version.
			 */
			if ($this->data != '')
			{
				$cmsVersion = array();
				if( preg_match("/\<meta name\=\"generator\" content\=\"([^\"]+)\"/", $this->data, $cmsVersion) > 0 ) {
					$this->variables['cms_version'] = $cmsVersion[1];
				}
			}

		} elseif (null !== $this->persistedData &&
					$this->persistedData['status'] == static::STATUS_FAILED) {
			/*
			 * Second time FAILED, site is now DOWN,
			 * we send a notification
			 */
			$this->variables['status'] = static::STATUS_DOWN;
			$this->notifyError();

		} elseif (null !== $this->persistedData &&
					$this->persistedData['status'] == static::STATUS_DOWN) {
			/*
			 * Site is already DOWN, do nothing
			 */
			$this->variables['status'] = static::STATUS_DOWN;

		} else {
			/*
			 * First failed for this site,
			 * we wait a second crawl to confirm.
			 */
			$this->variables['status'] = static::STATUS_FAILED;
		}
	}

	public function getVariables()
	{
		return $this->variables;
	}
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * Once piece download method
	 *
	 * @return [type] [description]
	 */
	public function download()
	{
		/* Check if cURL is available */
		if ($this->initRequest() === true) {

	        // exécution de la session
	        $this->data = curl_exec($this->curlHandle);
	        $info = curl_getinfo($this->curlHandle);

	        if ($this->data !== null && $this->data != '') {


		        // fermeture des ressources
		        curl_close($this->curlHandle);

	        	$this->variables['time'] = $info['starttransfer_time'];
	        	$this->variables['code'] = $info['http_code'];

	        	return true;
	        }
	        else {

	        	// fermeture des ressources
		        curl_close($ch);
	        	$this->variables['time'] = $info['starttransfer_time'];

		        return false;
	        }
		}
		else {
			return false;
		}
	}

	public function initRequest()
	{
		// initialisation de la session
		$this->curlHandle = curl_init();

		/* Check if cURL is available */
		if ($this->curlHandle !== FALSE) {
	        // configuration des options
	        curl_setopt($this->curlHandle, CURLOPT_URL,            $this->url);
	        curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($this->curlHandle, CURLOPT_SSL_VERIFYHOST, false);
       		curl_setopt($this->curlHandle, CURLOPT_VERBOSE,        false);
      		curl_setopt($this->curlHandle, CURLOPT_SSLVERSION,     3);
			curl_setopt($this->curlHandle, CURLOPT_SSL_VERIFYPEER, false);
	        curl_setopt($this->curlHandle, CURLOPT_FOLLOWLOCATION, TRUE);
	        curl_setopt($this->curlHandle, CURLOPT_TIMEOUT,        10);
	        curl_setopt($this->curlHandle, CURLOPT_USERAGENT,      "Mozilla/5.0 (Windows NT 5.1; rv:15.0) Gecko/20100101 Firefox/15.0.1");

	        if (defined("CURLOPT_IPRESOLVE")) {
	        	curl_setopt($this->curlHandle, CURLOPT_IPRESOLVE,  CURL_IPRESOLVE_V4);
	        }

	        return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Get infos from curl handler
	 * @return [type] [description]
	 */
	public function getInfos()
	{
		$this->data = curl_multi_getcontent($this->curlHandle);
		$info = curl_getinfo($this->curlHandle);

		if (isset($info['http_code'])) {
	   		$this->variables['code'] = (int) $info['http_code'];
		}
		if (isset($info['starttransfer_time'])) {
	   		$this->variables['time'] = $info['starttransfer_time'];
		}
		if (isset($info['connect_time'])) {
	   		$this->variables['connect_time'] = $info['connect_time'];
		}
		if (isset($info['effective_url'])) {
	   		$this->variables['effective_url'] = $info['effective_url'];
		}
		else {
			$this->variables['effective_url'] = "";
		}
		return $info;
	}
	/**
	 * Pass data to the crawler
	 * @param [type] $data [description]
	 */
	public function setRequestedData( $data )
	{
		$this->data = $data;
	}


	/**
	 * Close curl handler
	 * @return [type] [description]
	 */
	public function closeRequest()
	{
		curl_close($this->curlHandle);
	}

	/**
	 * Return current crawler curl handler
	 * @return [type] [description]
	 */
	public function &getCurlHandle()
	{
		return $this->curlHandle;
	}

	/**
	 * Send an email after a site has failed for the second time,
	 * then set it as DOWN.
	 */
	public function notifyError()
	{
		# Prev status was failed so we send mail
		$fromEmail = $this->conf['sender'];
		if (is_array($this->conf['mail'])) {
			$to = implode(', ', $this->conf['mail']);
		} else {
			$to = $this->conf['mail'];
		}
	    $subject = 'Monitor rezo-zero';
	    $message = 'URL : '.$this->url.' is not reachable at '.date('Y-m-d H:i:s');
	    $headers = 'From: RZ Monitor <'.$fromEmail. ">\r\n" .
	    'X-Mailer: PHP/' . phpversion();

	    mail($to, $subject, $message, $headers);
	}
	/**
	 * Send email when a site is online again.
	 */
	public function notifyUp()
	{
		# Prev status was failed so we send mail
		$fromEmail = $this->conf['sender'];
		if (is_array($this->conf['mail'])) {
			$to = implode(', ', $this->conf['mail']);
		} else {
			$to = $this->conf['mail'];
		}
	    $subject = 'Monitor rezo-zero';
	    $message = 'URL : '.$this->url.' is now online at '.date('Y-m-d H:i:s');
	    $headers = 'From: RZ Monitor <'.$fromEmail. ">\r\n" .
	    'X-Mailer: PHP/' . phpversion();

	    mail($to, $subject, $message, $headers);
	}

	/**
	 * Update data to persist and return whole status array.
	 *
	 * @return array
	 */
	public function getPersistableData()
	{
		if (null === $this->persistedData) {
			$this->persistedData = array(
				'url' => $this->url,
				'totalTime' => 0,
				'crawlCount' => 0,
				'successCount' => 0,
				'failCount' => 0,
			);
		}


		if (isset($this->persistedData['crawlCount'])) {
			$this->persistedData['crawlCount']++;
		} else {
			$this->persistedData['crawlCount'] = 1;
		}

		if (is_float($this->variables['time'])) {
			$this->persistedData['totalTime'] += $this->variables['time'];
		}


		if ($this->variables['status'] == static::STATUS_ONLINE) {
			$this->persistedData['successCount']++;
		} else {
			$this->persistedData['failCount']++;
		}

		if (is_float($this->variables['time'])) {
			$this->persistedData['time'] = (float)$this->variables['time'];
		} else {
			$this->persistedData['time'] = null;
		}

		if (is_float($this->variables['connect_time'])) {
			$this->persistedData['connect_time'] = (float)$this->variables['connect_time'];
		} else {
			$this->persistedData['time'] = null;
		}

		$this->persistedData['status'] = 		$this->variables['status'];
		$this->persistedData['effective_url'] = $this->variables['effective_url'];
		$this->persistedData['cms_version'] = 	$this->variables['cms_version'];
		$this->persistedData['code'] = 			$this->variables['code'];
		$this->persistedData['lastest'] = 		date('Y-m-d H:i:s');

		if ($this->persistedData['successCount'] > 0 &&
			$this->persistedData['totalTime'] > 0)
		{
			$this->persistedData['avg'] = $this->persistedData['totalTime'] / $this->persistedData['successCount'];
		}

		return $this->persistedData;
	}
}
