ARG TAG=xenial-v8-65-php-72

FROM pinepain/php-v8-docker:${TAG}

COPY . /root/php-v8
COPY ./scripts/provision/.bashrc /root/.bashrc

WORKDIR /root/php-v8

ENV NO_INTERACTION=1

RUN php -i && php-config || true
RUN phpize && ./configure && make
