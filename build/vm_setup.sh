#!/usr/bin/env bash
echo 'STARTING installer...'
echo 'INSTALLING php, mysql and nginx...'
sudo apt-get update
sudo apt-get install -y python-software-properties
sudo apt-get update
sudo add-apt-repository -y ppa:ondrej/php5-oldstable
sudo apt-get update
sudo apt-get install -y nginx
sudo service nginx start
sudo apt-get install -y curl php5 php5-fpm php5-cli php5-curl php5-gd php5-mcrypt php5-curl php5-mysql php5-xdebug

echo 'INSTALLING mysql-server...'
sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password password topsecret'
sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password topsecret'
sudo apt-get -y install mysql-server

echo 'CONFIGURING nginx'
sudo rm /etc/nginx/sites-available/manager.dev
sudo touch /etc/nginx/sites-available/manager.dev
sudo cat > /etc/nginx/sites-available/manager.dev <<"EOF"
server {
    listen 80;

    root /vagrant/vendor/magento/core;
    index index.php;
    server_name manager.dev;

    location / {
        try_files $uri $uri/ /index.php?q=$uri&$args;
    }

    location @handler { ## Magento uses a common front handler
        rewrite / /index.php;
    }

    location ~ .php$ {
        expires off;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/var/run/php5-fpm.sock;
#       fastcgi_param  SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param  MAGE_RUN_CODE default;
        fastcgi_param  MAGE_RUN_TYPE store;
        #fastcgi_pass     127.0.0.1:9000;
        fastcgi_index index.php;
        include fastcgi_params;
    }
}
EOF

echo 'ENABLE nginx configuration'
sudo sed -i "s/;listen.owner = .*/listen.owner = www-data/" /etc/php5/fpm/pool.d/www.conf
sudo sed -i "s/;listen.group = .*/listen.group = www-data/" /etc/php5/fpm/pool.d/www.conf
sudo sed -i "s/;listen.mode = .*/listen.mode = 0660/" /etc/php5/fpm/pool.d/www.conf
sudo ln -sfn /etc/nginx/sites-available/manager.dev /etc/nginx/sites-enabled/manager.dev
sudo /etc/init.d/nginx restart
sudo /etc/init.d/php5-fpm restart

echo 'CONFIGURING php-fpm'

sudo sed -i "s/error_reporting = .*/error_reporting = E_ALL/" /etc/php5/fpm/php.ini
sudo sed -i "s/display_errors = .*/display_errors = On/" /etc/php5/fpm/php.ini
sudo sed -i "s/disable_functions = .*/disable_functions = /" /etc/php5/cli/php.ini
sudo sed -i "s/memory_limit = .*/memory_limit = 1024M/" /etc/php5/fpm/php.ini
sudo sed -i "s#date\.timezone.*#date\.timezone = \"Europe\/London\"#" /etc/php5/fpm/php.ini
sudo /etc/init.d/php5-fpm restart

echo 'INSTALLING magento...'
wget --quiet https://raw.github.com/netz98/n98-magerun/master/n98-magerun.phar && sudo chmod +x n98-magerun.phar
php n98-magerun.phar install --noDownload --dbHost="127.0.0.1" --dbUser="root" --dbPass="topsecret" --dbName="magetest_manager_test" --useDefaultConfigParams=yes --installationFolder="/vagrant/vendor/magento/core" --baseUrl="http://manager.dev/"
rm n98-magerun.phar

sed -i "s/files/db/" /vagrant/vendor/magento/core/app/etc/local.xml

echo 'SETTING hostname.'
sudo bash -c "cat >> /etc/hosts <<EOF
127.0.0.1 manager.dev
EOF"
