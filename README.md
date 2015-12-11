# Chert

Chert is your own URL minifier in less than 5 minutes. Built with [Silex][1], you only need one command for setup and have you personal bit.ly or goo.gl.

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/9f580b96-d77a-4b78-b94f-254ad13cebaf/mini.png)](https://insight.sensiolabs.com/projects/9f580b96-d77a-4b78-b94f-254ad13cebaf)
 
## Requirements
Chert requires the following basics to run out-of-box.
* PHP 5.3.8+
* [Composer][2]
* The [PDO SQLite][4] driver (or Mysql)

## Installation
Using CLI, type the following : 

    $ composer install pygillier/chert

## Configuration
Installation process will create the file `app/settings.yml`. You need to rename it to `settings.yml` and ensure that SQLite database `chert.sqlite` is writable to have a running Chert instance.

### Alternate database (MySQL, PGSQL, ...)

Chert can use any [PDO compatible][4] DBMS.

As Chert is based on Doctrine DBAL, database configurations options in Chert are the same as the one used in [DBAL configuration][3]. An SQL script is provided for MySQL, it can be easily converted to others RDBMS.


### Web-access
Chert's entry point is `web/index.php`. In order to have shorter URLs and protect your configuration file, you need to redefine vhost document root to  `web/` directory. For more details, see the Silex [webserver configuration page](http://silex.sensiolabs.org/doc/web_servers.html).

### Settings.yml reference
Everything is documented in `settings.yml-dist`.

## Usage
Open your favorite browser and go to Chert URL. Enter an URL in input field then save. You're done!
 
 
[1]: http://silex.sensiolabs.org/
[2]: http://getcomposer.org
[3]: http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html
[4]: http://php.net/pdo
