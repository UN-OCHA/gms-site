# Build the code.
FROM public.ecr.aws/unocha/php-k8s:8.2-stable as builder

ARG  BRANCH_ENVIRONMENT

ENV  NODE_ENV=$BRANCH_ENVIRONMENT

# Copy only the files necessary to build the code (see .dockerignore in root).
COPY . /srv/www

WORKDIR /srv/www

# Clean up previous composer installation and run new one.
RUN rm -rf ./vendor && \
    composer self-update && \
    COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev --no-interaction --prefer-dist

# Clean up previous npm installation and run new one. Those commands correspond
# to running `composer sass` but without hitting the PHP timeout from composer.
# RUN cd html/themes/custom/gms_ocha_common && \
#     rm -rf ./node_modules && \
#     npm install && \
#     ./node_modules/.bin/sass sass:build && \
#     rm -rf ./node_modules

# Copy settings to default site location.
RUN cp -a docker/settings.php docker/services.yml html/sites/default

################################################################################

# Generate the image.
FROM public.ecr.aws/unocha/php-k8s:8.2-stable

ARG VCS_REF
ARG VCS_URL
ARG BUILD_DATE
ARG GITHUB_ACTOR
ARG GITHUB_REPOSITORY
ARG GITHUB_SHA
ARG GITHUB_REF

ENV  NGINX_SERVERNAME=gms.unocha.org \
     PHP_ENVIRONMENT=production \
     PHP_MEMORY_LIMIT=256M \
     PHP_MAX_EXECUTION_TIME=60 \
     PHP_MAX_CHILDREN=16 \
     PHP_MAX_INPUT_VARS=3000 \
     GIT_BLAME=$GITHUB_ACTOR \
     GIT_REPO=$GITHUB_REPOSITORY \
     GIT_SHA=$GITHUB_SHA \
     GIT_REF=$GITHUB_REF

LABEL info.humanitarianresponse.build.date=$BUILD_DATE \
      info.humanitarianresponse.build.vcs-url=$VCS_URL \
      info.humanitarianresponse.build.vcs-ref=$VCS_REF

COPY --from=builder /srv/www/config /srv/www/config/
COPY --from=builder /srv/www/html /srv/www/html/
COPY --from=builder /srv/www/vendor /srv/www/vendor/
COPY --from=builder /srv/www/composer.json /srv/www/composer.json
COPY --from=builder /srv/www/composer.patches.json /srv/www/composer.patches.json
COPY --from=builder /srv/www/composer.lock /srv/www/composer.lock
COPY --from=builder /srv/www/symfony.lock /srv/www/symfony.lock
COPY --from=builder /srv/www/PATCHES /srv/www/PATCHES
COPY --from=builder /srv/www/docker/99-elastic-apm-custom.ini /tmp/99-elastic-apm-custom.ini
COPY --from=builder /srv/www/docker/etc/nginx/ratelimit.conf.template /etc/nginx/ratelimit.conf.template

RUN  curl -L -o /tmp/apm-agent-php_all.apk https://github.com/elastic/apm-agent-php/releases/download/v1.10.0/apm-agent-php_1.10.0_all.apk && \
     apk add --allow-untrusted /tmp/apm-agent-php_all.apk && \
     rm -f /tmp/apm-agent-php_all.apk && \
     mv -f /tmp/99-elastic-apm-custom.ini /etc/php82/conf.d/99-elastic-apm-custom.ini
