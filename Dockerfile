FROM php:8.1-apache

# Copy đúng thư mục chứa index.php
COPY login-role/ /var/www/html/

# Cấp quyền
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Enable rewrite
RUN a2enmod rewrite