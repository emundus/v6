FROM php:7.4-apache
LABEL maintainer="Wilfried Maillet <wilfried.maillet@emundus.fr>"

# Enable Apache Rewrite Module
RUN a2enmod rewrite

# BUILDING VARS
ARG xdebug
ARG jest

# Install nodejs, npm and yarn
RUN if [ "$jest" = "1" ];then \
	apt update; \
	apt-get install -y --no-install-recommends \
      nodejs \
      npm \
    ; \
    \
    npm install --global yarn; \
	else \
		echo "[BUILD INFO] : Jest is not required"; \
	fi

# Install PHP extensions
RUN set -ex; \
	\
	savedAptMark="$(apt-mark showmanual)"; \
	\
	apt-get update; \
	apt-get install -y --no-install-recommends \
		mariadb-client \
		acl \
		libbz2-dev \
		libjpeg-dev \
		libldap2-dev \
		libmemcached-dev \
		libpng-dev \
		libpq-dev \
		libzip-dev \
		libfreetype6-dev \
    	libxslt-dev \
	; \
	\
	debMultiarch="$(dpkg-architecture --query DEB_BUILD_MULTIARCH)"; \
	docker-php-ext-configure ldap --with-libdir="lib/$debMultiarch"; \
	docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg;  \
	docker-php-ext-install -j "$(nproc)" \
		bz2 \
		gd \
		ldap \
		mysqli \
		pdo_mysql \
		pdo_pgsql \
		pgsql \
		zip \
		intl \
	; \
	\
    # pecl will claim success even if one install fails, so we need to perform each install separately
	pecl install APCu-5.1.21; \
	pecl install memcached-3.1.4; \
	pecl install redis-5.3.4; \
	\
	docker-php-ext-enable \
		apcu \
		memcached \
		redis \
	; \
	\
    # reset apt-mark's "manual" list so that "purge --auto-remove" will remove all build dependencies
	apt-mark auto '.*' > /dev/null; \
	apt-mark manual $savedAptMark; \
	ldd "$(php -r 'echo ini_get("extension_dir");')"/*.so \
		| awk '/=>/ { print $3 }' \
		| sort -u \
		| xargs -r dpkg-query -S \
		| cut -d: -f1 \
		| sort -u \
		| xargs -rt apt-mark manual; \
	\
	# Clean APT cache
	apt-get purge -y;\
	rm -rf /var/lib/apt/lists/*

# install xdebug
RUN if [ "$xdebug" = "1" ];then \
	pecl install xdebug; \
	docker-php-ext-enable xdebug; \
	{ \
		echo "xdebug.start_with_request=yes";  \
		echo "xdebug.client_host=127.0.0.1";  \
		echo "xdebug.mode=debug,profile,trace";  \
		echo "xdebug.mode=coverage";  \
		echo "xdebug.idekey=docker";  \
	} >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
	else \
		echo "[BUILD INFO] : Xdebug is not required"; \
	fi

# set recommended PHP.ini settings for opcache
# see https://secure.php.net/manual/en/opcache.installation.php
RUN { \
echo 'opcache.memory_consumption=128'; \
echo 'opcache.interned_strings_buffer=8'; \
echo 'opcache.max_accelerated_files=4000'; \
echo 'opcache.revalidate_freq=2'; \
echo 'opcache.fast_shutdown=1'; \
echo 'opcache.enable_cli=1'; \
} > /usr/local/etc/php/conf.d/opcache-recommended.ini

# Set PHP.ini settings for script execution and uploads
RUN { \
echo 'expose_php = Off'; \
echo 'file_uploads = On'; \
echo 'upload_max_filesize = 64M'; \
echo 'post_max_size = 64M'; \
echo 'memory_limit = 1024M'; \
echo 'max_execution_time = 600'; \
echo 'max_input_time = 600'; \
} > /usr/local/etc/php/php.ini

# Copy entrypoint script and Apache default vhost 
COPY --chown=www-data:www-data ["php/7.4/apache/000-default.conf","/etc/apache2/sites-available/000-default.conf"]
RUN sed -i 's/\/var\/www\/html\//\/builds\/emundus\/cms\/core\//g' /etc/apache2/sites-available/000-default.conf
COPY --chown=root:root ["php/7.4/apache/ports.conf","/etc/apache2/ports.conf"]
COPY --chown=www-data:www-data ["php/7.4/scripts/entrypoint.sh","/scripts/entrypoint.sh"]

# # Install yarn environment
# RUN if [ "$jest" = "1" ];then \
#     cd /var/www/html/components; \
#     yarn; \
# 	else \
# 		echo "[BUILD INFO] : Jest is not required"; \
# 	fi

# Entrypoint declaration
ENTRYPOINT [ "/scripts/entrypoint.sh" ]

# Volume, Port and workdir
VOLUME [ "/var/www/html" ]
EXPOSE 8080

# Set user
# USER www-data

# Start Web Server Apache
CMD ["apache2-foreground"]
