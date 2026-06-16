FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    libzip-dev \
    oniguruma-dev \
    mysql-client \
    git \
    curl \
    bash \
    supervisor

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath gd opcache

RUN pecl install redis && docker-php-ext-enable redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/backend

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

COPY . .
RUN composer run-script post-autoload-dump

# PHP-FPM pool: ondemand — kills idle workers to conserve RAM on 1GB servers
RUN sed -i 's/pm = dynamic/pm = ondemand/' /usr/local/etc/php-fpm.d/www.conf \
 && sed -i 's/pm.max_children = .*/pm.max_children = 5/' /usr/local/etc/php-fpm.d/www.conf \
 && echo 'pm.process_idle_timeout = 10s' >> /usr/local/etc/php-fpm.d/www.conf \
 && echo 'pm.max_requests = 200' >> /usr/local/etc/php-fpm.d/www.conf

# Supervisor config — runs php-fpm + queue-worker + scheduler in one container
RUN printf '[supervisord]\n\
nodaemon=true\n\
logfile=/dev/null\n\
logfile_maxbytes=0\n\
pidfile=/tmp/supervisord.pid\n\
\n\
[unix_http_server]\n\
file=/tmp/supervisor.sock\n\
\n\
[supervisorctl]\n\
serverurl=unix:///tmp/supervisor.sock\n\
\n\
[rpcinterface:supervisor]\n\
supervisor.rpcinterface_factory = supervisor.rpcinterface:make_main_rpcinterface\n\
\n\
[program:php-fpm]\n\
command=php-fpm --nodaemonize\n\
autostart=true\n\
autorestart=true\n\
stdout_logfile=/dev/stdout\n\
stdout_logfile_maxbytes=0\n\
stderr_logfile=/dev/stderr\n\
stderr_logfile_maxbytes=0\n\
priority=1\n\
\n\
[program:queue-worker]\n\
command=php /var/www/backend/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 --no-interaction\n\
autostart=true\n\
autorestart=true\n\
stdout_logfile=/dev/stdout\n\
stdout_logfile_maxbytes=0\n\
stderr_logfile=/dev/stderr\n\
stderr_logfile_maxbytes=0\n\
priority=2\n\
startsecs=5\n\
\n\
[program:scheduler]\n\
command=bash -c "while true; do php /var/www/backend/artisan schedule:run --no-interaction; sleep 60; done"\n\
autostart=true\n\
autorestart=true\n\
stdout_logfile=/dev/stdout\n\
stdout_logfile_maxbytes=0\n\
stderr_logfile=/dev/stderr\n\
stderr_logfile_maxbytes=0\n\
priority=3\n\
startsecs=5\n\
' > /etc/supervisord.conf

EXPOSE 9000

# Single process group: php-fpm + queue-worker + scheduler
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
