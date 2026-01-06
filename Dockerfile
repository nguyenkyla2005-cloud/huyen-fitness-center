FROM php:8.1-apache

RUN docker-php-ext-install mysqli

# Copy site chính
COPY login-role/ /var/www/html/

# Copy khu vực admin
COPY admin/ /var/www/html/admin/

# Cấp quyền
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

RUN echo '<Directory /var/www/html/>\n\
AllowOverride All\n\
Require all granted\n\
</Directory>' >> /etc/apache2/apache2.conf

RUN a2enmod rewrite
