# RZ Monitor

RZ Monitor is a command-line tool made to watch website using RZ-CMS and others too…
It's based on *cURL* and it downloads each url and searches for CMS version.
It can send a notification email when a website cannot be accessed and when it is reachable again.

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/c72026e1-b0fd-4c22-b514-6a36dc7d2160/mini.png)](https://insight.sensiolabs.com/projects/c72026e1-b0fd-4c22-b514-6a36dc7d2160)

## Dependancies

* PHP 5.4.3 min
* cURL
* Composer

## Setup

* Copy `sites.default.json` to `sites.json` and `conf.default.json` to `conf.json`.
* Write your emails and users/passwords in **conf.json** file.
* Setup your URLs in **site.json**.
* Run `composer install --no-dev` to install dependencies and create autoloader
* Run `composer dumpautoload -o` to get better autoload performances.

## Command-line usage

```shell
cd /yourinstallfolder
php index.php
```

If you want to setup a automatic crawl, you can use `crontab` to execute index.php periodically.

```shell
# Check websites every 10 minutes
*/10 * * * * /usr/bin/php /path/to/index.php
```


## Browser usage

Copy Apache configuration from `apache.conf` file into your virtual host config (good method),
or just use `.htaccess` files to securize and enable url-rewriting (deprecated method).

Then go to your install folder from your internet browser: `https://my-domain.com/rz-monitor`.

## Table view

Use Panic’s Status Board™ iOS app with your install folder URL.

## Security

You can autorize only known users in rz-monitor web and table views,
you just have to specify your accounts in `conf.json`.

```json
{
    "sender":"mynotification@email.com",
    "timezone":"Europe/Paris",
    "mail":[
        "mynotification@email.com",
        "mysecondnotification@email.com"
    ],
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

## Mailer

You can use a different mailer system from your PHP server.
Just add a little configuration to `conf/conf.json` file for
your external SMTP service and RZMonitor will use it instead of *sendmail* command.

For example, here is a configured *Mandrill* SMTP service.

```json
{
    "mailer": {
        "type": "smtp",
        "host": "smtp.mandrillapp.com",
        "port": 587,
        "encryption": false,
        "username": "your-username",
        "password": "your-password"
    }
}
```

## Logs

RZMonitor uses *Monolog* to build a log file in `data/monitor.log`. It will write every connections
and site status changes. Do not forget to use `logrotate` process on this file.

## Author

**REZO ZERO** sarl
1 rue de l’abbé Rozier
69001 Lyon
FRANCE
