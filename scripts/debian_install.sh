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

DEBIAN_REPO="ftp.us.debian.org"
DEBIAN_VERSION=$6

POSTACTIV_REPO="https://git.postactiv.com/postActiv/postActiv.git"
POSTACTIV_COMMIT='1477a300de87faebfcb7fd7163c3a55c75728e2d'

QVITTER_THEME_REPO="https://git.gnu.io/h2p/Qvitter.git"
QVITTER_THEME_COMMIT='a7f82628402db3a7579bb9b2877da3c5737da77b'

SSH_PORT=22
ALLOW_PING=yes

function create_firewall {
	apt-get -yq install iptables
	
	iptables -P INPUT ACCEPT
	ip6tables -P INPUT ACCEPT
	iptables -F
	ip6tables -F
	iptables -t nat -F
	ip6tables -t nat -F
	iptables -X
	ip6tables -X
	iptables -P INPUT DROP
	ip6tables -P INPUT DROP
	iptables -P FORWARD DROP
	ip6tables -P FORWARD DROP
	iptables -A INPUT -i lo -j ACCEPT
	iptables -A INPUT -m conntrack --ctstate ESTABLISHED -j ACCEPT

	# Drop invalid packets
	iptables -t mangle -A PREROUTING -m conntrack --ctstate INVALID -j DROP

	# Make sure incoming tcp connections are SYN packets
	iptables -A INPUT -p tcp ! --syn -m state --state NEW -j DROP
	iptables -t mangle -A PREROUTING -p tcp ! --syn -m conntrack --ctstate NEW -j DROP

	# Drop SYN packets with suspicious MSS value
	iptables -t mangle -A PREROUTING -p tcp -m conntrack --ctstate NEW -m tcpmss ! --mss 536:65535 -j DROP

	# Drop packets with incoming fragments
	iptables -A INPUT -f -j DROP

	# Drop bogons
	iptables -A INPUT -p tcp --tcp-flags ALL ALL -j DROP
	iptables -A INPUT -p tcp --tcp-flags ALL FIN,PSH,URG -j DROP
	iptables -A INPUT -p tcp --tcp-flags ALL SYN,RST,ACK,FIN,URG -j DROP
	iptables -t mangle -A PREROUTING -p tcp --tcp-flags FIN,SYN,RST,PSH,ACK,URG NONE -j DROP
	iptables -t mangle -A PREROUTING -p tcp --tcp-flags FIN,SYN FIN,SYN -j DROP
	iptables -t mangle -A PREROUTING -p tcp --tcp-flags SYN,RST SYN,RST -j DROP
	iptables -t mangle -A PREROUTING -p tcp --tcp-flags SYN,FIN SYN,FIN -j DROP
	iptables -t mangle -A PREROUTING -p tcp --tcp-flags FIN,RST FIN,RST -j DROP
	iptables -t mangle -A PREROUTING -p tcp --tcp-flags FIN,ACK FIN -j DROP
	iptables -t mangle -A PREROUTING -p tcp --tcp-flags ACK,URG URG -j DROP
	iptables -t mangle -A PREROUTING -p tcp --tcp-flags ACK,FIN FIN -j DROP
	iptables -t mangle -A PREROUTING -p tcp --tcp-flags ACK,PSH PSH -j DROP
	iptables -t mangle -A PREROUTING -p tcp --tcp-flags ALL ALL -j DROP
	iptables -t mangle -A PREROUTING -p tcp --tcp-flags ALL NONE -j DROP
	iptables -t mangle -A PREROUTING -p tcp --tcp-flags ALL FIN,PSH,URG -j DROP
	iptables -t mangle -A PREROUTING -p tcp --tcp-flags ALL SYN,FIN,PSH,URG -j DROP
	iptables -t mangle -A PREROUTING -p tcp --tcp-flags ALL SYN,RST,ACK,FIN,URG -j DROP

	# Incoming malformed NULL packets:
	iptables -A INPUT -p tcp --tcp-flags ALL NONE -j DROP

    # telnet isn't enabled as an input and we can also
	# drop any outgoing telnet, just in case
	iptables -A OUTPUT -p tcp --dport telnet -j REJECT
	iptables -A OUTPUT -p udp --dport telnet -j REJECT

    # drop spoofed packets
    iptables -t mangle -A PREROUTING -s 224.0.0.0/3 -j DROP
	iptables -t mangle -A PREROUTING -s 169.254.0.0/16 -j DROP
	iptables -t mangle -A PREROUTING -s 172.16.0.0/12 -j DROP
	iptables -t mangle -A PREROUTING -s 192.0.2.0/24 -j DROP
	iptables -t mangle -A PREROUTING -s 10.0.0.0/8 -j DROP
	iptables -t mangle -A PREROUTING -s 240.0.0.0/5 -j DROP
	iptables -t mangle -A PREROUTING -s 127.0.0.0/8 ! -i lo -j DROP	
	
    # Limit connections per source IP
	iptables -A INPUT -p tcp -m connlimit --connlimit-above 111 -j REJECT --reject-with tcp-reset

	# Limit RST packets
	iptables -A INPUT -p tcp --tcp-flags RST RST -m limit --limit 2/s --limit-burst 2 -j ACCEPT
	iptables -A INPUT -p tcp --tcp-flags RST RST -j DROP

	# Limit new TCP connections per second per source IP
	iptables -A INPUT -p tcp -m conntrack --ctstate NEW -m limit --limit 60/s --limit-burst 20 -j ACCEPT
	iptables -A INPUT -p tcp -m conntrack --ctstate NEW -j DROP

	# SSH brute-force protection
	iptables -A INPUT -p tcp --dport ssh -m conntrack --ctstate NEW -m recent --set
	iptables -A INPUT -p tcp --dport ssh -m conntrack --ctstate NEW -m recent --update --seconds 60 --hitcount 10 -j DROP	

    # These shouldn't be used anyway, but just in case
	iptables -A INPUT -s 6.0.0.0/8 -j DROP
	iptables -A OUTPUT -s 6.0.0.0/8 -j DROP
	iptables -A INPUT -s 7.0.0.0/8 -j DROP
	iptables -A OUTPUT -s 7.0.0.0/8 -j DROP
	iptables -A INPUT -s 11.0.0.0/8 -j DROP
	iptables -A OUTPUT -s 11.0.0.0/8 -j DROP
	iptables -A INPUT -s 21.0.0.0/8 -j DROP
	iptables -A OUTPUT -s 21.0.0.0/8 -j DROP
	iptables -A INPUT -s 22.0.0.0/8 -j DROP
	iptables -A OUTPUT -s 22.0.0.0/8 -j DROP
	iptables -A INPUT -s 26.0.0.0/8 -j DROP
	iptables -A OUTPUT -s 26.0.0.0/8 -j DROP
	iptables -A INPUT -s 28.0.0.0/8 -j DROP
	iptables -A OUTPUT -s 28.0.0.0/8 -j DROP
	iptables -A INPUT -s 29.0.0.0/8 -j DROP
	iptables -A OUTPUT -s 29.0.0.0/8 -j DROP
	iptables -A INPUT -s 30.0.0.0/8 -j DROP
	iptables -A OUTPUT -s 30.0.0.0/8 -j DROP
	iptables -A INPUT -s 33.0.0.0/8 -j DROP
	iptables -A OUTPUT -s 33.0.0.0/8 -j DROP
	iptables -A INPUT -s 55.0.0.0/8 -j DROP
	iptables -A OUTPUT -s 55.0.0.0/8 -j DROP
	iptables -A INPUT -s 214.0.0.0/8 -j DROP
	iptables -A OUTPUT -s 214.0.0.0/8 -j DROP
	iptables -A INPUT -s 215.0.0.0/8 -j DROP
	iptables -A OUTPUT -s 215.0.0.0/8 -j DROP	

	if [[ $ALLOW_PING != 'yes' ]]; then
		iptables -A INPUT -p icmp --icmp-type echo-request -j DROP
		iptables -A OUTPUT -p icmp --icmp-type echo-reply -j DROP
	fi
	
	# DNS
    iptables -A INPUT -p udp -m udp --dport 1024:65535 --sport 53 -j ACCEPT
	
	# ssh
    iptables -A INPUT -p tcp --dport $SSH_PORT -j ACCEPT
	
	# http/s
    iptables -A INPUT -p tcp --dport 80 -j ACCEPT
    iptables -A INPUT -p tcp --dport 443 -j ACCEPT

	# If you are also going to install an xmpp server uncomment these lines
    #iptables -A INPUT -p tcp --dport 5222 -j ACCEPT
    #iptables -A INPUT -p tcp --dport 5223 -j ACCEPT
    #iptables -A INPUT -p tcp --dport 5269 -j ACCEPT
    #iptables -A INPUT -p tcp --dport 5280 -j ACCEPT
    #iptables -A INPUT -p tcp --dport 5281 -j ACCEPT
		
	# save the firewall
    iptables-save > /etc/firewall.conf
	ip6tables-save > /etc/firewall6.conf
	printf '#!/bin/sh\n' > /etc/network/if-up.d/iptables
	printf 'iptables-restore < /etc/firewall.conf\n' >> /etc/network/if-up.d/iptables
	printf 'ip6tables-restore < /etc/firewall6.conf\n' >> /etc/network/if-up.d/iptables
	if [ -f /etc/network/if-up.d/iptables ]; then
		chmod +x /etc/network/if-up.d/iptables
	fi	
}

