Shinage Server
==============

[![MIT license](https://img.shields.io/badge/License-MIT-blue.svg)](https://lbesson.mit-license.org/)
[![Build Status](https://github.com/michz/shinage-server/workflows/Test%20and%20Build/badge.svg)](https://github.com/michz/shinage-server/workflows/)
[![Open Source Love](https://badges.frapsoft.com/os/v1/open-source.svg?v=103)](https://github.com/ellerbrock/open-source-badges/)


Introduction
------------

This is a piece of software running on a web server providing management
functionality for *Shinage* digital signage solutions.

It's based on [Symfony](http://symfony.com/).


Hosted
------

If you don't want to care about stuff like servers and security
we can provide the fully functionally hosted solution for you.
No knowledge about servers or programming needed.
Please contact us!


Prerequisites (development)
---------------------------

* A machine with a running, up-to-date PHP installation
  with mysql support, libgd and terminal access.
* MariaDB- or MySQL-Server (local or remote). 
* At least one database on this MySQL-Server.  
  (Run `mysql -uroot -p` and then type `CREATE DATABASE your_database_name;` )
* At least one user with full access to this database.  
  (Run `CREATE USER 'your_user_name'@'localhost' IDENTIFIED BY 'your_password';` and `GRANT ALL PRIVILEGES ON your_db_name . * TO 'your_user_name'@'localhost';`)
* Local or global [Composer](https://getcomposer.org/download/)-Installation
  on this machine.


Installation (manual)
---------------------

* This guide assumes that `composer` is installed globally.
  (If yours is installed somewhere locally, 
   replace `composer` by something like `php /path/to/composer.phar` )
* Please check (and install) the [Prerequisites](#Prerequisites).
* Clone this repository.
* Change to the freshly cloned directory. (Something like `cd shinage-server` )
* Run `composer install --no-dev`
* Create a `.env` file containing configuration and credentials (for example see `.env.dist`).
* Run `php bin/console doctrine:schema:update --force`
* To create a first user run:  
  `php bin/console fos:user:create --super-admin`
* Perhaps you have to adjust the file system permissions. On Linux/Unix/BSD/... do:  
  `mkdir ./data; chmod -R 0777 ./var ./data`  
  (If you know what you do you can avoid giving 777-permissions by only granting 
   read-write permission to the user the web server is running as.)
* Build assets:
  `nvm use && corepack enable && yarn install --frozen-lockfile`
* If you want to host your own service,
  you *really* should know what to do from here.
  (i.e. installing and configuring a web server)
* If you *do not know* what to do but still want to use *shinage*,
  please think about using a [hosted](#Hosted) solution.


Installation (container)
------------------------

You can use containers (for example docker) to run Shinage.

Prebuilt images are available at 
https://github.com/michz/shinage-server/pkgs/container/shinage-server-web and
https://github.com/michz/shinage-server/pkgs/container/shinage-server-app .

See [./etc/prod/compose.example.yml](./etc/prod/compose.example.yml)
for an example `docker compose` file to run Shinage.

Please note:

* The database given in `DATABASE_URL` must exist and the given user 
  must be allowed to read and write into it.
* When you first run Shinage, there won't be any user in the database.
  Run `bin/console users:create-admin your-email-address@example.com`
  to create a first user with `ROLE_SUPER_ADMIN` permissions.
* Remember to use a centralized session storage (for example Redis, memcached, ...)
* The `/app/data` directory has to be in sync between all nodes.
  If running a single app container, use a volume.
  If running multiple app containers, use a network storage (nfs),
  another shared storage mechanism or at least sync the files in realtime.
* The `/app/data/pool` directory must be readable and writable by user `www-data` (uid 33).
  To initially create the necessary directory, do: `mkdir /app/data/pool ; chown www-data:www-data /app/data/pool` .
  With the example docker compose file, a sample call could look like:
  `docker compose exec --user=root app bash -c 'mkdir /app/data/pool ; chown www-data:www-data /app/data/pool'`
* Configuration is mainly done using environment variables:
  * `DATABASE_URL`: Database connection URL including username, password, hostname, port, database name and server version
  * `MAILER_DSN`: Mailer URL to use for sending system emails (most likely a SMTP server address; see [Symfony Mailer Docs](https://symfony.com/doc/current/mailer.html) for details)
  * `MAILER_FROM`: Sender email address
  * `APP_SECRET`: a randomly generated secret string
  * `TRUSTED_PROXIES`: List of IP addresses to trust as reverse proxy servers



Development
-----------

* Follow the [Installation steps above](#Installation),
  but do a `composer install` instead of `composer install --no-dev`.
* There are two ways of running the application for development:
  * Executing `php bin/console server:start`  will run the built-in
    webserver on loopback device (`127.0.0.1` or `::1`) on port `8000`.
    You can even run the webserver on a specific device/address and port:  
    `php bin/console server:start 192.168.0.1:8080`.  
    
    For details see Symfony's [How to Use PHP's built-in Web Server](http://symfony.com/doc/current/setup/built_in_web_server.html).

  * For better testing (including sending mails) you can use a ready-to-go docker based development environment:
    `bin/devEnv.sh start`.
    For this you need a working `docker` and `docker-compose` installation.
    The relevant ports are mapped to host ports and printed to console during startup.
    For example, you can open the web interface via `http://localhost:8001/` or `https://localhost:44301`.
    
    In the docker development environment there is a working [Mailpit](https://github.com/axllent/mailpit) installation
    that catches all mails sent via PHP `mail()` function in the php container.
    
  * If you use the docker based environment
    it is important to understand that must commands have to be executed *inside* the php container.
    There is a tiny helper script that executes the necessary `docker exec` command at `bin/runInDev.sh`.
    For example to call the Symfony console you have to run `bin/runInDev.sh bin/console`.

To initialize the development database, do:

```bash
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:schema:update --force
php bin/console doctrine:fixtures:load --no-interaction
```

Or for docker based setup respectively:

```bash
bin/runInDev.sh php bin/console doctrine:database:create --if-not-exists
bin/runInDev.sh php bin/console doctrine:schema:update --complete --force
bin/runInDev.sh php bin/console doctrine:fixtures:load --no-interaction
```

Testing
-------

To run phpspec, do:

```bash
./vendor/bin/phpspec run --format=dot --no-code-generation
```

To run behat, first initialize the testing database:

```bash
php bin/console doctrine:database:create --env=test --if-not-exists
php bin/console doctrine:schema:update --force --env=test --no-interaction
php bin/console doctrine:fixtures:load --env=test --no-interaction
```

Then start the development webserver:

```bash
APP_ENV=test symfony local:server:start --no-tls --port=8000
```

And in a separate shell execute behat:

```bash
APP_ENV=test vendor/bin/behat --format=progress --strict -n --tags="~@todo"
```


Contributing
------------

Feel free to file issues, fork and create pull requests.


License
-------

MIT, see also file `LICENSE`.
