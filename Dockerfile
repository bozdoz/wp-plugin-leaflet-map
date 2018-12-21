FROM wordpress:cli

ENV WORDPRESS_PORT 1234

COPY docker-install.sh /usr/local/bin/

USER root

RUN mv /usr/local/bin/wp /usr/local/bin/_wp && \
  echo -e '#!/bin/sh\n_wp --allow-root "$@"' > /usr/local/bin/wp && \
  chmod +x /usr/local/bin/wp

CMD [ "docker-install.sh" ]