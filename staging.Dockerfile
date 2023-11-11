FROM laravelphp/vapor:php82




RUN apk add --update --no-cache \
    c-client \
    imap-dev \
    krb5-dev \
    openssl-dev


RUN docker-php-ext-configure fileinfo && docker-php-ext-install fileinfo

RUN docker-php-ext-configure mbstring && docker-php-ext-install mbstring

RUN docker-php-ext-configure imap --with-kerberos --with-imap-ssl && \
    docker-php-ext-install imap

    


COPY . /var/task