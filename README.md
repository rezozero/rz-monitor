# RZ Monitor

RZ Monitor is a command-line tool made to watch website using RZ-CMS. It's a PHP script that download each url and search for CMS version.
It can send a notice email, when a website cannot be accessed.

## Setup

Just copy `sites.default.json` to `sites.json` and `conf.default.json` to `conf.json`.

Write your email and wanted delay before each loop over your sites in **conf.json**. Then setup your URLs in **site.json**.

## Command-line usage

In your terminal, you should use *screen* to detach RZMonitor and let it execute in background.

	screen
    
	cd /yourinstallfolder
	php index.php

## Browser usage

Just go to your install folder from your internet browser: `https://my-domain.com/rz-monitor`.

## Table view

Use Panic’s Status Board™ iOS app with your install folder URL.

## Security

You can autorize only known users in rz-monitor web and table views, you just have to specify your accounts in `conf.json`.


    {
        "mail":"mynotification@email.com",
        "delay":120,
        "users": {
            "firstuser": "password",
            "seconduser": "password"
        }
    }


Then a user and password pair will be asked for next connexion.

If you are using Panic’s Status Board™ just use inline authentification in your URL : `https://user:password@my-domain.com/rz-monitor`.

We strongly recommand using HTTPS protocol to ensure a minimum security during authentification.

## Copyright

**REZO ZERO** sarl        
3 rue de l’abbé Rozier    
69007 Lyon    
FRANCE   