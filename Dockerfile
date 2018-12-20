FROM wordpress:cli

ENV WORDPRESS_PORT 1234

COPY docker-install.sh /usr/local/bin/

USER root

CMD [ "docker-install.sh" ]