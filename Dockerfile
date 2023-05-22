FROM registry.onschool.edu.vn/srm2:base

MAINTAINER infra@onschool.edu.vn

LABEL Vendor="Infra" email="infra@onschool.edu.vn" Version="1.0.0" Description="Docker file, contain: Open-SSH, apache2, php8.0..."

WORKDIR /var/www/html

COPY . .


RUN composer install
