<?php
/**
 * Copyright © 2015, Ambroise Maupate
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @file Router.php
 * @author Ambroise Maupate
 */
namespace rezozero\monitor\kernel;

use Psr\Log\LoggerInterface;

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
                $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"];
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

        //Remove script path:
        if (strlen(dirname($_SERVER['SCRIPT_NAME'])) == 1) {
            $path = substr($path, strlen(dirname($_SERVER['SCRIPT_NAME'])));
        } else {
            $path = substr($path, strlen(dirname($_SERVER['SCRIPT_NAME'])) + 1);
        }

        return (explode('/', $path));
    }

    // fonction pour analyser l'en-tête http auth
    public static function httpDigestParse($txt)
    {
        // protection contre les données manquantes
        $needed_parts = array('nonce' => 1, 'nc' => 1, 'cnonce' => 1, 'qop' => 1, 'username' => 1, 'uri' => 1, 'response' => 1);
        $data = array();
        $keys = implode('|', array_keys($needed_parts));

        preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);

        foreach ($matches as $m) {
            $data[$m[1]] = $m[3] ? $m[3] : $m[4];
            unset($needed_parts[$m[1]]);
        }

        return $needed_parts ? false : $data;
    }

    public static function authentificate(&$CONF, LoggerInterface $log)
    {
        $realm = 'RZ Monitor - Restricted area';
        /*
         * If users are set, need auth
         */
        if (isset($CONF['users']) && is_array($CONF['users'])) {

            if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
                header('HTTP/1.1 401 Unauthorized');
                header('WWW-Authenticate: Digest realm="' . $realm .
                    '",qop="auth",nonce="' . uniqid() . '",opaque="' . md5($realm) . '"');

                return false;
            }

            // analyse la variable PHP_AUTH_DIGEST
            if (!($data = static::httpDigestParse($_SERVER['PHP_AUTH_DIGEST'])) ||
                !isset($CONF['users'][$data['username']])) {
                header('HTTP/1.0 401 Unauthorized');
                return false;
            }

            // Génération de réponse valide
            $A1 = md5($data['username'] . ':' . $realm . ':' . $CONF['users'][$data['username']]);
            $A2 = md5($_SERVER['REQUEST_METHOD'] . ':' . $data['uri']);
            $valid_response = md5($A1 . ':' . $data['nonce'] . ':' . $data['nc'] . ':' . $data['cnonce'] . ':' . $data['qop'] . ':' . $A2);

            if ($data['response'] != $valid_response) {
                $log->addWarning($data['username'] . " has tried to connect to the monitor, but failed.");
                header('HTTP/1.0 401 Unauthorized');
                return false;
            }

            $log->addInfo($data['username'] . " has connected to the monitor.");
            return true;
        }

        $log->addInfo("Someone has connected to the monitor.");
        /*
         * Else no auth needed
         */
        return true;
    }
}
