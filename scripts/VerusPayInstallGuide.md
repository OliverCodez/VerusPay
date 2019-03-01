
# Server, LAMP, and Wallet Setup and Configuration Instructions

### 1 - Get a server with ubuntu 18.04 OS and at min 2GB RAM 

You can get $100 credit with DigitalOcean if you use my referral link (this also helps continue supporting the project as I'll get $25 of credit toward the hosted veruspay.io server after you spend $25)

Link: https://m.do.co/c/13c092042583

### 2 - SSH in and change the root password when prompted

### 3 - Create new Sudo user with the following commands, replacing USERNAME with the username you want:

`adduser USERNAME`
`usermod -aG sudo USERNAME`

At this point, log off and back in as the new user

Next, do the following commands to disable root SSH access:

`sudo nano /etc/ssh/sshd_config`

Inside this file, find and change `PermitRootLogin yes` to `PermitRootLogin no` and save with CTRL-O and CTRL-X

Last, download the scripts by issuing the following commands:

`cd ~`
`wget https://veruspay.io/setup/veruspayserverinstall.tar.gz`
`tar -xvf veruspayserverinstall.tar.gz`
`cd veruspayserverinstall`
`chmod +x *`

#### Make sure you are in the folder: `~/veruspayserverinstall` for the remainder of this guide!

### 4 - Run the server config script

Now run script_1 from your SSH login by issuing `./script_1`

### 5 - Run verus in screen and Apache install script

First, let's get Verus syncing up while we finish the install. To do this, start a new screen and then start the daemon:

`screen`
`./verus-cli/verusd -mint -daemon`

To disconnect from the screen issue: CTRL-D

Now run script_2 from your SSH login by issuing `./script_2` from your home folder.

### 6 - Now run the new website setup script.  This will setup a your site in a way that later you can add additional websites to the same server!

Now run script_3 from your SSH login by issuing `./script_3` from your home folder.

### 7 - Setup MySQL and PHP

You will be prompted during this process and need to follow this guide as the script runs.

To begin, run script_4 from your SSH login by issuing `./script_4` from your home folder.

At the first prompt, asking if you want to VALIDATE passwords, for simplicity choose "No"

Next, you will be presented with a file containing the line of text `DirectoryIndex index.html index.cgi index.pl index.php index.xhtml index.htm`

You want to change it so the index.php is first in the list after DirectoryIndex, like this: 
`DirectoryIndex index.php index.html index.cgi index.pl index.xhtml index.htm`

Next, you'll be at a mysql> prompt, issue each of the following commands, changing the SECUREPASS to a secure password only you know:

`SELECT user,authentication_string,plugin,host FROM mysql.user;`
`ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'SECUREPASS';`
`FLUSH PRIVILEGES;`
`exit;`

### 8 - Now setup SSL, choosing option 2 when prompted.  If you have an error, make sure your domain is pointing to your server's IP with A records for both the domain and the www of your domain and that your IP is accessible over port 443. You can test this first with `telnet YOURIP 443`

Now run script_5 from your SSH login by issuing `./script_5` from your home folder.

Press ENTER when prompted.

### 9 - Setup WordPress Database

Be sure to modify the following commands before you issue/paste them in ssh! Modify the following:

wp_yourdomain - change to reflect your domain name...for example if your domain is www.example.com, this would be wp_example
UNIQUEWPUSER - This should be a username you use for this wordpress install, like wp_example_user or something only you know
UNIQUEWPPASS - This is the password and should be strong, unique, and only known by you.

Remember these three values!  You'll use them later.

`mysql -u root -p`
`CREATE DATABASE wp_yourdomain DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;`
`GRANT ALL ON wp_yourdomain.* TO 'UNIQUEWPUSER'@'localhost' IDENTIFIED BY 'UNIQUEWPPASS';`
`FLUSH PRIVILEGES;`
`exit;`

### 10 - Setup PHP Modules and WordPress Files

Next, run script_6 from your SSH login by issuing `./script_6` from your home folder.

### 11 - Setup WordPress Config file

This last step requires editing the WordPress wp-config.php file.  To make life easier when replacing the salt section, use CTRL-K to cut the lines that you'll be replacing. To do this, move your cursor to the line to remove, press CTRL-K and the line disappears.  Do this for the 8 lines we will be changing, then you can paste your new salt generated into that section of the file.

First get your new salt:

`curl -s https://api.wordpress.org/secret-key/1.1/salt/`

Now copy the output and edit your wp-config.php file.  Use the following line replacing YOURDOMAIN with your domain name WITHOUT the www:

`sudo nano /var/www/YOURDOMAIN/html/wp-config.php`

Inside the file find the section that has the 8 lines we are replacing. It looks like this:
``define('AUTH_KEY',         'put your unique phrase here');
define('SECURE_AUTH_KEY',  'put your unique phrase here');
define('LOGGED_IN_KEY',    'put your unique phrase here');
define('NONCE_KEY',        'put your unique phrase here');
define('AUTH_SALT',        'put your unique phrase here');
define('SECURE_AUTH_SALT', 'put your unique phrase here');
define('LOGGED_IN_SALT',   'put your unique phrase here');
define('NONCE_SALT',       'put your unique phrase here');``

Move your cursor to the top line, press CTRL-K and it will remove it, repeat for each line.

After you've removed the placeholder lines, paste the salt you copied.

Now change the database name, username, and password to match what you created in Step 9, the placeholder section in the config file looks like this: 

``define('DB_NAME', 'wordpress');

/** MySQL database username */
define('DB_USER', 'wordpressuser');

/** MySQL database password */
define('DB_PASSWORD', 'password');``

Lastly, add the following line to the file, just after `define('DB_COLLATE', '');`:

`define('FS_METHOD', 'direct');`

Save with CTRL-O and exit with CTRL-X.  

If you are lost in this part of the guide, refer to https://www.digitalocean.com/community/tutorials/how-to-install-wordpress-with-lamp-on-ubuntu-18-04#step-5-%E2%80%93-configuring-the-wordpress-directory beginning at the heading "Setting up the WordPress Configuration File" 

### Step 12 - Completing Setup

Lastly, issue the following command: 

`sudo systemctl restart apache2`

Now visit your domain and you should see the WordPress setup!

As a final note, it's extremely important to enable ssh key login and disable password login.  To learn how, follow this guide: https://www.digitalocean.com/docs/droplets/how-to/add-ssh-keys/ 

Notes:

* If you reboot your server you will need to SSH in and start the Verus daemon.  Refer to Step 5 on how to start the daemon.

* You can create bash scripts and crontabs to keep the daemon alive, start automatically after a reboot, etc. Ask me in Discord how if interested.
