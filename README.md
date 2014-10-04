# RZ Monitor

RZ Monitor is a command-line tool made to watch website using RZ-CMS.
It's a PHP script that download each url and search for CMS version.
It can send a notice email, when a website cannot be accessed.

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/c72026e1-b0fd-4c22-b514-6a36dc7d2160/mini.png)](https://insight.sensiolabs.com/projects/c72026e1-b0fd-4c22-b514-6a36dc7d2160)

## Setup

Just copy `sites.default.json` to `sites.json` and `conf.default.json` to `conf.json`.

Write your email and wanted delay before each loop over your sites in **conf.json**.
Then setup your URLs in **site.json**.

## Command-line usage


```
cd /yourinstallfolder
php index.php
```

If you want to setup a automatic crawl, you can use `crontab` to execute index.php periodically.

## Browser usage

Copy Apache configuration from `apache.conf` file into your virtual host config (good method),
or just use `.htaccess` files to securize and enable url-rewriting (deprecated method).

Then go to your install folder from your internet browser: `https://my-domain.com/rz-monitor`.

## Table view

Use Panic’s Status Board™ iOS app with your install folder URL.

## Security

You can autorize only known users in rz-monitor web and table views,
you just have to specify your accounts in `conf.json`.

```
{
    "mail":"mynotification@email.com",
    "users": {
        "firstuser": "password",
        "seconduser": "password"
    }
}
```

Then a user and password pair will be asked for next connexion.

If you are using Panic’s Status Board™ just use inline authentification
in your URL : `https://user:password@my-domain.com/rz-monitor`.

We strongly recommand using HTTPS protocol to ensure a minimum security during authentification.

## Author

**REZO ZERO** sarl
1 rue de l’abbé Rozier
69007 Lyon
FRANCE
