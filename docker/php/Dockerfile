FROM php:8.2-fpm

RUN apt-get update -y \
    && apt-get install -y nginx

RUN mkdir /usr/local/nvm

ENV PHP_CPPFLAGS="$PHP_CPPFLAGS -std=c++11"
ENV NVM_DIR /usr/local/nvm
ENV NODE_VERSION 20.9.0

RUN docker-php-ext-install pdo_mysql \
    && docker-php-ext-install opcache \
    && apt-get install libicu-dev -y \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && apt-get remove libicu-dev icu-devtools -y
RUN { \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.interned_strings_buffer=8'; \
        echo 'opcache.max_accelerated_files=4000'; \
        echo 'opcache.revalidate_freq=2'; \
        echo 'opcache.fast_shutdown=1'; \
        echo 'opcache.enable_cli=1'; \
    } > /usr/local/etc/php/conf.d/php-opocache-cfg.ini

RUN curl --silent -o- https://raw.githubusercontent.com/creationix/nvm/v0.39.5/install.sh | bash

RUN . $NVM_DIR/nvm.sh \
    && nvm install $NODE_VERSION \
    && nvm alias default $NODE_VERSION \
    && nvm use default

ENV NODE_PATH $NVM_DIR/v$NODE_VERSION/lib/node_modules
ENV PATH $NVM_DIR/versions/node/v$NODE_VERSION/bin:$PATH

COPY nginx-site.conf /etc/nginx/sites-enabled/default
COPY entrypoint.sh /etc/entrypoint.sh

RUN chmod +x /etc/entrypoint.sh

WORKDIR /var/www/doctor-appointment

EXPOSE 80 443

ENTRYPOINT ["sh", "/etc/entrypoint.sh"]