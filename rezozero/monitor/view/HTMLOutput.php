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
 * @file HTMLOutput.php
 * @author Ambroise Maupate
 */
namespace rezozero\monitor\view;

class HTMLOutput
{
    private $header = "";
    private $footer = "";
    private $content = "";

    private static $columnWidth = array(
        'url' => 60,
        'time' => 8,
        'avg' => 8,
        // 'totalTime'=>8,
        'crawlCount' => 5,
        'successCount' => 5,
        'code' => 5,
        'failCount' => 5,
        'status' => 6,
        'cms_version' => 18,
    );

    public function __construct()
    {
        $this->header();
        $this->footer();
    }

    public function header()
    {
        ob_start();
        ?>
<!DOCTYPE html>
<html>
<head>
	<title>RZ Monitor</title>
	<link rel="stylesheet" href="./css/style.css" type="text/css"/>
	<link rel="apple-touch-icon" sizes="57x57" href="./img/apple-touch-icon-57x57.png">
	<link rel="apple-touch-icon" sizes="114x114" href="./img/apple-touch-icon-114x114.png">
	<link rel="apple-touch-icon" sizes="72x72" href="./img/apple-touch-icon-72x72.png">
	<link rel="apple-touch-icon" sizes="144x144" href="./img/apple-touch-icon-144x144.png">
	<link rel="apple-touch-icon" sizes="60x60" href="./img/apple-touch-icon-60x60.png">
	<link rel="apple-touch-icon" sizes="120x120" href="./img/apple-touch-icon-120x120.png">
	<link rel="apple-touch-icon" sizes="76x76" href="./img/apple-touch-icon-76x76.png">
	<link rel="apple-touch-icon" sizes="152x152" href="./img/apple-touch-icon-152x152.png">
	<link rel="apple-touch-icon" sizes="180x180" href="./img/apple-touch-icon-180x180.png">
	<link rel="icon" type="image/png" href="./img/favicon-192x192.png" sizes="192x192">
	<link rel="icon" type="image/png" href="./img/favicon-160x160.png" sizes="160x160">
	<link rel="icon" type="image/png" href="./img/favicon-96x96.png" sizes="96x96">
	<link rel="icon" type="image/png" href="./img/favicon-16x16.png" sizes="16x16">
	<link rel="icon" type="image/png" href="./img/favicon-32x32.png" sizes="32x32">
	<meta name="msapplication-TileColor" content="#da532c">
	<meta name="msapplication-TileImage" content="/mstile-144x144.png">
	<script type="text/javascript" src="./js/jquery-1.10.1.min.js"></script>
	<script type="text/javascript" src="./js/less-1.4.1.min.js"></script>
</head>
<body>
	<h1>RZ Monitor</h1>
		<?php
$this->header = ob_get_clean();
        return $this->header;
    }

    public function footer()
    {
        ob_start();
        ?>
</body>
</html>
		<?php

        $this->footer = ob_get_clean();
        return $this->footer;
    }

    public function content($html)
    {
        $this->content .= $html;
        return $this->content;
    }

    public function parseArray($arr)
    {
        $this->content("<table>");

        foreach ($arr as $ckey => $crawler) {
            $this->content("\n<tr>");

            krsort($crawler);
            foreach ($crawler as $key => $value) {
                /*
                 * If not in column width donot display
                 */
                if (!in_array($key, array_keys(static::$columnWidth))) {
                    continue;
                }
                if ($ckey > 0) {

                    $additionalClass = '';

                    switch ($key) {
                        case 'status':
                            if ($value == \rezozero\monitor\engine\Crawler::STATUS_ONLINE) {
                                $value = _('Online');
                                $additionalClass = " online";
                            } else if ($value == \rezozero\monitor\engine\Crawler::STATUS_DOWN) {
                                $value = _('Down');
                                $additionalClass = " down";
                            } else {
                                $value = _('Failed');
                                $additionalClass = " failed";
                            }
                            break;
                        case 'time':
                            $value = sprintf('%.3fs', (float) $value);
                            break;
                        case 'totalTime':
                            $value = sprintf('%.3fs', (float) $value);
                            break;
                        case 'connect_time':
                            $value = sprintf('%.3fs', (float) $value);
                            break;
                        case 'avg':
                            $value = sprintf('%.3fs', (float) $value);
                            break;
                        case 'url':
                            $value = str_replace("http://", "", $value);
                            $value = str_replace("https://", "", $value);
                            $value = str_replace("www.", "", $value);
                            $value = str_replace(".com", "", $value);
                            $value = str_replace(".net", "", $value);
                            $value = str_replace(".org", "", $value);
                            $value = str_replace(".eu", "", $value);
                            $value = str_replace(".fr", "", $value);
                            break;

                        default:
                            # code...
                            break;
                    }

                    $this->content("\n\t<td class='" . $key . $additionalClass . "'>" . $value . "</td>");
                } else {
                    $this->content("\n\t<th class='" . $key . "'>" . $value . "</th>");
                }

            }
            $this->content("\n</tr>");
        }
        $this->content("</table>");
    }

    public function output()
    {
        return $this->header . $this->content . $this->footer;
    }
}
?>
