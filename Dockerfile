FROM php:7.2-alpine

MAINTAINER @bozdoz

# install the PHP extensions we need for WP-CLI
RUN set -ex; \
	\
	apk add --no-cache --virtual .build-deps \
		libjpeg-turbo-dev \
		libpng-dev \
	; \
	\
	docker-php-ext-configure gd --with-png-dir=/usr --with-jpeg-dir=/usr; \
	docker-php-ext-install gd mysqli opcache zip; \
	\
	runDeps="$( \
		scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
			| tr ',' '\n' \
			| sort -u \
			| awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
	)"; \
	apk add --virtual .wordpress-phpexts-rundeps $runDeps; \
    apk del .build-deps

RUN set -ex; \
	mkdir -p /var/www/html; \
    chown -R www-data:www-data /var/www/html

WORKDIR /var/www/html
VOLUME /var/www/html

RUN curl -L https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar > /usr/bin/wp && \
    chmod +x /usr/bin/wp

ENV WORDPRESS_PORT 1234

COPY docker-install.sh /usr/local/bin/

RUN chmod 755 /usr/local/bin/docker-install.sh

CMD [ "docker-install.sh" ]