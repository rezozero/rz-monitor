<?php 
namespace rezozero\monitor\view;

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
 * @file HTMLOutput.class.php
 * @copyright REZO ZERO 2013
 * @author Ambroise Maupate
 */


class HTMLOutput
{
	private $header = "";
	private $footer = "";
	private $content = "";
	
	function __construct()
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

	<script type="text/javascript" src="./js/jquery-1.10.1.min.js"></script>
</head>
<style type="text/css">
	body {
		font-family: Arial, Helvetica, sans-serif;
		width: 990px;
		font-size: 12px;
		margin: 30px auto;
	}
	td {
		padding: 5px 15px;
		border-top:1px solid #CCC;
	}
</style>
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

	public function content( $html )
	{
		$this->content .= $html;
		return $this->content;
	}

	public function parseArray( $arr )
	{
		$this->content("<table>");

		foreach ($arr as $ckey => $crawler) {
			$this->content("\n<tr>");

			krsort($crawler);
			foreach ($crawler as $key => $value) {
				$this->content("\n\t<td class='".$key."'>".$value."</td>");
			}
			$this->content("\n</tr>");
		}
		$this->content("</table>");
	}

	public function output()
	{
		return $this->header.$this->content.$this->footer;
	}
}

 ?>