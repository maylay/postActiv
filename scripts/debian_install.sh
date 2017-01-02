#!/bin/bash

# Copyright (C) 2017 Bob Mottram <bob@freedombone.net>
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU Affero General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU Affero General Public License for more details.
#
# You should have received a copy of the GNU Affero General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.

# This program tries to start the daemons for StatusNet.
# Note that the 'maildaemon' needs to run as a mail filter.

MARIADB_PASSWORD=$1
POSTACTIV_ADMIN_USER=$2
POSTACTIV_ADMIN_PASSWORD=$3
POSTACTIV_DOMAIN_NAME=$4
MY_EMAIL_ADDRESS=$5

if [ ! $1 ]; then
    echo './scripts/debian_install.sh [mariadb password] [username] [password] [domain] [email address]'
    exit 0
fi

# Install a web server
apt-get -yq remove --purge apache2
if [ -d /etc/apache2 ]; then
    rm -rf /etc/apache2
fi
# install nginx
apt-get -yq install nginx php5-fpm

# Create the database
apt-get -yq install python-software-properties debconf-utils
apt-get -yq install software-properties-common
apt-get -yq update
debconf-set-selections <<< "mariadb-server mariadb-server/root_password password $MARIADB_PASSWORD"
debconf-set-selections <<< "mariadb-server mariadb-server/root_password_again password $MARIADB_PASSWORD"
apt-get -yq install mariadb-server
echo "create database postactiv;
CREATE USER '${POSTACTIV_ADMIN_USER}@localhost' IDENTIFIED BY '${POSTACTIV_ADMIN_PASSWORD}';
GRANT ALL PRIVILEGES ON postactiv.* TO '${POSTACTIV_ADMIN_USER}@localhost';
quit" > ~/batch.sql
chmod 600 ~/batch.sql
mysql -u root --password="$MARIADB_PASSWORD" < ~/batch.sql
shred -zu ~/batch.sql

apt-get -yq install php-gettext php5-curl php5-gd php5-mysql git curl
apt-get -yq install php5-memcached php5-intl php-xml-parser

# Clone the PostActiv repo
if [ ! -d /var/www/postactiv ]; then
    git clone https://git.postactiv.com/postActiv/postActiv.git /var/www/postactiv
else
    cd /var/www/postactiv
    git stash
    git pull
fi

# Set permissions
chmod g+w /var/www/postactiv
chmod a+w /var/www/postactiv/avatar
chmod a+w /var/www/postactiv/file
chown -R www-data:www-data /var/www/postactiv
chmod +x /var/www/postactiv/scripts/maildaemon.php
chmod 777 /var/www/postactiv/extlib/HTMLPurifier/HTMLPurifier/DefinitionCache/Serializer.php
if ! grep 'www-data: root' /etc/aliases; then
    echo 'www-data: root' >> /etc/aliases
fi
if ! grep 'maildaemon.php' /etc/aliases; then
    echo '*: /var/www/postactiv/scripts/maildaemon.php' >> /etc/aliases
fi

# Generate the config
postactiv_installer=/var/www/postactiv/scripts/install_cli.php
${postactiv_installer} --server "${POSTACTIV_DOMAIN_NAME}" \
                       --host="localhost" --database="postactiv" \
                       --dbtype=mysql --username="$POSTACTIV_ADMIN_USER" -v \
                       --password="$POSTACTIV_ADMIN_PASSWORD" \
                       --sitename=$"postactiv" --fancy='yes' \
                       --admin-nick="$POSTACTIV_ADMIN_USER" \
                       --admin-pass="$POSTACTIV_ADMIN_PASSWORD" \
                       --site-profile="community" \
                       --ssl="always"

