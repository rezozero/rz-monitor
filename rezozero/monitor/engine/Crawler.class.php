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
	
	function __construct( $url )
	{
		$this->url = $url;
		$this->variables = array();

		$this->variables['url'] = $url;
		$this->variables['cms_version'] = "";
		$this->variables['time'] = "";

		if ($this->download() === true) {
			$this->variables['status'] = _('Online');
			$this->parse();
		}
		else {
			$this->notifyError();
			$this->variables['status'] = _('Fail');
		}
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
			if( preg_match("/\<meta name\=\"generator\" content\=\"RZ\-CMS ([^\"]+)\"/", $this->data, $cmsVersion) > 0 ) {
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

		$startTime = microtime(true);
		
		/* Check if cURL is available */
		if ($ch !== FALSE) {
	        // configuration des options
	        curl_setopt($ch, CURLOPT_URL, $this->url);
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
	        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE); 
	        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.116 Safari/537.36 FirePHP/4Chrome"); 
	        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15); 
	        
	        // exécution de la session
	        $this->data = curl_exec($ch);

	        if ($this->data !== null && $this->data != '') {

		        // fermeture des ressources
		        curl_close($ch);

	        	$endTime = microtime(true);
	        	$this->variables['time'] = $endTime - $startTime;
	        	return true;
	        }
	        else {
	        	// fermeture des ressources
		        curl_close($ch);

		        $endTime = microtime(true);
	        	$this->variables['time'] = $endTime - $startTime;

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

		$to      = $CONF['mail'];
	    $subject = 'Monitor rezo-zero';
	    $message = 'URL : '.$this->url.' is not reachable at '.date('Y-m-d H:i:s');
	    $headers = 'From: monitor@rezo-zero.com' . "\r\n" .
	    'Reply-To: contact@rezo-zero.com' . "\r\n" .
	    'X-Mailer: PHP/' . phpversion();

	    mail($to, $subject, $message, $headers);
	}
}

?>