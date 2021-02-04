FROM php:7.4-cli-alpine
ARG VERSION=azcopy_linux_amd64_10.8.0
ENV ENV_TERMINUS_USERNAME=
ENV ENV_TERMINUS_TOKEN=
ENV ENV_TERMINUS_SITENAME=
ENV ENV_AZURE_KEY=
ENV ENV_STORAGE_ACCT=
ENV ENV_CONTAINER=
RUN apk --update add --virtual build-dependencies --no-cache wget tar 
RUN apk --update add libc6-compat ca-certificates bash
RUN wget -O azcopyv10.tar https://aka.ms/downloadazcopy-v10-linux && \
    tar -xf azcopyv10.tar && \
    mkdir /app && \
    mv ${VERSION}/azcopy /app/azcopy && \
    rm -rf azcopy* && \
    apk del build-dependencies
RUN wget https://github.com/pantheon-systems/terminus/releases/download/2.5.0/terminus.phar -O /app/terminus
RUN chmod +x /app/terminus
RUN mkdir /pantheon-backup
COPY . /app
RUN chmod +x /app/pantheon-backup.sh
WORKDIR /app
RUN wget https://getcomposer.org/composer.phar -O /app/composer.phar
RUN php /app/composer.phar install
ENTRYPOINT ["/app/pantheon-backup.sh"]