Shinage Server
==============

[![MIT license](https://img.shields.io/badge/License-MIT-blue.png)](https://lbesson.mit-license.org/)
[![Build Status](https://travis-ci.org/michz/shinage-server.svg?branch=master)](https://travis-ci.org/michz/shinage-server)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/michz/shinage-server/badges/quality-score.png)](https://scrutinizer-ci.com/g/michz/shinage-server/)
[![Open Source Love png1](https://badges.frapsoft.com/os/v1/open-source.png?v=103)](https://github.com/ellerbrock/open-source-badges/)

Introduction
------------

This is a piece of software running on a web server providing management
functionality for *Shinage* digital signage solutions.

It's based on [Symfony 4](http://symfony.com/).


Hosted
------
If you don't want to care about stuff like servers and security
we can provide the fully functionally hosted solution for you.
No knowledge about servers or programming needed.
Please contact us!


Prerequisites
-------------
* A machine with a running PHP installation (7.1 or newer)
  with mysql support, libgd and terminal access.
* MySQL-Server (local or remote).  
  (On Ubuntu 18.04 or newer you can get a very basic system configuration by running:  
  `apt-get install mysql-server mysql-client php7.2-cli php7.2-gd php7.2-intl php7.2-json php7.2-mysql php7.2-xml`)
* At least one database on this MySQL-Server.  
  (Run `mysql -uroot -p` and then type `CREATE DATABASE your_database_name;` )
* At least one user with full access to this database.  
  (Run `CREATE USER 'your_user_name'@'localhost' IDENTIFIED BY 'your_password';` and `GRANT ALL PRIVILEGES ON your_db_name . * TO 'your_user_name'@'localhost';`)
* Local or global [Composer](https://getcomposer.org/download/)-Installation
  on this machine.




Installation
------------
* This guide assumes that `composer` is installed globally.
  (If yours is installed somewhere locally, 
   replace `composer` by something like `php /path/to/composer.phar` )
* Please check (and install) the [Prerequisites](#Prerequisites).
* Clone this repository.
* Change to the freshly cloned directory. (Something like `cd shinage-server` )
* Run `composer install`
* Run `php bin/console doctrine:schema:update --force`
* To create a first user run:  
  `php bin/console fos:user:create --super-admin`
* Perhaps you have to adjust the file system permissions. On Linux/Unix/BSD/... do:  
  `mkdir ./data; chmod -R 0777 ./var ./data`  
  (If you know what you do you can avoid giving 777-permissions by only granting 
   read-write permission to the user the web server is running as.)
* If you want to host your own service,
  you *really* should know what to do from here.
  (i.e. installing and configuring a web server)
* If you *do not know* what to do but still want to use *shinage*,
  please think about using a [hosted](#Hosted) solution.


Development
-----------
* Follow the [Installation steps above](#Installation).
* Executing `php bin/console server:start`  will run the built-in
  webserver on loopback device (`127.0.0.1` or `::1`) on port `8000`.
* You can even run the webserver on a specific device/address and port:  
  `php bin/console server:start 192.168.0.1:8080`.  
  For details see Symfony's [How to Use PHP's built-in Web Server](http://symfony.com/doc/current/setup/built_in_web_server.html).



Contributing
------------
Feel free to file issues, fork and/or create pull requests.


License
-------
MIT, see also file `LICENSE`.



