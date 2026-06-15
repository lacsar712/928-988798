FROM php:7.4-apache

# Install mysqli extension
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Enable Apache mod_rewrite
# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install ping for NetTool (Use Aliyun mirror for stability)
RUN sed -i 's/deb.debian.org/mirrors.aliyun.com/g' /etc/apt/sources.list \
    && sed -i 's/security.debian.org/mirrors.aliyun.com/g' /etc/apt/sources.list \
    && apt-get update && apt-get install -y iputils-ping && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /var/www/html

# Copy source code (Best practice for delivery)
COPY src/ /var/www/html/

# Set permissions for uploads and data
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && mkdir -p /var/www/html/uploads \
    && mkdir -p /var/www/html/data \
    && chmod -R 777 /var/www/html/uploads \
    && chmod -R 777 /var/www/html/data
