web: $(composer config bin-dir)/heroku-php-apache2 -c app/config/apache/apache_app.conf web/
worker: php bin/console rabbitmq:consumer bot_events
