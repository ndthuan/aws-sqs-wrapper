#!/usr/bin/env bash

pecl list | grep -i xdebug > /dev/null || yes | pecl install xdebug

echo 'zend_extension=xdebug.so' > /usr/local/etc/php/conf.d/xdebug.ini
