FROM php:8.2-apache

# Enable Apache rewrite module
RUN a2enmod rewrite

# Copy all files to container
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Permissions (optional tweak for Render)
RUN chown -R www-data:www-data /var/www/html/

# Expose port 80
EXPOSE 80