# Diffie-Hellman parameters. From BetterCrypto:
#
#   "Where configurable, we recommend using the Diffie Hellman groups
#    defined for IKE, specifically groups 14-18 (2048–8192bit MODP).
#    These groups have been checked by many eyes and can be assumed
#    to be secure."
echo '-----BEGIN DH PARAMETERS-----
MIIECAKCBAEA///////////JD9qiIWjCNMTGYouA3BzRKQJOCIpnzHQCC76mOxOb
IlFKCHmONATd75UZs806QxswKwpt8l8UN0/hNW1tUcJF5IW1dmJefsb0TELppjft
awv/XLb0Brft7jhr+1qJn6WunyQRfEsf5kkoZlHs5Fs9wgB8uKFjvwWY2kg2HFXT
mmkWP6j9JM9fg2VdI9yjrZYcYvNWIIVSu57VKQdwlpZtZww1Tkq8mATxdGwIyhgh
fDKQXkYuNs474553LBgOhgObJ4Oi7Aeij7XFXfBvTFLJ3ivL9pVYFxg5lUl86pVq
5RXSJhiY+gUQFXKOWoqqxC2tMxcNBFB6M6hVIavfHLpk7PuFBFjb7wqK6nFXXQYM
fbOXD4Wm4eTHq/WujNsJM9cejJTgSiVhnc7j0iYa0u5r8S/6BtmKCGTYdgJzPshq
ZFIfKxgXeyAMu+EXV3phXWx3CYjAutlG4gjiT6B05asxQ9tb/OD9EI5LgtEgqSEI
ARpyPBKnh+bXiHGaEL26WyaZwycYavTiPBqUaDS2FQvaJYPpyirUTOjbu8LbBN6O
+S6O/BQfvsqmKHxZR05rwF2ZspZPoJDDoiM7oYZRW+ftH2EpcM7i16+4G912IXBI
HNAGkSfVsFqpk7TqmI2P3cGG/7fckKbAj030Nck0AoSSNsP6tNJ8cCbB1NyyYCZG
3sl1HnY9uje9+P+UBq2eUw7l2zgvQTABrrBqU+2QJ9gxF5cnsIZaiRjaPtvrz5sU
7UTObLrO1Lsb238UR+bMJUszIFFRK9evQm+49AE3jNK/WYPKAcZLkuzwMuoV0XId
A/SC185udP721V5wL0aYDIK1qEAxkAscnlnnyX++x+jzI6l6fjbMiL4PHUW3/1ha
xUvUB7IrQVSqzI9tfr9I4dgUzF7SD4A34KeXFe7ym+MoBqHVi7fF2nb1UKo9ih+/
8OsZzLGjE9Vc2lbJ7C7yljI4f+jXbjwEaAQ+j2Y/SGDuEr8tWwt0dNbmlPkebb4R
WXSjkm8S/uXkOHd8tqky34zYvsTQc7kxujvIMraNndMAdB+nv4r8R+0ldvaTa6Qk
ZjqrY5xa5PVoNCO0dCvxyXgjjxbL451lLeP9uL78hIrZIiIuBKQDfAcT61eoGiPw
xzRz/GRs6jBrS8vIhi+Dhd36nUt/osCH6HloMwPtW906Bis89bOieKZtKhP4P0T4
Ld8xDuB0q2o2RZfomaAlXcFk8xzFCEaFHfmrSBld7X6hsdUQvX7nTXP682vDHs+i
aDWQRvTrh5+SQAlDi0gcbNeImgAu1e44K8kZDab8Am5HlVjkR1Z36aqeMFDidlaU
38gfVuiAuW5xYMmA3Zjt09///////////wIBAg==
-----END DH PARAMETERS-----
' > /etc/ssl/certs/${POSTACTIV_DOMAIN_NAME}.dhparam

# Get a LetsEncrypt cert
apt-get -yq install certbot -t jessie-backports
systemctl stop nginx
if [ ! -f /etc/letsencrypt/live/${POSTACTIV_DOMAIN_NAME}/fullchain.pem ]; then
    certbot certonly -n --server https://acme-v01.api.letsencrypt.org/directory --standalone -d $POSTACTIV_DOMAIN_NAME --renew-by-default --agree-tos --email $MY_EMAIL_ADDRESS
    ln -s /etc/letsencrypt/live/${POSTACTIV_DOMAIN_NAME}/privkey.pem /etc/ssl/private/${POSTACTIV_DOMAIN_NAME}.key
    ln -s /etc/letsencrypt/live/${POSTACTIV_DOMAIN_NAME}/fullchain.pem /etc/ssl/certs/${POSTACTIV_DOMAIN_NAME}.pem
fi

