# PHP 8.2 ve Apache ile bir Docker imajı başlatıyoruz
FROM php:8.2-apache

# Sistemi güncelliyoruz ve gerekli kütüphaneleri kuruyoruz
RUN apt-get update && apt-get install -y \
    libxml2-dev \
    libonig-dev \
    libcurl4-openssl-dev \
    git \
    unzip \
    curl \
    && docker-php-ext-install pdo pdo_mysql mbstring xml curl \
    && docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-install pcntl
RUN apt-get update && apt-get install -y pdftk

# Composer'ı indirip kuruyoruz
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# XDebug'ı yüklüyoruz ve etkinleştiriyoruz
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Apache mod_rewrite modülünü etkinleştiriyoruz
RUN a2enmod rewrite

# Çalışma dizinini belirliyoruz
WORKDIR /var/www/html

# Uygulama kodlarını kopyalıyoruz
COPY ./src /var/www/html

# Composer bağımlılıklarını kuruyoruz
RUN composer install --no-dev --optimize-autoloader

# Apache varsayılan root klasörünü ayarlıyoruz (isteğe bağlı)
# Bu adım `COPY ./src /var/www/html` ile birlikte zaten yapıldı

# Sunucu başlatıldığında çalıştırılacak komut
CMD ["apache2-foreground"]
