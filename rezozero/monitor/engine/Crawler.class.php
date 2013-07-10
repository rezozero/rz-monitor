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

	private $variables;

	const STATUS_ONLINE = 0;
	const STATUS_FAILED = 1;
	const HTTP_OK = 200;
	
	function __construct( $url )
	{
		$this->url = $url;
		$this->variables = array();

		$this->variables['url'] = $url;
		$this->variables['cms_version'] = "";
		$this->variables['time'] = "";

		if ($this->download() === true) {

			if ((int)($this->variables['code']) != static::HTTP_OK) {
				$this->notifyError();
				$this->variables['status'] = static::STATUS_FAILED;
			}
			else {
				$this->variables['status'] = static::STATUS_ONLINE;
				$this->parse();
			}
		}
		else {
			$this->notifyError();
			$this->variables['status'] = static::STATUS_FAILED;
		}

		$this->persist();
	}

	public function parse()
	{
		/*
		 * var myRegex = /name\=\"generator\" content\=\"RZ\-CMS ([^"]+)\"/;
			var result = myRegex.exec(data);

			var end = new Date().getTime();
			var time = end - start;

			<meta name="generator" content="RZ-CMS R1-20130115-master" />
		 */

		if ($this->data != '') 
		{
			$cmsVersion = array();
			if( preg_match("/\<meta name\=\"generator\" content\=\"([^\"]+)\"/", $this->data, $cmsVersion) > 0 ) {
				$this->variables['cms_version'] = $cmsVersion[1];
			}
		}
	}

	public function getVariables()
	{
		return $this->variables;
	}

	public function download()
	{
		/* --------------------
		 * Get files from github
		 * -------------------- */
		if (!function_exists('curl_init')) {
			return false;
		}

		// initialisation de la session
		$ch = curl_init();

		
		
		/* Check if cURL is available */
		if ($ch !== FALSE) {
	        // configuration des options
	        curl_setopt($ch, CURLOPT_URL, $this->url);
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
	        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE); 
	        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.116 Safari/537.36 FirePHP/4Chrome"); 
	        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); 
	        
	        // exécution de la session
	        $this->data = curl_exec($ch);
	        $info = curl_getinfo($ch);

	        if ($this->data !== null && $this->data != '') {


		        // fermeture des ressources
		        curl_close($ch);

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

	public function notifyError()
	{
		global $CONF;

		/*
		 * Check if previous crawl with failed too before sendin an email
		 */
		$file = BASE_FOLDER.'/data/persistedData.json';
		if (file_exists($file)) 
		{
			$persisted = json_decode(file_get_contents($file), true);

			if (isset($persisted[md5($this->url)]) && 
				isset($persisted[md5($this->url)]['status']) && 
				$persisted[md5($this->url)]['status'] == static::STATUS_FAILED) {
				
				# Prev status was failed so we send mail
				$to      = $CONF['mail'];
			    $subject = 'Monitor rezo-zero';
			    $message = 'URL : '.$this->url.' is not reachable at '.date('Y-m-d H:i:s');
			    $headers = 'From: monitor@rezo-zero.com' . "\r\n" .
			    'Reply-To: contact@rezo-zero.com' . "\r\n" .
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
			$persisted[md5($this->url)]['time'] = 			$this->variables['time'];
		}
		else {
			$persisted[md5($this->url)]['time'] = 			null;
		}
		$persisted[md5($this->url)]['status'] = 		$this->variables['status'];
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

?>