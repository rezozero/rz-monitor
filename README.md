# RZ Monitor

RZ Monitor is a command-line tool made to watch website using RZ-CMS. It's a PHP script that download each url and search for CMS version.
It can send a notice email, when a website cannot be accessed.

## Setup

Just copy **sites.default.json** to **sites.json** and **conf.default.json** to **conf.json**.

Write your email and wanted delay before each loop over your sites in **conf.json**. Then setup your URLs in **site.json**.

## Command-line usage

In your terminal, you should use *screen* to detach RZMonitor and let it execute in background.

<pre>
screen

cd /yourinstallfolder
php index.php
</pre>


## Browser usage

Just go to your install folder with your internet browser.

## Copyright

**REZO ZERO** sarl    
*Ambroise Maupate*    
3 rue de l’abbé Rozier    
69007 Lyon    
FRANCE   