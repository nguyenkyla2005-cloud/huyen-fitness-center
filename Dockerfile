FROM php:8.1-apache

# Cài mysqli
RUN docker-php-ext-install mysqli

# Copy source code (đúng thư mục login-role)
COPY login-role/ /var/www/html/

# Cấp quyền cho Apache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Cho phép Apache truy cập
RUN echo '<Directory /var/www/html/>\n\
AllowOverride All\n\
Require all granted\n\
</Directory>' >> /etc/apache2/apache2.conf

# Enable rewrite
RUN a2enmod rewrite
