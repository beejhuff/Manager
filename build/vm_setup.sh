#!/usr/bin/env bash

sudo apt-get update
sudo apt-get install -y python-software-properties
sudo apt-get update
sudo add-apt-repository -y ppa:ondrej/php5-oldstable
sudo apt-get update
sudo apt-get install -y nginx
sudo service nginx start
update-rc.d nginx defaults

sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password password root'
sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password root'

sudo apt-get install -y curl apache2 php5 php5-fpm php5-cli php5-curl php5-gd php5-mcrypt php5-mysql php5-xdebug mysql-server

sudo touch /etc/nginx/sites-available/manager.dev

sudo bash -c "cat >> /etc/nginx/sites-available/manager.dev <<EOF
server {
    listen 80;

    server_name manager.dev
    root /vagrant/vendor/magetest/magento/src;

    access_log /srv/logs/nginx_access.log;
    error_log /srv/logs/nginx_error.log;

    location / {
        index index.html index.php;
        try_files $uri $uri/ @handler;
        expires 30d;
    }

    location ^~ /app/                { deny all; }
    location ^~ /includes/           { deny all; }
    location ^~ /lib/                { deny all; }
    location ^~ /media/downloadable/ { deny all; }
    location ^~ /pkginfo/            { deny all; }
    location ^~ /report/config.xml   { deny all; }
    location ^~ /var/                { deny all; }

    location /var/export/ {
        autoindex on;
    }

    location  /. {
        return 404;
    }

    location @handler {
        rewrite / /index.php;
    }

    location ~ .php/ {
        rewrite ^(.*.php)/ $1 last;
    }

    location ~ .php$ {
        if (!-e $request_filename) {
            rewrite / /index.php last;
        }

        if ($host ~* .([a-z]*)$) {
            set $mage_run_code $1;
        }

        expires        off;
        fastcgi_pass   127.0.0.1:9000;
        fastcgi_param  SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param  MAGE_RUN_CODE $mage_run_code;
        fastcgi_param  MAGE_RUN_TYPE store;
        fastcgi_param  MAGE_IS_DEVELOPER_MODE 1;
        include        fastcgi_params;
        fastcgi_read_timeout 600;
    }

    location /api/rest {
       rewrite ^/api/rest /api.php?type=rest last;
    }

    location /js {
        root /srv/magento/;
        gzip on;
        gzip_proxied expired no-cache no-store private auth;
        gzip_comp_level 9;
    }

    location /skin {
        root /srv/magento/;
        gzip on;
        gzip_proxied expired no-cache no-store private auth;
        gzip_comp_level 9;
    }

    location /media {
        root /srv/magento/;
        gzip on;
        gzip_proxied expired no-cache no-store private auth;
        gzip_comp_level 9;
    }

}
EOF"

sudo ln -s /etc/nginx/sites-available/manager.dev /etc/nginx/sites-enabled/manager.dev



sudo sed -i "s/error_reporting = .*/error_reporting = E_ALL/" /etc/php5/fpm/php.ini
sudo sed -i "s/display_errors = .*/display_errors = On/" /etc/php5/fpm/php.ini
sudo sed -i "s/disable_functions = .*/disable_functions = /" /etc/php5/cli/php.ini
sudo sed -i "s/memory_limit = .*/memory_limit = 1024M/" /etc/php5/fpm/php.ini
sudo sed -i "s#date\.timezone.*#date\.timezone = \"Europe\/London\"#" /etc/php5/fpm/php.ini


mysql -uroot -e 'CREATE DATABASE 'magento';'

rm -f /vagrant/vendor/magetest/magento/src/app/etc/local.xml
php -f /vagrant/vendor/magetest/magento/src/install.php -- --license_agreement_accepted yes --locale en_GB --timezone Europe/London --default_currency GBP --db_host localhost --db_name magento --db_user root --db_pass "" --url http://manager.dev/ --skip_url_validation yes --use_rewrites yes --use_secure no --secure_base_url --use_secure_admin no --admin_firstname admin --admin_lastname admin --admin_email admin@example.com --admin_username admin --admin_password adminadmin123123

sudo bash -c "cat >> /etc/hosts <<EOF
127.0.0.1 manager.dev
EOF"