function configure_ip {
	# This should be fixed in recent debian versions, but we can do it anyway
    if ! grep -q "tcp_challenge_ack_limit" /etc/sysctl.conf; then
        echo 'net.ipv4.tcp_challenge_ack_limit = 999999999' >> /etc/sysctl.conf
    else
        sed -i 's|net.ipv4.tcp_challenge_ack_limit.*|net.ipv4.tcp_challenge_ack_limit = 999999999|g' /etc/sysctl.conf
    fi

    sed -i "s/#net.ipv4.tcp_syncookies.*/net.ipv4.tcp_syncookies=1/g" /etc/sysctl.conf
	sed -i "s/#net.ipv4.conf.all.accept_redirects.*/net.ipv4.conf.all.accept_redirects = 0/g" /etc/sysctl.conf
	sed -i "s/#net.ipv6.conf.all.accept_redirects.*/net.ipv6.conf.all.accept_redirects = 0/g" /etc/sysctl.conf
	sed -i "s/#net.ipv4.conf.all.send_redirects.*/net.ipv4.conf.all.send_redirects = 0/g" /etc/sysctl.conf
	sed -i "s/#net.ipv4.conf.all.accept_source_route.*/net.ipv4.conf.all.accept_source_route = 0/g" /etc/sysctl.conf
	sed -i "s/#net.ipv6.conf.all.accept_source_route.*/net.ipv6.conf.all.accept_source_route = 0/g" /etc/sysctl.conf
	sed -i "s/#net.ipv4.conf.default.rp_filter.*/net.ipv4.conf.default.rp_filter=1/g" /etc/sysctl.conf
	sed -i "s/#net.ipv4.conf.all.rp_filter.*/net.ipv4.conf.all.rp_filter=1/g" /etc/sysctl.conf
	sed -i "s/#net.ipv4.ip_forward.*/net.ipv4.ip_forward=0/g" /etc/sysctl.conf
	sed -i "s/#net.ipv6.conf.all.forwarding.*/net.ipv6.conf.all.forwarding=0/g" /etc/sysctl.conf

	sed -i "s/# net.ipv4.tcp_syncookies.*/net.ipv4.tcp_syncookies=1/g" /etc/sysctl.conf
	sed -i "s/# net.ipv4.conf.all.accept_redirects.*/net.ipv4.conf.all.accept_redirects = 0/g" /etc/sysctl.conf
	sed -i "s/# net.ipv6.conf.all.accept_redirects.*/net.ipv6.conf.all.accept_redirects = 0/g" /etc/sysctl.conf
	sed -i "s/# net.ipv4.conf.all.send_redirects.*/net.ipv4.conf.all.send_redirects = 0/g" /etc/sysctl.conf
	sed -i "s/# net.ipv4.conf.all.accept_source_route.*/net.ipv4.conf.all.accept_source_route = 0/g" /etc/sysctl.conf
	sed -i "s/# net.ipv6.conf.all.accept_source_route.*/net.ipv6.conf.all.accept_source_route = 0/g" /etc/sysctl.conf
	sed -i "s/# net.ipv4.conf.default.rp_filter.*/net.ipv4.conf.default.rp_filter=1/g" /etc/sysctl.conf
	sed -i "s/# net.ipv4.conf.all.rp_filter.*/net.ipv4.conf.all.rp_filter=1/g" /etc/sysctl.conf
	sed -i "s/# net.ipv4.ip_forward.*/net.ipv4.ip_forward=0/g" /etc/sysctl.conf
	sed -i "s/# net.ipv6.conf.all.forwarding.*/net.ipv6.conf.all.forwarding=0/g" /etc/sysctl.conf

	if [[ $ALLOW_PING != 'yes' ]]; then
		if ! grep -q "ignore pings" /etc/sysctl.conf; then
			echo '# ignore pings' >> /etc/sysctl.conf
			echo 'net.ipv4.icmp_echo_ignore_all = 1' >> /etc/sysctl.conf
			echo 'net.ipv6.icmp_echo_ignore_all = 1' >> /etc/sysctl.conf
		fi
	fi
	
	if ! grep -q "disable ipv6" /etc/sysctl.conf; then
		echo '# disable ipv6' >> /etc/sysctl.conf
		echo 'net.ipv6.conf.all.disable_ipv6 = 1' >> /etc/sysctl.conf
	fi
	if ! grep -q "net.ipv4.tcp_synack_retries" /etc/sysctl.conf; then
		echo 'net.ipv4.tcp_synack_retries = 2' >> /etc/sysctl.conf
		echo 'net.ipv4.tcp_syn_retries = 1' >> /etc/sysctl.conf
	fi
	if ! grep -q "keepalive" /etc/sysctl.conf; then
		echo '# keepalive' >> /etc/sysctl.conf
		echo 'net.ipv4.tcp_keepalive_probes = 9' >> /etc/sysctl.conf
		echo 'net.ipv4.tcp_keepalive_intvl = 75' >> /etc/sysctl.conf
		echo 'net.ipv4.tcp_keepalive_time = 7200' >> /etc/sysctl.conf
	fi
	if ! grep -q "net.ipv4.conf.default.send_redirects" /etc/sysctl.conf; then
		echo "net.ipv4.conf.default.send_redirects = 0" >> /etc/sysctl.conf
	else
		sed -i "s|# net.ipv4.conf.default.send_redirects.*|net.ipv4.conf.default.send_redirects = 0|g" /etc/sysctl.conf
		sed -i "s|#net.ipv4.conf.default.send_redirects.*|net.ipv4.conf.default.send_redirects = 0|g" /etc/sysctl.conf
		sed -i "s|net.ipv4.conf.default.send_redirects.*|net.ipv4.conf.default.send_redirects = 0|g" /etc/sysctl.conf
	fi
	if ! grep -q "net.ipv4.conf.all.secure_redirects" /etc/sysctl.conf; then
		echo "net.ipv4.conf.all.secure_redirects = 0" >> /etc/sysctl.conf
	else
		sed -i "s|# net.ipv4.conf.all.secure_redirects.*|net.ipv4.conf.all.secure_redirects = 0|g" /etc/sysctl.conf
		sed -i "s|#net.ipv4.conf.all.secure_redirects.*|net.ipv4.conf.all.secure_redirects = 0|g" /etc/sysctl.conf
		sed -i "s|net.ipv4.conf.all.secure_redirects.*|net.ipv4.conf.all.secure_redirects = 0|g" /etc/sysctl.conf
	fi
	if ! grep -q "net.ipv4.conf.default.accept_source_route" /etc/sysctl.conf; then
		echo "net.ipv4.conf.default.accept_source_route = 0" >> /etc/sysctl.conf
	else
		sed -i "s|# net.ipv4.conf.default.accept_source_route.*|net.ipv4.conf.default.accept_source_route = 0|g" /etc/sysctl.conf
		sed -i "s|#net.ipv4.conf.default.accept_source_route.*|net.ipv4.conf.default.accept_source_route = 0|g" /etc/sysctl.conf
		sed -i "s|net.ipv4.conf.default.accept_source_route.*|net.ipv4.conf.default.accept_source_route = 0|g" /etc/sysctl.conf
	fi
	if ! grep -q "net.ipv4.conf.default.secure_redirects" /etc/sysctl.conf; then
		echo "net.ipv4.conf.default.secure_redirects = 0" >> /etc/sysctl.conf
	else
		sed -i "s|# net.ipv4.conf.default.secure_redirects.*|net.ipv4.conf.default.secure_redirects = 0|g" /etc/sysctl.conf
		sed -i "s|#net.ipv4.conf.default.secure_redirects.*|net.ipv4.conf.default.secure_redirects = 0|g" /etc/sysctl.conf
		sed -i "s|net.ipv4.conf.default.secure_redirects.*|net.ipv4.conf.default.secure_redirects = 0|g" /etc/sysctl.conf
	fi
	if ! grep -q "net.ipv4.conf.default.accept_redirects" /etc/sysctl.conf; then
		echo "net.ipv4.conf.default.accept_redirects = 0" >> /etc/sysctl.conf
	else
		sed -i "s|# net.ipv4.conf.default.accept_redirects.*|net.ipv4.conf.default.accept_redirects = 0|g" /etc/sysctl.conf
		sed -i "s|#net.ipv4.conf.default.accept_redirects.*|net.ipv4.conf.default.accept_redirects = 0|g" /etc/sysctl.conf
		sed -i "s|net.ipv4.conf.default.accept_redirects.*|net.ipv4.conf.default.accept_redirects = 0|g" /etc/sysctl.conf
	fi
	
    sysctl -p -q
}

