# Chert URL minifier
 
Chert is an URL minifier built using [Silex][1] micro-framework (so it's written in PHP)
 
## Requirements
To install and run Chert, you'll need the following components:

* PHP 5.3.8+
* [Composer][2]
* A [PDO compatible][4] database and associated PHP driver

## Installation
For now, Chert can be installed only via [Composer][2], you need a working composer install beforehand.

### Getting package and dependencies

Download and extract Chert to a web-accessible directory, then run `composer install` in this directory.

## Setup

An already configured settings file is present under the name `settings.yml-dist`. You need to rename it to `settings.yml` and ensure that SQLite database `chert.sqlite` is writable to have a running Chert instance.

### Alternate database (MySQL, PGSQL, ...)

Chert can use any [PDO compatible][4] DBMS.

As Chert is based on Doctrine DBAL, database configurations options in Chert are the same as the one used in [DBAL configuration][3] (example for MySQL is present in `settings.yml-dist`. An SQL script is provided for MySQL, it can be easily converted to others RDBMS.


### Web-access
Chert's entry point is `web/index.php`. In order to have shorter URLs and protect your configuration file, you may redefine vhost document root to  `web/` directory. An apache htaccess file is provided.

### Settings.yml reference
Everything is documented in `settings.yml-dist`. A better explanation will come.

## Usage
Open your favorite browser and go to Chert URL. Enter an URL in input field then save. You're done!
 
 
[1]: http://silex.sensiolabs.org/
[2]: http://getcomposer.org
[3]: http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html
[4]: http://php.net/pdo
