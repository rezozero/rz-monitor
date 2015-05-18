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
 * @file CLIMonitor.php
 * @author Ambroise Maupate
 */
namespace rezozero\monitor\kernel;

use Psr\Log\LoggerInterface;
use \rezozero\monitor\engine\Collector;
use \rezozero\monitor\engine\PersistedData;
use \rezozero\monitor\view;
use \rezozero\monitor\view\CLIOutput;

class CLIMonitor
{
    private $output;
    private $colors;
    private $collector;
    private $data;

    public function __construct(&$CONF, PersistedData &$data, LoggerInterface $log)
    {
        $this->output = new view\CLIOutput();
        $this->colors = new view\Colors();
        $this->data = $data;

        CLIOutput::echoAT(
            0,
            0,
            $this->colors->getColoredString(
                'Please wait for RZ Monitor to crawl your websites',
                'white',
                'black'
            )
        );

        $this->collector = new Collector('sites.json', $CONF, $this->data, $log);

        $this->output->parseArray($this->collector->getStatuses());
        system("clear");
        echo $this->output->output();

        $this->output->flushContent();
    }
}
