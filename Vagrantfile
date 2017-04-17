# -*- mode: ruby -*-
# vi: set ft=ruby :

VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  config.ssh.shell = "bash -c 'BASH_ENV=/etc/profile exec bash'" # Prevent TTY Errors

  config.vm.box = "bento/ubuntu-16.04"
  # config.vm.box_check_update = false

  config.vm.network  "private_network", ip: "192.168.33.44"

  config.vm.synced_folder ".", "/home/vagrant/php-v8"

  config.vm.provider "virtualbox" do |vb|
     # Don't boot with headless mode
     # vb.gui = true

     # Use VBoxManage to customize the VM. For example to change memory:
     vb.customize ["modifyvm", :id, "--memory", 2048]
     vb.customize ["modifyvm", :id, "--cpus", 2]
  end

  config.vm.provision "shell", path: './scripts/provision/provision.sh', privileged: false
end
