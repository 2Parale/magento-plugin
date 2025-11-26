FROM markoshust/magento-php:8.2-fpm

# Add Magento Marketplace keys
ARG MAGENTO_PUBLIC_KEY
ARG MAGENTO_PRIVATE_KEY

USER root
RUN sed -i 's|listen = .*|listen = 9000|g' /usr/local/etc/php-fpm.conf && \
    sed -i 's|listen = .*|listen = 9000|g' /usr/local/etc/php-fpm.d/www.conf
USER app:app

RUN echo "PUBLIC=${MAGENTO_PUBLIC_KEY} PRIVATE=${MAGENTO_PRIVATE_KEY}" && \
    composer global config http-basic.repo.magento.com ${MAGENTO_PUBLIC_KEY} ${MAGENTO_PRIVATE_KEY} && \
    composer create-project --repository=https://repo.magento.com/ magento/project-community-edition /var/www/html