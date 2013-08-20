# Chert URL minifier
 
Chert is an URL minifier built using [Silex][1] micro-framework (so it's written in PHP)
 
## Requirements
To install and run Chert, you'll need the following components:

* PHP 5.3.8+
* [Composer][2]
* A [PDO compatible](http://php.net/pdo) database and associated PHP driver

## Installation
For now, Chert can be installed only via [Composer][2], you need a working composer install beforehand.

### Getting package and dependencies

Download and extract Chert to a web-accessible directory, then run `composer install` in this directory.

### Database

Depending on chosen DBMS, create a database and associated user.

## Setup

### Database initialization

On chosen database, execute matching sql scripts (in `sql/`folder ) to create the necessary table.

As Chert is based on Doctrine DBAL, database configurations options in Chert are the same as the one used in [DBAL configuration][3]. SQL scripts are provided for MySQL and SQlite, schema is simple so converting these files to other DBMS is easy (feel free to queue them if you want to)


### Web-access
Chert's entry point is `web/index.php`. In order to have shorter URLs and protect your configuration file, you may redefine 

### Configure database and other options
All configuration options are defined in `settings.yml`, a `settings.yml-dist` version is available as a kickstart.




 
## Usage
Open your favorite browser and go to Chert URL 
 
 
[1]: http://silex.sensiolabs.org/
[2]: http://getcomposer.org
[3]: http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html