#!/bin/bash
#set working directory to the location of this script
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd $DIR
#Get variables and user input
clear
echo "===================================================="
echo "         WELCOME TO THE VERUSPAY INSTALLER!         "
echo "                                                    "
echo "This installer is meant for NEW SERVERS ONLY. If you"
echo "are already running WordPress on this server, you   "
echo "should abort now with CTRL-Z. Otherwise continue by "
echo "answering the following questions.                  "
echo "                                                    "
echo "Installer will begin in 10 seconds                  "
echo "                                                    "
echo "===================================================="
echo ""
sleep 10
echo "What is your primary domain? Enter WITHOUT the www (e.g. yourdomain.com):"
read domain
shopt -s nocasematch
echo ""
echo "Include support for the www version for your domain? (e.g. www.yourdomain.com) (yes or no)"
read wwwans
if [[ $wwwans == "yes" ]] || [[ $wwwans == "y" ]];
then
    export subdomain="www."
else
    export subdomain=""
fi
echo ""
echo "Email address for you as the admin (used for Apache and SSL settings only):"
read email
export domain
export email
[ "$passlength" == "" ] && passlength=32
export rootpass=$(tr -dc A-Za-z0-9_ < /dev/urandom | head -c ${passlength} | xargs)
export wppass=$(tr -dc A-Za-z0-9_ < /dev/urandom | head -c ${passlength} | xargs)
[ "$namelength" == "" ] && namelength=6
export wpdb="wp_db_"$(tr -dc A-Za-z0-9_ < /dev/urandom | head -c ${namelength} | xargs)
export wpuser="wp_us_"$(tr -dc A-Za-z0-9_ < /dev/urandom | head -c ${namelength} | xargs)
#Begin operations
echo ""
echo "Thank you. Beginning server configuration!"
echo ""
sudo fallocate -l 4G /swapfile
echo "Setting up 4GB swap file..."
sleep 3
sudo chmod 600 /swapfile
sudo mkswap /swapfile
sudo swapon /swapfile
sudo cp /etc/fstab /etc/fstab.bk
echo "/swapfile none swap sw 0 0" | sudo tee -a /etc/fstab
echo "vm.swappiness=40" | sudo tee -a /etc/sysctl.conf
echo "vm.vfs_cache_pressure=50" | sudo tee -a /etc/sysctl.conf
clear
echo "Installing dependencies for Verus CLI wallet..."
echo ""
echo ""
sleep 3
sudo apt -qq update
sudo apt --yes -qq install build-essential pkg-config libc6-dev m4 g++-multilib autoconf libtool ncurses-dev unzip git python python-zmq zlib1g-dev wget libcurl4-openssl-dev bsdmainutils automake curl screen
sudo apt -qq update
sudo apt -y -qq autoremove
clear
echo "Downloading and unpacking VerusPay scripts..."
echo ""
echo ""
sleep 3
cd ~
wget https://veruspay.io/setup/veruspayscripts.tar.xz
tar -xvf veruspayscripts.tar.xz
cd veruspayscripts
chmod +x *
mv do_*.sh ~
cd ~
echo "Downloading and unpacking latest Verus CLI release..."
echo ""
echo ""
sleep 3
wget https://veruspay.io/setup/latestverus.tar.gz
tar -xvf latestverus.tar.gz
clear
echo "Fetching Zcash parameters..."
echo ""
echo ""
sleep 3
./verus-cli/fetch-params
clear
echo "Downloading and unpacking VRSC bootstrap..."
echo ""
echo ""
sleep 3
wget https://bootstrap.0x03.services/veruscoin/VRSC-bootstrap.tar.gz
mkdir -p .komodo/VRSC
tar -xvf VRSC-bootstrap.tar.gz -C .komodo/VRSC/
clear
echo "Starting new screen and running Verus daemon to begin Verus blockchain sync..."
echo ""
echo ""
sleep 6
screen -d -m ./verus-cli/verusd -mint -daemon
echo "Installing cron job to run verusstat script every 5 min to check Verus daemon status and start if it stops..."
echo ""
echo ""
sleep 6
crontab -l > tempcron
echo "*/5 * * * * /home/$USER/veruspayscripts/verusstat" >> tempcron
crontab tempcron
rm tempcron
clear
echo "Installing Apache..."
echo ""
echo ""
sleep 3
sudo apt --yes -qq install apache2
sudo ufw allow OpenSSH
sudo ufw allow "Apache Full"
echo "y" | sudo ufw enable
sudo cp /etc/apache2/apache2.conf /etc/apache2/apache2.conf.bak
echo "ServerName $domain" | sudo tee -a /etc/apache2/apache2.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
clear
echo "Configuring $domain directory and enabling Apache config..."
echo ""
echo ""
sleep 6
sudo mkdir -p /var/www/$domain/html
sudo chmod -R 755 /var/www/$domain
sudo touch /etc/apache2/sites-available/$domain.conf
echo "<VirtualHost *:80>" | sudo tee -a /etc/apache2/sites-available/$domain.conf
echo "    ServerAdmin $email" | sudo tee -a /etc/apache2/sites-available/$domain.conf
echo "    ServerName $domain" | sudo tee -a /etc/apache2/sites-available/$domain.conf
echo "    ServerAlias $subdomain$domain" | sudo tee -a /etc/apache2/sites-available/$domain.conf
echo "    DocumentRoot /var/www/$domain/html" | sudo tee -a /etc/apache2/sites-available/$domain.conf
echo "    ErrorLog ${APACHE_LOG_DIR}/error.log" | sudo tee -a /etc/apache2/sites-available/$domain.conf
echo "    CustomLog ${APACHE_LOG_DIR}/access.log combined" | sudo tee -a /etc/apache2/sites-available/$domain.conf
echo "RewriteEngine on" | sudo tee -a /etc/apache2/sites-available/$domain.conf
echo "RewriteCond %{SERVER_NAME} =$subdomain$domain [OR]" | sudo tee -a /etc/apache2/sites-available/$domain.conf
echo "RewriteCond %{SERVER_NAME} =$domain" | sudo tee -a /etc/apache2/sites-available/$domain.conf
echo "RewriteRule ^ https://%{SERVER_NAME}%{REQUEST_URI} [END,NE,R=permanent]" | sudo tee -a /etc/apache2/sites-available/$domain.conf
echo "<Directory /var/www/$domain/html/>" | sudo tee -a /etc/apache2/sites-available/$domain.conf
echo "	AllowOverride All" | sudo tee -a /etc/apache2/sites-available/$domain.conf
echo "</Directory>" | sudo tee -a /etc/apache2/sites-available/$domain.conf
echo "</VirtualHost>" | sudo tee -a /etc/apache2/sites-available/$domain.conf
sudo a2ensite $domain.conf
sudo ufw delete allow "Apache Full"
sudo ufw allow "Apache Full"
echo "y" | sudo ufw enable
clear
echo "Disabling default config..."
echo ""
echo ""
sleep 3
sudo a2dissite 000-default.conf
sudo systemctl reload apache2
clear
echo "Installing and configuring MySQL services and WordPress Database and DB user..."
echo ""
echo ""
sleep 6
sudo apt --yes -qq install mysql-server expect
#Run expect script for mysql, retain environment vars
sudo -E ./do_mysql_secure.sh
sudo apt --yes -qq install php libapache2-mod-php php-mysql
sudo rm /etc/apache2/mods-available/dir.conf
echo "<IfModule mod_dir.c>" | sudo tee -a /etc/apache2/mods-available/dir.conf
echo "        DirectoryIndex index.php index.html index.cgi index.pl index.xhtml index.htm" | sudo tee -a /etc/apache2/mods-available/dir.conf
echo "</IfModule>" | sudo tee -a /etc/apache2/mods-available/dir.conf
echo "# vim: syntax=apache ts=4 sw=4 sts=4 sr noet" | sudo tee -a /etc/apache2/mods-available/dir.conf
sudo systemctl restart apache2
clear
echo "Installing CertBot and setting up SSL with Lets Encrypt..."
echo ""
echo ""
sleep 6
sudo add-apt-repository -y ppa:certbot/certbot
sudo apt --yes -qq install python-certbot-apache
sudo systemctl reload apache2
sudo -E ./do_certs.sh
clear
echo "Installing WordPress dependencies..."
echo ""
echo ""
sleep 3
sudo apt -qq update
sudo apt --yes -qq install php-curl php-gd php-mbstring php-xml php-xmlrpc php-soap php-intl php-zip
sudo systemctl restart apache2
clear
echo "Downloading and unpacking latest WordPress..."
echo ""
echo ""
sleep 3
cd /tmp
curl -O https://wordpress.org/latest.tar.gz
tar xzvf latest.tar.gz
clear
echo "Configuring WordPress files, folders, permissions, and wp-config.php file..."
echo ""
echo ""
sleep 6
touch /tmp/wordpress/.htaccess
cp /tmp/wordpress/wp-config-sample.php /tmp/wordpress/wp-config.php
mkdir /tmp/wordpress/wp-content/upgrade
sudo cp -a /tmp/wordpress/. /var/www/$domain/html
sudo perl -pi -e "s/database_name_here/$wpdb/g" /var/www/$domain/html/wp-config.php
sudo perl -pi -e "s/username_here/$wpuser/g" /var/www/$domain/html/wp-config.php
sudo perl -pi -e "s/password_here/$wppass/g" /var/www/$domain/html/wp-config.php
sudo perl -i -pe'
  BEGIN {
    @chars = ("a" .. "z", "A" .. "Z", 0 .. 9);
    push @chars, split //, "!@#$%^&*()-_ []{}<>~\`+=,.;:/?|";
    sub salt { join "", map $chars[ rand @chars ], 1 .. 64 }
  }
  s/put your unique phrase here/salt()/ge
