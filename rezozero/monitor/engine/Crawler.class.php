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
 *
 * @file Crawler.class.php
 * @copyright REZO ZERO 2013
 * @author Ambroise Maupate
 */

class Crawler
{
	private $url;
	private $data;
	private $time;
	private $conf;
	private $variables;

	private $curlHandle;

	const STATUS_ONLINE = 0;
	const STATUS_FAILED = 1;
	const STATUS_DOWN = 2;

	const HTTP_OK = 200;
	const HTTP_REDIRECT = 300;
	const HTTP_PERMANENT_REDIRECT = 301;

	function __construct( $url,  &$CONF )
	{
		$this->url = $url;
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

		if ((int)($this->variables['code']) >= static::HTTP_OK &&
			(int)($this->variables['code']) <= static::HTTP_PERMANENT_REDIRECT) {

			$this->variables['status'] = static::STATUS_ONLINE;
			$this->notifyUp();

			if ($this->data != '')
			{
				$cmsVersion = array();
				if( preg_match("/\<meta name\=\"generator\" content\=\"([^\"]+)\"/", $this->data, $cmsVersion) > 0 ) {
					$this->variables['cms_version'] = $cmsVersion[1];
				}
			}
		}
		else
		{
			$this->variables['status'] = static::STATUS_FAILED;
			$this->notifyError();
		}

		$this->persist();
	}

	public function getVariables()
	{
		return $this->variables;
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
	        curl_setopt($this->curlHandle, CURLOPT_TIMEOUT, 10);
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
	   		$this->variables['code'] = $info['http_code'];
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

	public function notifyError()
	{
		/*
		 * Check if previous crawl with failed too before sendin an email
		 */
		$file = BASE_FOLDER.'/data/persistedData.json';
		if (file_exists($file))
		{
			$persisted = json_decode(file_get_contents($file), true);

			if (isset($persisted[md5($this->url)]) &&
				isset($persisted[md5($this->url)]['status']) &&
				$this->variables['status'] == static::STATUS_FAILED &&
				$persisted[md5($this->url)]['status'] == static::STATUS_FAILED) {

				# Prev status was failed so we send mail
				$to      = $this->conf['mail'];
			    $subject = 'Monitor rezo-zero';
			    $message = 'URL : '.$this->url.' is not reachable at '.date('Y-m-d H:i:s');
			    $headers = 'From: '.$this->conf['mail']. "\r\n" .
			    'X-Mailer: PHP/' . phpversion();

			    mail($to, $subject, $message, $headers);

			    /*
			     * Tag this site as DOWN when notification sent
			     */
			    $this->variables['status'] = static::STATUS_DOWN;
			}
		}
	}

	public function notifyUp()
	{
		/*
		 * Check if previous crawl with failed too before sendin an email
		 */
		$file = BASE_FOLDER.'/data/persistedData.json';
		if (file_exists($file))
		{
			$persisted = json_decode(file_get_contents($file), true);

			if (isset($persisted[md5($this->url)]) &&
				isset($persisted[md5($this->url)]['status']) &&
			    $this->variables['status'] == static::STATUS_ONLINE &&
				$persisted[md5($this->url)]['status'] == static::STATUS_DOWN) {

				# Prev status was down so we send mail when the site is up again
				$to      = $this->conf['mail'];
			    $subject = 'Monitor rezo-zero';
			    $message = 'URL : '.$this->url.' is now online at '.date('Y-m-d H:i:s');
			    $headers = 'From: '.$this->conf['mail']. "\r\n" .
			    'X-Mailer: PHP/' . phpversion();

			    mail($to, $subject, $message, $headers);
			}
		}
	}

	public function persist()
	{
		$file = BASE_FOLDER.'/data/persistedData.json';

		$persisted = array();

		if (file_exists($file)) {
			$persisted = json_decode(file_get_contents($file), true);
		}

		if (isset($persisted[md5($this->url)]))  {
			if (isset($persisted[md5($this->url)]['crawlCount'])) {
				$persisted[md5($this->url)]['crawlCount']++;
			}
			else {
				$persisted[md5($this->url)]['crawlCount'] = 1;
			}

			if (is_float($this->variables['time'])) {
				$persisted[md5($this->url)]['totalTime'] += 	$this->variables['time'];
			}
		}
		else {
			$persisted[md5($this->url)] = $this->variables;
			$persisted[md5($this->url)]['totalTime'] = $this->time;
			$persisted[md5($this->url)]['crawlCount'] = 1;
			$persisted[md5($this->url)]['successCount'] = 0;
			$persisted[md5($this->url)]['failCount'] = 0;
		}

		if ($this->variables['status'] == static::STATUS_ONLINE) {
			$persisted[md5($this->url)]['successCount']++;
		}
		else {
			$persisted[md5($this->url)]['failCount']++;
		}

		if (is_float($this->variables['time'])) {
			$persisted[md5($this->url)]['time'] = (float)$this->variables['time'];
		}
		else {
			$persisted[md5($this->url)]['time'] = null;
		}
		if (is_float($this->variables['connect_time'])) {
			$persisted[md5($this->url)]['connect_time'] = (float)$this->variables['connect_time'];
		}
		else {
			$persisted[md5($this->url)]['time'] = null;
		}
		$persisted[md5($this->url)]['status'] = 		$this->variables['status'];
		$persisted[md5($this->url)]['effective_url'] = 	$this->variables['effective_url'];
		$persisted[md5($this->url)]['cms_version'] = 	$this->variables['cms_version'];
		$persisted[md5($this->url)]['code'] = 			$this->variables['code'];
		$persisted[md5($this->url)]['lastest'] = 		date('Y-m-d H:i:s');

		if ($persisted[md5($this->url)]['successCount'] > 0 &&
			$persisted[md5($this->url)]['totalTime'] > 0)
		{
			$persisted[md5($this->url)]['avg'] = $persisted[md5($this->url)]['totalTime'] /
													$persisted[md5($this->url)]['successCount'];
		}

		$this->variables = $persisted[md5($this->url)];

		file_put_contents($file, json_encode($persisted, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
	}
}
