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
 * @file index.php
 * @author Ambroise Maupate
 */
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use \rezozero\monitor\engine\Collector;
use \rezozero\monitor\engine\PersistedData;
use \rezozero\monitor\kernel\CLIMonitor;
use \rezozero\monitor\kernel\Router;
use \rezozero\monitor\view;

define('BASE_FOLDER', dirname(__FILE__));

require BASE_FOLDER . '/vendor/autoload.php';

/*
 * CONF
 */
$confFile = file_get_contents(BASE_FOLDER . '/conf/conf.json');
$CONF = json_decode($confFile, true);

if (!empty($CONF['timezone'])) {
    date_default_timezone_set($CONF['timezone']);
} else {
    date_default_timezone_set('Europe/Paris');
}

/*
 * Logs
 */
// create a log channel
$log = new Logger('RZMonitor');
$log->pushHandler(new StreamHandler(BASE_FOLDER . '/data/monitor.log', Logger::INFO));

/*
 * Persisted data
 */
$data = new PersistedData(BASE_FOLDER . '/data/persistedData.json');

/*
 * Command line utility with infinite crawl loop
 */
if (php_sapi_name() == 'cli') {
    new CLIMonitor($CONF, $data, $log);
}
/*
 * Need auth for HTTP requests
 */
elseif (Router::authentificate($CONF, $log) === true) {

    $tokens = Router::parseQueryString();

    /*
     * Simple table view for Panic StatusBoard™ iOS app
     *
     * Just call yourdomain.com/table
     */
    if (isset($tokens[0]) && $tokens[0] == 'table') {
        $collector = new Collector('sites.json', $CONF, $data, $log);
        $output = new view\TableOutput();
        $output->parseArray($collector->getStatuses());
        echo $output->output();
    } else {
        /*
         * HTML view for internet browsers
         */
        $collector = new Collector('sites.json', $CONF, $data, $log);
        $output = new view\HTMLOutput();
        $output->parseArray($collector->getStatuses());
        echo $output->output();
    }
}
