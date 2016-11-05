This directory contains [Vagrant](https://www.vagrantup.com/) build
instructions to make it easy to test postActiv.

To run it should be sufficient to simply go into one of the subdirs and run
`vagrant up`. When developing you'll probably want to run `vagrant rsync-auto`
in the same dir as well as that keeps the source dir in the virtual machine in
sync with your host system. You can find your instance at `http://localhost:8080`,
the admin account username is `admin`, password `admin`.

Note that the php7 playbook requires vagrant > 1.8.1 due to ubuntu Xenial
installing a newer ansible than vagrant 1.8.1 expects.

Internally the provisioning (getting a VM just right so postActiv will run in it)
is done with [Ansible](https://www.ansible.com), see `playbook.yml`.

The machines currently are all Debian based, so you can find the apache logs under
`/var/www/apache2` (you'll need to sudo to read them). The GNU Social logs are
usually best viewed with `journalctl`.

## Phan

The apache-php7-mariadb setup contains [Phan](https://github.com/etsy/phan), a
PHP static analyzer. To run it simply run `vagrant up` in the apache-php7-mariadb
dir and then `vagrant ssh -c './runphan.sh'`. Be prepared for rather a load of output.

## Setups

**apache-php5-mysql**

* Debian Jessie (8.0)
* Apache with mod_php
* PHP5
* MySQL

**apache-php7-mariadb**

* Ubuntu Xenial (16.04)
* Apache with FastCGI proxy
* PHP5 (with FPM)
* MariaDB
* Bonus: phan installed (see Phan section)
