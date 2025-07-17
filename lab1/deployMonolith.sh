#!/bin/bash
# update everything
apt-get update -y
apt-get install -y

# Install packages
apt-get install mysql-server -y
apt-get install php-mysql -y
apt-get install php -y
apt install apache2 -y

