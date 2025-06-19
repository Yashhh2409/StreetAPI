FROM php:8.2-apache

# Install mysqli and required dependencies
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Enable Apache rewrite module
RUN a2enmod rewrite

# Copy all files to Apache root
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Optional: Set permissions
RUN chown -R www-data:www-data /var/www/html/

# Expose port 80
EXPOSE 80
