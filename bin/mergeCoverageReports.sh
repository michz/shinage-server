#!/usr/bin/env bash

# Download binary if necessary
if [ ! -f bin/phpcov.phar ]; then
  wget -O bin/phpcov.phar https://phar.phpunit.de/phpcov.phar
fi

# Actual merge
php bin/phpcov.phar merge --html coverage/merged-html --text coverage/merged.txt --php coverage/merged.cov coverage/

# Output text version
cat coverage/merged.txt