# LetsEncrypt cert renewals
renewals_script=/etc/cron.monthly/letsencrypt
echo '#!/bin/bash' > $renewals_script
echo 'if [ -d /etc/letsencrypt ]; then' >> $renewals_script
echo '    if [ -f ~/letsencrypt_failed ]; then' >> $renewals_script
echo '        rm ~/letsencrypt_failed' >> $renewals_script
echo '    fi' >> $renewals_script
echo '    for d in /etc/letsencrypt/live/*/ ; do' >> $renewals_script
echo -n '        LETSENCRYPT_DOMAIN=$(echo "$d" | ' >> $renewals_script
echo -n "awk -F '/' '{print " >> $renewals_script
echo -n '$5' >> $renewals_script
echo "}')" >> $renewals_script
echo '        if [ -f /etc/nginx/sites-available/$LETSENCRYPT_DOMAIN ]; then' >> $renewals_script
echo "            certbot certonly -n --server https://acme-v01.api.letsencrypt.org/directory --standalone -d $LETSENCRYPT_DOMAIN --renew-by-default --agree-tos --email $MY_EMAIL_ADDRESS" >> $renewals_script
echo '        fi' >> $renewals_script
echo '    done' >> $renewals_script
echo 'fi' >> $renewals_script
chmod +x $renewals_script

# Create web server config
echo "server {
    listen 80;
    listen [::]:80;
    server_name ${POSTACTIV_DOMAIN_NAME};
    root /var/www/postactiv;
    access_log /var/log/nginx/postactiv.access.log;
    error_log /var/log/nginx/postactiv.err.log warn;
    client_max_body_size 20m;
    client_body_buffer_size 128k;
    limit_conn conn_limit_per_ip 10;
    limit_req zone=req_limit_per_ip burst=10 nodelay;

    rewrite ^ https://$server_name$request_uri? permanent;
}

server {
    listen 443 ssl;
    listen [::]:443 ssl;
    server_name ${POSTACTIV_DOMAIN_NAME};

    ssl_stapling off;
    ssl_stapling_verify off;
    ssl on;
    ssl_certificate /etc/ssl/certs/${POSTACTIV_DOMAIN_NAME}.pem;
    ssl_certificate_key /etc/ssl/private/${POSTACTIV_DOMAIN_NAME}.key;
    ssl_dhparam /etc/ssl/certs/${POSTACTIV_DOMAIN_NAME}.dhparam;

    ssl_session_cache  builtin:1000  shared:SSL:10m;
    ssl_session_timeout 60m;
    ssl_prefer_server_ciphers on;
    ssl_protocols TLSv1 TLSv1.1 TLSv1.2;
    ssl_ciphers 'EDH+CAMELLIA:EDH+aRSA:EECDH+aRSA+AESGCM:EECDH+aRSA+SHA256:EECDH:+CAMELLIA128:+AES128:+SSLv3:!aNULL:!eNULL:!LOW:!3DES:!MD5:!EXP:!PSK:!DSS:!RC4:!SEED:!IDEA:!ECDSA:kEDH:CAMELLIA128-SHA:AES128-SHA';
    add_header Content-Security-Policy \"default-src https:; script-src https: 'unsafe-inline'; style-src https: 'unsafe-inline'\";
    add_header X-Frame-Options DENY;
    add_header X-Content-Type-Options nosniff;

  add_header Strict-Transport-Security max-age=15768000;

  # Logs
  access_log /var/log/nginx/postactiv.access.log;
  error_log /var/log/nginx/postactiv.err.log warn;

  # Root
  root /var/www/postactiv;

  # Index
  index index.php;

  # PHP
  location ~ \.php {
    include snippets/fastcgi-php.conf;
    fastcgi_pass unix:/var/run/php5-fpm.sock;
  }

  # Location
  location / {
    client_max_body_size 15m;
    client_body_buffer_size 128k;

    limit_conn conn_limit_per_ip 10;
    limit_req zone=req_limit_per_ip burst=10 nodelay;

    try_files $uri $uri/ @postactiv;
  }

  # Fancy URLs
  location @postactiv {
    rewrite ^(.*)$ /index.php?p=$1 last;
  }

  # Restrict access that is unnecessary anyway
  location ~ /\.(ht|git) {
    deny all;
  }

}" > /etc/nginx/sites-available/postactiv
ln -s /etc/nginx/sites-available/postactiv /etc/nginx/sites-enabled/

# Start the web server
systemctl restart php5-fpm
systemctl start nginx

exit 0
