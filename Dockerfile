FROM php:8.2-apache

RUN a2enmod rewrite

RUN apt-get update && apt-get install -y \
  libzip-dev \
  zip \
  curl \
  unzip \
  cron \
  git \
  default-mysql-client \
  libfreetype6-dev \
  libjpeg62-turbo-dev \
  libpng-dev \
  && docker-php-ext-configure gd --with-freetype --with-jpeg\
  && docker-php-ext-install -j$(nproc) gd \
  && docker-php-ext-install pdo_mysql \
  && docker-php-ext-install pdo_mysql \
  && docker-php-ext-install mysqli \
  && docker-php-ext-install opcache \
  && docker-php-ext-install mysqli \
  && docker-php-ext-install zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN echo 'alias drush="/var/www/html/vendor/drush/drush/drush"' >> ~/.bashrc

RUN echo "0 6 * * *  root /var/www/html/vendor/drush/drush/drush feeds:import-all planet --import-disabled >> /var/log/cron.log 2>&1" >> /etc/crontab

RUN touch /var/log/cron.log

ENV APACHE_DOCUMENT_ROOT /var/www/html/web

COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

SHELL ["/bin/bash", "-c", "source ~/.bashrc"]

EXPOSE 80

CMD ["sh", "-c", "cron && apache2-foreground"]
