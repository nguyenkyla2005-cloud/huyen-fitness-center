FROM php:8.1-apache

# Copy source code vào Apache
COPY . /var/www/html/

# Cấp quyền
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Enable Apache rewrite (nếu cần)
RUN a2enmod rewrite
