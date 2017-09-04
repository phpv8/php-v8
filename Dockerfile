ARG TAG=xenial-v8-63-php-72

FROM pinepain/php-v8-docker:${TAG}

COPY . /root/php-v8
COPY ./scripts/provision/.bashrc /root/.bashrc

WORKDIR /root/php-v8

RUN php -i && php-config || true

RUN phpize && ./configure && make
