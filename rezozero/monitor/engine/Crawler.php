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
 * @file Crawler.php
 * @author Ambroise Maupate
 */
namespace rezozero\monitor\engine;

use Psr\Log\LoggerInterface;
use \rezozero\monitor\engine\PersistedData;
use \rezozero\monitor\exception\WebsiteDownException;
use \rezozero\monitor\parser\GeneratorParser;
use \rezozero\monitor\validator\EmptyDataValidator;
use \rezozero\monitor\validator\SqlErrorValidator;
use \rezozero\monitor\validator\StatusCodeValidator;
use \rezozero\monitor\validator\UncaughtExceptionValidator;
use \rezozero\monitor\view\Notifier;

/**
 * Crawl a single website.
 */
class Crawler
{
    private $url;
    private $data;
    private $conf;
    private $variables;
    private $persistedData;
    private $notifier;
    private $log;

    private $parsers;
    private $validators;

    private $curlHandle;

    const STATUS_ONLINE = 0;
    const STATUS_FAILED = 1;
    const STATUS_DOWN = 2;

    const HTTP_OK = 200;
    const HTTP_REDIRECT = 300;
    const HTTP_PERMANENT_REDIRECT = 301;

    public function __construct($url, &$CONF, PersistedData &$persistedData, LoggerInterface $log)
    {
        $this->url = $url;
        $this->log = $log;
        $this->persistedData = $persistedData->getSiteData($this->url);
        $this->variables = array();
        $this->conf = $CONF;
        $this->variables['url'] = $url;
        $this->variables['cms_version'] = "";
        $this->variables['time'] = "";

        $this->initRequest();

        $this->notifier = new Notifier($this->conf);
    }

    /**
     * Parse downloaded data to find HTTP Code and Meta generator
     * @return void
     */
    public function parse()
    {
        $this->getInfos();
        $this->parsers = [
            new GeneratorParser(),
        ];
        $this->validators = [
            new StatusCodeValidator($this->variables['code']),
            new EmptyDataValidator($this->data),
            new SqlErrorValidator($this->data),
            new UncaughtExceptionValidator($this->data),
        ];

        try {
            foreach ($this->validators as $validator) {
                $validator->validate();
            }
            foreach ($this->parsers as $parser) {
                $parser->parse($this->data, $this->variables);
            }

            /*
             * Site is up
             */
            $this->variables['status'] = static::STATUS_ONLINE;
            $this->postSuccess();

        } catch (WebsiteDownException $e) {
            $this->variables['status'] = static::STATUS_FAILED;
            $this->log->addWarning($this->url . " is not reachable (" . $e->getMessage() . ").");
            $this->postFailed();
        }
    }

    protected function postSuccess()
    {
        if (null !== $this->persistedData &&
            $this->persistedData['status'] == static::STATUS_DOWN) {
            /*
             * If site was DOWN, we notify it's UP again.
             */
            $this->notifier->notifyUp($this->url);
            $this->log->addInfo($this->url . " is not down anymore. Everything go to normal.");
        }
    }

    protected function postFailed()
    {
        if (null !== $this->persistedData &&
            $this->persistedData['status'] == static::STATUS_FAILED) {
            /*
             * Second time FAILED, site is now DOWN,
             * we send a notification
             */
            $this->variables['status'] = static::STATUS_DOWN;
            $this->notifier->notifyDown($this->url);
            $this->log->addCritical($this->url . " is not reachable for the second time.");

        } elseif (null !== $this->persistedData &&
            $this->persistedData['status'] == static::STATUS_DOWN) {
            /*
             * Site is already DOWN, do nothing
             */
            $this->variables['status'] = static::STATUS_DOWN;
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

    public function initRequest()
    {
        // initialisation de la session
        $this->curlHandle = curl_init();

        /* Check if cURL is available */
        if ($this->curlHandle !== false) {
            // configuration des options
            curl_setopt($this->curlHandle, CURLOPT_URL, $this->url);
            curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($this->curlHandle, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($this->curlHandle, CURLOPT_VERBOSE, false);
            curl_setopt($this->curlHandle, CURLOPT_SSLVERSION, 3);
            curl_setopt($this->curlHandle, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->curlHandle, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($this->curlHandle, CURLOPT_TIMEOUT, 10);
            curl_setopt($this->curlHandle, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.152 Safari/537.36 RZMonitor");

            if (defined("CURLOPT_IPRESOLVE")) {
                curl_setopt($this->curlHandle, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            }

            return true;
        } else {
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
        } else {
            $this->variables['effective_url'] = "";
        }
        return $info;
    }
    /**
     * Pass data to the crawler
     * @param [type] $data [description]
     */
    public function setRequestedData($data)
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
            $this->persistedData['time'] = (float) $this->variables['time'];
        } else {
            $this->persistedData['time'] = null;
        }

        if (is_float($this->variables['connect_time'])) {
            $this->persistedData['connect_time'] = (float) $this->variables['connect_time'];
        } else {
            $this->persistedData['time'] = null;
        }

        $this->persistedData['status'] = $this->variables['status'];
        $this->persistedData['effective_url'] = $this->variables['effective_url'];
        $this->persistedData['cms_version'] = $this->variables['cms_version'];
        $this->persistedData['code'] = $this->variables['code'];
        $this->persistedData['lastest'] = date('Y-m-d H:i:s');

        if ($this->persistedData['successCount'] > 0 &&
            $this->persistedData['totalTime'] > 0) {
            $this->persistedData['avg'] = $this->persistedData['totalTime'] / $this->persistedData['successCount'];
        }

        return $this->persistedData;
    }
}
