FROM hacklab/wordpress
MAINTAINER Rich Hildred <rhildred@wlu.ca>

RUN rm -rf /var/www/html/
RUN wp core download --path=/var/www/html/ \
    && { \
        echo "file_uploads = On"; \
        echo "upload_max_filesize = 2048M"; \
        echo "post_max_size = 2048M"; \
        echo "max_file_uploads = 20"; \
    } > /usr/local/etc/php/conf.d/wordpress-uploads.ini \
    && chown -R www-data: /var/www/html/ \
    && mkdir -p /var/www/html/wp-config.d
COPY ./root/var/www/html/wp-config.php /var/www/html/
COPY wp-config.php /var/www/html/wp-config.d
COPY ./root/do_chown.sh /docker-entrypoint-extra/
# this is a pattern to follow for plugins and themes

COPY . /var/www/html/wp-content/plugins/out-of-the-box
COPY . /var/www/html/wp-content/themes/dooplay





