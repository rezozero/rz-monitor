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
 * @file rz-monitor.class.js
 * @copyright REZO ZERO 2013
 * @author Ambroise Maupate
 */

RZMonitor = function ( sites ) {
	var o = this;

	o.sites = sites;

	setInterval(function () {
		o.checkSites();
	}, 1000*60);

	o.checkSites();
}
RZMonitor.prototype.sites = null;

RZMonitor.prototype.checkSites = function() {
	var o = this;

	$('#responses').empty();

	for( var i in o.sites )
	{
		o.checkURL(i);
	}
};

RZMonitor.prototype.checkURL = function( i ) {
	var o = this;

	var body = $('#responses');
	var url = o.sites[i];
	console.log("TEST "+url);

	var start = new Date().getTime();

	$.ajax({
		url: url,
		method: 'get',
		error: function () {
			body.prepend('<tr><td>'+url+'</td><td><strong class="error">Fail</strong></td></tr>');
		},
		success: function(data) {
			var myRegex = /name\=\"generator\" content\=\"RZ\-CMS ([^"]+)\"/;
			var result = myRegex.exec(data);

			var end = new Date().getTime();
			var time = end - start;
			body.prepend('<tr><td>'+url+'</td><td>'+result[1]+'</td><td>'+time+'ms</td></tr>');
		}
	});
};