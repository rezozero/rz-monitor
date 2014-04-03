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
 * @file Router.class.php
 * @copyright REZO ZERO 2013
 * @author Ambroise Maupate
 */


abstract class Router
{
	private static $baseURL = null;

	/**
	 * Resolve current front controller URL
	 * 
	 * This method is the base of every URL building methods in RZ-CMS. 
	 * Be careful with handling it.
	 * 
	 * @return string 
	 */
	public static function getResolvedBaseUrl()
	{
		if (static::$baseURL === null) {

			$url = pathinfo($_SERVER['PHP_SELF']);

			// Protocol
			$pageURL = 'http';
			if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
			$pageURL .= "://";
			// Port
			if (isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] != "80") {
				$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
			} else {
				$pageURL .= $_SERVER["SERVER_NAME"];
			}
			// Non root folder
			if (!empty($url["dirname"]) && $url["dirname"] != '/') {
				$pageURL .= $url["dirname"];
			}
			// Trailing slash
			$pageURL .= '/';

			static::$baseURL = $pageURL;
		}

		return static::$baseURL;
	}
	
	/**
	 * Parse query string and current url to get each url tokens in a single array
	 * 
	 * @return array URL tokens
	 */
	public static function parseQueryString()
	{
		//Remove request parameters:
		list($path) = explode('?', $_SERVER['REQUEST_URI']);

		//print_r($path);
		//echo(strlen(dirname($_SERVER['SCRIPT_NAME']))+1);
		//echo(dirname($_SERVER['SCRIPT_NAME']));

		//Remove script path:
		if (strlen(dirname($_SERVER['SCRIPT_NAME'])) == 1) {
			$path = substr($path, strlen(dirname($_SERVER['SCRIPT_NAME'])));
		}
		else {
			$path = substr($path, strlen(dirname($_SERVER['SCRIPT_NAME']))+1);
		}

		return (explode('/', $path));
	}

	// fonction pour analyser l'en-tête http auth
	public static function http_digest_parse($txt)
	{
	    // protection contre les données manquantes
	    $needed_parts = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
	    $data = array();
	    $keys = implode('|', array_keys($needed_parts));
	 
	    preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);

	    foreach ($matches as $m) {
	        $data[$m[1]] = $m[3] ? $m[3] : $m[4];
	        unset($needed_parts[$m[1]]);
	    }

	    return $needed_parts ? false : $data;
	}

	public static function authentificate( &$CONF )
	{
		$realm = _('RZ Monitor - Restricted area');

		/*
		 * If users are set, need auth
		 */
		if (isset($CONF['users']) && is_array($CONF['users'])) {
			
			if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
			    header('HTTP/1.1 401 Unauthorized');
			    header('WWW-Authenticate: Digest realm="'.$realm.
			           '",qop="auth",nonce="'.uniqid().'",opaque="'.md5($realm).'"');

			    exit();
			    return false;
			}

			// analyse la variable PHP_AUTH_DIGEST
			if (!($data = static::http_digest_parse($_SERVER['PHP_AUTH_DIGEST'])) ||
			    !isset($CONF['users'][$data['username']])) 
			{
				/*
				 * ============================================================================
				 * Mail connexion attempt
				 * ============================================================================
				 */
				# Prev status was failed so we send mail
				$to      = $CONF['mail'];
			    $subject = 'Monitor rezo-zero - Connexion attempt';
			    $message = 'User : '.$data['username'].' does not exist (try at '.date('Y-m-d H:i:s').')'."\n\n";
			    foreach ($data as $key => $value) {
			    	 $message .= sprintf("[%s] => %s \n", $key, $value);
			    }

			    $headers = 'From: '.$CONF['mail']. "\r\n" .
			    'X-Mailer: PHP/' . phpversion();

			    mail($to, $subject, $message, $headers);

				return false;
			}

			// Génération de réponse valide
			$A1 = md5($data['username'] . ':' . $realm . ':' . $CONF['users'][$data['username']]);
			$A2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
			$valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);

			if ($data['response'] != $valid_response) {

				/*
				 * ============================================================================
				 * Mail connexion attempt
				 * ============================================================================
				 */
				# Prev status was failed so we send mail
				$to      = $CONF['mail'];
			    $subject = 'Monitor rezo-zero - Connexion attempt';
			    $message = 'User : '.$data['username'].' does not exist (try at '.date('Y-m-d H:i:s').')'."\n\n";
			    foreach ($data as $key => $value) {
			    	 $message .= sprintf("[%s] => %s \n", $key, $value);
			    }

			    $message .= sprintf("Valid response must be %s \n", $valid_response);

			    $headers = 'From: '.$CONF['mail']. "\r\n" .
			    'X-Mailer: PHP/' . phpversion();

			    mail($to, $subject, $message, $headers);

				return false;
			}

			// ok, utilisateur & mot de passe valide
			//echo 'Vous êtes identifié en tant que : ' . $data['username'];

			return true;
		}
		/*
		 * Else no auth needed
		 */
		return true;
	}
}

 ?>