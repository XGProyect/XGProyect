<p align="center"
    <a href="https://www.xgproyect.org/" target="_blank">
        <img align="center" img src="https://xgproyect.org/wp-content/uploads/2019/10/xgp-new-logo-black.png" width="250px" title="XG Proyect" alt="xgp-logo">
    </a>
    <br>
    <strong>X</strong>treme <strong>G</strong>amez <strong>Proyect</strong>o
    <br>
    <strong>Open-source OGame Clon</strong>
</p>

About
====

XG Proyect (XGP) is a web browser game based on the famous OGame. Our goal is to offer a package that is as similar as possible to the original.

Official Website: https://www.xgproyect.org/  
Live Server: https://www.xgproyect.net/  

## Requirements

PHP 8.4 or greater  
MySQL 5.7 or greater  

## How to get XG Proyect?

### Manually
Download and install XG Proyect is easy.

- Go to the releases section and get the latest stable release.
- Then unzip the upload dir in your localhost, rename that folder to wathever you want.
- Point you browser to your localhost and follow the step by step instructions provided by the installation software.

### Composer

```
composer create-project xgproyect/xgproyect
```

### Quick start

The easiest way to run XG Proyect locally is with Docker through Laravel Sail. See this <a href="https://laravel.com/docs/12.x/sail">Laravel Sail guide</a> if you need to install or configure it first.

1. Start the containers:

```bash
./vendor/bin/sail up -d
```

If you already configured the Sail shell alias, you can use:

```bash
sail up -d
```

2. Open the local services:

- Game: `http://localhost`
- Mailpit: `http://localhost:8025`
- Adminer: `http://localhost:8080`

3. Use the default MySQL connection from `.env.example`:

- Host: `mysql`
- Port: `3306`
- Database: `xgproyect`
- Username: `xgp`
- Password: `xgp`

If you already created your own `.env`, those values may be different. Check that file because it overrides `.env.example`.

## Mailpit

Mailpit captures outgoing emails locally so you can inspect them from a simple web UI.

Open `http://localhost:8025` after starting Sail.

Read more about <a href="https://github.com/axllent/mailpit" target="_blank">Mailpit guide</a> to get started.

## Adminer

Adminer provides a lightweight UI for the local MySQL database.

Open `http://localhost:8080` and use these defaults:

- Server: `mysql`
- Database: `xgproyect`
- Username: `xgp`
- Password: `xgp`

If your local `.env` uses different database credentials, use those values instead.

Read more about <a href="https://www.adminer.org/" target="_blank">Adminer</a> to get started.

## Who is using XG Proyect?
We are happy to deliver this software giving others the possibility to have a good OGame Clon.
On the other hand, it's a pleasure to see people using XG Proyect.
<a href="https://github.com/XGProyect/XGProyect/issues" target="_blank">Create a ticket</a> on GitHub so I can put your game logo here!

<img align="center" img src="https://xgproyect.org/wp-content/uploads/2019/10/xgp-new-logo-black.png" width="150px" title="XG Proyect" alt="xgp-logo">

## We support
The following are tools or frameworks that we use to do our coding experience better!

<ul>
    <li>
        <a href="https://laravel.com/" rel="nofollow">
            Laravel
        </a>
    </li>
    <li>
        <a href="https://getcomposer.org/" rel="nofollow">
            Composer
        </a>
    </li>
    <li>
        <a href="https://github.com/axllent/mailpit" rel="nofollow">
            Mailpit
        </a>
    </li>
    <li>
        <a href="https://www.phpdoc.org/" rel="nofollow">
            PHPDocumentor
        </a>
    </li>
</ul>

## License
The XG Proyect is open-sourced software licensed under the GPL-3.0 License.
