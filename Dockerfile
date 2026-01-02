FROM php:8.1-apache

# Copy source code
COPY login-role/ /var/www/html/

# Cấp quyền đầy đủ cho Apache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Cho phép Apache truy cập thư mục
RUN echo '<Directory /var/www/html/>\n\
AllowOverride All\n\
Require all granted\n\
</Directory>' >> /etc/apache2/apache2.conf

# Enable rewrite
RUN a2enmod rewrite