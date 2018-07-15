FROM phpv8/libv8:latest

ARG PHP=7.2

ENV DEBIAN_FRONTEND noninteractive
ENV TERM=xterm-256color
ENV LC_ALL=C.UTF-8
ENV NO_INTERACTION=1
ENV REPORT_EXIT_STATUS=1

RUN echo "deb http://ppa.launchpad.net/ondrej/php/ubuntu xenial main" > /etc/apt/sources.list.d/ondrej-php-xenial.list && \
    apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 4F4EA0AAE5267A6C && \
    apt-get update && \
    apt-get install -y valgrind && \
    apt-get install -y php${PHP} php${PHP}-cli php${PHP}-dev php${PHP}-fpm && \
    rm -rf /var/lib/apt/lists/* && \
    echo 'variables_order = "EGPCS"' >> `php --ini | grep "Loaded Configuration File" | awk '{print $4}'` && \
    php -i && \
    php-config || true && \
    mkdir /root/php-v8

WORKDIR /root/php-v8

#COPY . /root/php-v8
#RUN phpize && ./configure && make

