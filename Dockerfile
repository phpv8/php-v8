FROM ubuntu:xenial

ARG V8=6.3
ARG PHP=7.1

ENV DEBIAN_FRONTEND noninteractive
ENV TERM=xterm-256color

RUN echo "deb http://ppa.launchpad.net/ondrej/php/ubuntu xenial main" > /etc/apt/sources.list.d/ondrej-php-xenial.list && \
    apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 4F4EA0AAE5267A6C && \
    echo "deb http://ppa.launchpad.net/pinepain/libv8-${V8}/ubuntu xenial main" > /etc/apt/sources.list.d/pinepain-libv8-xenial.list && \
    apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 40ECBCF960C60AA4 && \
    apt-get update && \
    apt-get install -y libv8-${V8} libv8-${V8}-dev libv8-${V8}-dbg && \
    apt-get install -y valgrind && \
    apt-get install -y php${PHP} php${PHP}-cli php${PHP}-dev php${PHP}-fpm && \
    rm -rf /var/lib/apt/lists/* && \
    php -i && \
    php-config || true && \
    echo 'variables_order = "EGPCS"' >> `php --ini | grep "Loaded Configuration File" | awk '{print $4}'` && \
    mkdir /root/php-v8

COPY . /root/php-v8
COPY ./scripts/provision/.bashrc /root/.bashrc

WORKDIR /root/php-v8

RUN phpize && ./configure && make
