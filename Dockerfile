FROM composer:2.9

RUN composer global config allow-plugins.dealerdirect/phpcodesniffer-composer-installer true
RUN composer global require --dev wp-coding-standards/wpcs:"^3.0"

ENV PATH="${PATH}:/tmp/vendor/bin"

WORKDIR /app

CMD ["phpcs", "-s", "-q", "-n", "."]