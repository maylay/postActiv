This directory contains [Vagrant](https://www.vagrantup.com/) build
instructions to make it easy to test GNU Social.

To run it should be sufficient to simply go into one of the subdirs and run
`vagrant up`. When developing you'll probably want to run `vagrant rsync-auto`
in the same dir as well as that keeps the source dir in the virtual machine in
sync with your host system. You can find your instance at `http://localhost:8080`.

Internally the provisioning (getting a VM just right so postActiv will run in it)
is done with [Ansible](https://www.ansible.com), see `playbook.yml`.

The machines currently are all Debian based, so you can find the apache logs under
`/var/www/apache2` (you'll need to sudo to read them). The GNU Social logs are
usually best viewed with `journalctl`.
