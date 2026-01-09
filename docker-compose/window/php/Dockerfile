FROM php:7.4-fpm AS crmeb_php
WORKDIR "/var/www"

# 安装系统依赖
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# 安装 PHP 扩展（包括 OPcache、pcntl、posix）
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        mysqli \
        pdo_mysql \
        gd \
        pcntl \
        posix \
        opcache

# 安装 Redis 扩展
RUN pecl install redis && docker-php-ext-enable redis

# 配置 open_basedir
RUN echo "open_basedir = /tmp:/var:/dev" > /usr/local/etc/php/conf.d/open_basedir.ini

# 配置 PHP-FPM 进程数
COPY docker-compose/window/php/www.conf /usr/local/etc/php-fpm.d/zz-custom.conf

# 创建 OPcache 文件缓存目录
RUN mkdir -p /tmp/opcache && chmod 777 /tmp/opcache

# 关键优化：复制 vendor 目录到镜像内部（解决 Windows Docker 文件系统性能问题）
# 构建时需要设置 context 为项目根目录
COPY crmeb/vendor /var/www/vendor
