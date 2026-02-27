#!/bin/bash
set -e

# Start PHP-FPM w tle
php-fpm -D

# Start nginx na pierwszym planie
nginx -g 'daemon off;'