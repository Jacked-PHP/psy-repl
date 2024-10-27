#!/bin/bash

# This is an example of recommended steps to build your application.

rm -rf vendor node_modules
composer install
npm ci
npm run build
php artisan native:build linux x64
