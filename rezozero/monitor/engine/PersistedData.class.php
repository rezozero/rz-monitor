<?php
/*
 * Copyright REZO ZERO 2014
 *
 *
 *
 * @file PersistedData.php
 * @copyright REZO ZERO 2014
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