function create_repo_sources {
    if [ ! $DEBIAN_VERSION ]; then
        DEBIAN_VERSION='jessie'
    fi
    rm -rf /var/lib/apt/lists/*
    apt-get clean

    echo "deb http://${DEBIAN_REPO}/debian/ ${DEBIAN_VERSION} main" > /etc/apt/sources.list
    echo "deb-src http://${DEBIAN_REPO}/debian/ ${DEBIAN_VERSION} main" >> /etc/apt/sources.list
    echo '' >> /etc/apt/sources.list
    echo "deb http://security.debian.org/ ${DEBIAN_VERSION}/updates main" >> /etc/apt/sources.list
    echo "deb-src http://security.debian.org/ ${DEBIAN_VERSION}/updates main" >> /etc/apt/sources.list
    echo '' >> /etc/apt/sources.list
    echo "deb http://${DEBIAN_REPO}/debian/ ${DEBIAN_VERSION}-updates main" >> /etc/apt/sources.list
    echo "deb-src http://${DEBIAN_REPO}/debian/ ${DEBIAN_VERSION}-updates main" >> /etc/apt/sources.list
    echo '' >> /etc/apt/sources.list
    echo "deb http://${DEBIAN_REPO}/debian/ ${DEBIAN_VERSION}-backports main" >> /etc/apt/sources.list
    echo "deb-src http://${DEBIAN_REPO}/debian/ ${DEBIAN_VERSION}-backports main" >> /etc/apt/sources.list

    apt-get update
    apt-get -yq install git apt-transport-https
}


function install_mariadb {
    apt-get -yq install python-software-properties debconf-utils
    apt-get -yq install software-properties-common

    debconf-set-selections <<< "mariadb-server mariadb-server/root_password password $MARIADB_PASSWORD"
    debconf-set-selections <<< "mariadb-server mariadb-server/root_password_again password $MARIADB_PASSWORD"
    apt-get -yq install mariadb-server

    if [ ! -d /etc/mysql ]; then
        echo $"ERROR: mariadb-server does not appear to have installed. $CHECK_MESSAGE"
        exit 76833
    fi

    if [ ! -f /usr/bin/mysql ]; then
        echo $"ERROR: mariadb-server does not appear to have installed. $CHECK_MESSAGE"
        exit 34672
    fi

    mysqladmin -u root password "$MARIADB_PASSWORD"
}

function install_web_server {
    if [[ $DEBIAN_VERSION != 'stretch' ]]; then
        apt-get -yq install php-gettext php5-curl php5-gd php5-mysql git curl
        apt-get -yq install php5-memcached php5-intl php-xml-parser
        apt-get -yq remove --purge apache2
        if [ -d /etc/apache2 ]; then
            rm -rf /etc/apache2
        fi

        apt-get -yq install nginx
        apt-get -yq install php5-fpm
    else
        apt-get -yq install php-gettext php7.0-curl php7.0-gd php7.0-mysql git curl
        apt-get -yq install php-memcached php7.0-intl php-xml-parser
        apt-get -yq remove --purge apache2
        if [ -d /etc/apache2 ]; then
            rm -rf /etc/apache2
        fi

        apt-get -yq install nginx
        apt-get -yq install php7.0-fpm
    fi
}

function create_postactiv_database {
    echo "create database postactiv;
CREATE USER '${POSTACTIV_ADMIN_USER}@localhost' IDENTIFIED BY '${POSTACTIV_ADMIN_PASSWORD}';
GRANT ALL PRIVILEGES ON postactiv.* TO '${POSTACTIV_ADMIN_USER}@localhost';
quit" > ~/batch.sql
    chmod 600 ~/batch.sql
    mysql -u root --password="$MARIADB_PASSWORD" < ~/batch.sql
    shred -zu ~/batch.sql
}

function install_postactiv_from_repo {
    # Clone the PostActiv repo
    if [ ! -d /var/www/postactiv ]; then
        git clone $POSTACTIV_REPO /var/www/postactiv
    else
        cd /var/www/postactiv
        git stash
        git checkout master
        git pull
    fi
    cd /var/www/postactiv
    git checkout $POSTACTIV_COMMIT -b $POSTACTIV_COMMIT

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
                           --dbtype=mysql --username="$POSTACTIV_ADMIN_USER@localhost" -v \
                           --password="$POSTACTIV_ADMIN_PASSWORD" \
                           --sitename=$"postactiv" --fancy='yes' \
                           --admin-nick="$POSTACTIV_ADMIN_USER" \
                           --admin-pass="$POSTACTIV_ADMIN_PASSWORD" \
                           --site-profile="community" \
                           --ssl="always"
}

function configure_tls_cert {
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
    if [[ $DEBIAN_VERSION != 'stretch' ]]; then
        apt-get -yq install certbot -t jessie-backports
    else
        apt-get -yq install certbot
    fi
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
}

function configure_web_server {
    echo "server {
    listen 80;
    listen [::]:80;
    server_name ${POSTACTIV_DOMAIN_NAME};
    root /var/www/postactiv;
    access_log /var/log/nginx/postactiv.access.log;
    error_log /var/log/nginx/postactiv.err.log warn;
    client_max_body_size 20m;
    client_body_buffer_size 128k;

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
  location /scripts/ {
    deny all;
  }

}" > /etc/nginx/sites-available/postactiv
    ln -s /etc/nginx/sites-available/postactiv /etc/nginx/sites-enabled/

    # Start the web server
    if [[ $DEBIAN_VERSION == 'stretch' ]]; then
        sed -i 's|php5|php7.0|g' /etc/nginx/sites-available/postactiv
        systemctl restart php7.0-fpm
    else
        systemctl restart php5-fpm
    fi
    systemctl start nginx
}

function additional_postactiv_settings {
    postactiv_config_file=/var/www/postactiv/config.php

    echo "" >> $postactiv_config_file
    echo "// Recommended postactiv settings" >> $postactiv_config_file
    echo "\$config['thumbnail']['maxsize'] = 3000;" >> $postactiv_config_file
    echo "\$config['profile']['delete'] = true;" >> $postactiv_config_file
    echo "\$config['profile']['changenick'] = true;" >> $postactiv_config_file
    echo "\$config['public']['localonly'] = false;" >> $postactiv_config_file
    echo "addPlugin('StoreRemoteMedia');" >> $postactiv_config_file
    echo "\$config['queue']['enabled'] = true;" >> $postactiv_config_file
    echo "\$config['queue']['daemon'] = true;" >> $postactiv_config_file
    echo "\$config['ostatus']['hub_retries'] = 3;" >> $postactiv_config_file

    # This improves performance
    sed -i "s|//\$config\['db'\]\['schemacheck'\].*|\$config\['db'\]\['schemacheck'\] = 'script';|g" $postactiv_config_file

    # remove the install script
    if [ -f /var/www/postactiv/install.php ]; then
        rm /var/www/postactiv/install.php
    fi
}

function keep_daemons_running {
    echo '#!/bin/bash' > /etc/cron.hourly/postactiv-daemons
    echo -n 'daemon_lines=$(ps aux | grep "' >> /etc/cron.hourly/postactiv-daemons
    echo 'postactiv/scripts/queuedaemon.php" | grep "/var/www")' >> /etc/cron.hourly/postactiv-daemons
    echo 'cd /var/www/postactiv' >> /etc/cron.hourly/postactiv-daemons
    echo 'if [[ $daemon_lines != *"/var/www/"* ]]; then' >> /etc/cron.hourly/postactiv-daemons

    echo '    scripts/startdaemons.sh' >> /etc/cron.hourly/postactiv-daemons
    echo 'fi' >> /etc/cron.hourly/postactiv-daemons

    echo 'php scripts/delete_orphan_files.php > /dev/null' >> /etc/cron.hourly/postactiv-daemons
    echo 'php scripts/clean_thumbnails.php -y > /dev/null' >> /etc/cron.hourly/postactiv-daemons
    echo 'php scripts/clean_file_table.php -y > /dev/null' >> /etc/cron.hourly/postactiv-daemons
    echo 'php scripts/upgrade.php > /dev/null' >> /etc/cron.hourly/postactiv-daemons

    chmod +x /etc/cron.hourly/postactiv-daemons
}

function install_qvitter {
    mkdir -p /var/www/postactiv/local/plugins

    git clone $QVITTER_THEME_REPO /var/www/postactiv/local/plugins/Qvitter
    if [ ! -d /var/www/postactiv/local/plugins/Qvitter ]; then
        echo "Couldn't clone Qvitter"
        exit 6278254
    fi
    cd /var/www/postactiv/local/plugins/Qvitter
    git checkout $QVITTER_THEME_COMMIT -b $QVITTER_THEME_COMMIT

    config_file=/var/www/postactiv/config.php
    if ! grep -q "addPlugin('Qvitter')" $config_file; then
        echo "" >> $config_file
        echo "// Qvitter settings" >> $config_file
        echo "addPlugin('Qvitter');" >> $config_file
        echo "\$config['site']['qvitter']['enabledbydefault'] = true;" >> $config_file
        echo "\$config['site']['qvitter']['defaultbackgroundcolor'] = '#f4f4f4';" >> $config_file
        echo "\$config['site']['qvitter']['defaultlinkcolor'] = '#0084B4';" >> $config_file
        echo "\$config['site']['qvitter']['timebetweenpolling'] = 30000; // 30 secs" >> $config_file
        echo "\$config['site']['qvitter']['favicon'] = 'img/favicon.ico?v=4';" >> $config_file
        echo "\$config['site']['qvitter']['sprite'] = Plugin::staticPath('Qvitter', '').'img/sprite.png?v=40';" >> $config_file
        echo "\$config['site']['qvitter']['enablewelcometext'] = false;" >> $config_file
        echo "\$config['site']['qvitter']['blocked_ips'] = array();" >> $config_file
    fi

    chown -R www-data:www-data /var/www/postactiv

    cd /var/www/postactiv
    php scripts/upgrade.php
    php scripts/checkschema.php
    chown -R www-data:www-data /var/www/postactiv
}

if [ ! $1 ]; then
    echo './scripts/debian_install.sh [mariadb password] [username] [password] [domain] [email address] [jessie|stretch]'
    exit 0
fi

create_firewall
configure_ip
create_repo_sources
install_mariadb
install_web_server
create_postactiv_database
install_postactiv_from_repo
configure_tls_cert
configure_web_server
additional_postactiv_settings
keep_daemons_running
install_qvitter

echo "postActiv installed"

exit 0
