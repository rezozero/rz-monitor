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
 * @file PersistedData.php
 * @author Ambroise Maupate
 */
namespace rezozero\monitor\engine;

/**
 *
 */
class PersistedData
{
    protected $data;
    protected $url;

    public function __construct($url)
    {
        $this->url = $url;

        if (file_exists($this->url)) {
            $this->data = json_decode(file_get_contents($this->url), true);
        } else {
            $this->data = array();
            @file_put_contents($this->url, json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     *
     * @param  string $name
     *
     * @return array
     */
    public function getSiteData($name)
    {
        if (isset($this->data[md5($name)])) {
            return $this->data[md5($name)];
        } else {
            return null;
        }
    }

    /**
     * @param string $name
     * @param array $data
     */
    public function setSiteData($name, $data)
    {
        $this->data[md5($name)] = $data;

        return $this;
    }

    /**
     * @return $this
     */
    public function writeData()
    {
        @file_put_contents($this->url, json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        return $this;
    }
}
