FROM wordpress:cli

ENV WORDPRESS_PORT 1234

COPY docker-install.sh /usr/local/bin/

USER root
 
RUN chmod 755 /usr/local/bin/docker-install.sh

USER xfs

CMD [ "docker-install.sh" ]