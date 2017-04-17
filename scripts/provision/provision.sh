#!/bin/bash

echo Provisioning...

# Add Ondřej Surý's PPA with co-installable PHP versions:
sudo add-apt-repository -y ppa:ondrej/php
# Add libv8 PPA:
sudo add-apt-repository ppa:pinepain/libv8-6.0

# Let's update packages list:
sudo apt-get update

# Make sure this system tools installed:
sudo apt-get install -y git htop curl pkgconf


# Build and development requirements
sudo apt-get install -y libv8-6.0 libv8-6.0-dev libv8-6.0-dbg
sudo apt-get install -y dh-make valgrind
sudo apt-get install -y libssl-dev openssl
sudo apt-get install -y php7.0 php7.0-cli php7.0-dev php7.0-fpm
sudo apt-get install -y php7.1 php7.1-cli php7.1-dev php7.1-fpm
sudo apt-get install -y php-pear autoconf automake curl libcurl3-openssl-dev build-essential libxslt1-dev re2c libxml2 libxml2-dev bison libbz2-dev libreadline-dev
sudo apt-get install -y libfreetype6 libfreetype6-dev libpng12-0 libpng12-dev libjpeg-dev libjpeg8-dev libjpeg8  libgd-dev libgd3 libxpm4 libltdl7 libltdl-dev
sudo apt-get install -y libssl-dev openssl gettext libgettextpo-dev libgettextpo0 libicu-dev libmhash-dev libmhash2 libmcrypt-dev libmcrypt4


# Install phpbrew to manage php versions
curl -L -O -s https://github.com/phpbrew/phpbrew/raw/master/phpbrew
chmod +x phpbrew
sudo mv phpbrew /usr/local/bin/phpbrew
phpbrew init

cp ~/php-v8/scripts/provision/.bashrc ~/.bashrc

# Cleanup unused stuff
sudo apt-get autoremove -y

date > /home/vagrant/.vagrant_provisioned_at