' /var/www/$domain/html/wp-config.php
sudo chown -R www-data:www-data /var/www/$domain/html
sudo find /var/www/$domain/html/ -type d -exec chmod 750 {} \;
sudo find /var/www/$domain/html/ -type f -exec chmod 640 {} \;
clear
echo "Setting up simple postfix mail services for WordPress..."
echo ""
echo ""
sleep 3
#Setup mail services
sudo debconf-set-selections <<< "postfix postfix/mailname string $domain"
sudo debconf-set-selections <<< "postfix postfix/main_mailer_type string Internet Site"
sudo debconf-set-selections <<< "postfix postfix/mailbox_size_limit string 0"
sudo debconf-set-selections <<< "postfix postfix/recipient_delimiter string +"
sudo debconf-set-selections <<< "postfix postfix/inet_interfaces string loopback-only"
sudo apt --yes -qq install postfix
clear
echo "Cleaning up..."
echo ""
echo ""
sleep 3
sudo apt -y -qq purge expect
rm /tmp/latest.tar.gz
cd ~
rm *.tar.*
rm do_*.sh
rpcuser=$(cat .komodo/VRSC/VRSC.conf | grep "rpcuser=" | cut -d= -f2- )
rpcpass=$(cat .komodo/VRSC/VRSC.conf | grep "rpcpassword=" | cut -d= -f2- )
clear
echo ""
echo ""
echo "=================================="
echo "IMPORTANT! Below Are Your New Credentials and Details. Write This Information Down in a Secure Place:"
echo "=================================="
echo "  Root MySQL password: "$rootpass
echo "  WordPress DB Name: "$wpdb
echo "  WordPress DB User: "$wpuser
echo "  WordPress DB Pass: "$wppass
echo "  -----------------------------   "
echo "  - RPC Credentials for Verus -   "
echo ""
echo "  RPC User: "$rpcuser
echo "  RPC Pass: "$rpcpass
echo "=================================="
echo ""