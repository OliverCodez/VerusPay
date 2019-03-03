
# Server, LAMP, and Wallet Setup and Configuration Instructions

What this guide and accompanying scripts will do:

Setup a new, unconfigured server with your first domain name (or a first domain) with WordPress and Verus CLI wallet.  You'll then need to configure WordPress as normal, install WooCommerce and then VerusPay plugin.  Here's what you'll end up with after this guide and scripts:
- Apache2 with multi-domain support
- MySQL
- PHP
- WordPress base install w/ mail capability
- Sync'd up Verus CLI wallet
- Cron job to keep Verus wallet alive (checks every 5 min)

After running the script, make sure to write down the information it displays and keep in a safe place.

### Before you begin

For a fully functional VerusPay blockchain-integrated experience, you will need a VPS or Dedicated server with a min of 2GB RAM running Ubuntu 18.04+.  You will also need an additional "offline" Verus wallet (preferrably the CLI wallet) running on a seperate machine/server ("offline" doesn't mean off the network in this context, just off the live webstore).  You can setup both as servers...a live store server and a dedicated "wallet server" for the "offline" wallet.  The min requirement for the "offline" wallet is 1GB RAM.

Last quick note: if you already have an operational VPS or Dedicated server web store running WooCommerce, skip down to Step 4-b.

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

Next, I recommend enabling SSH key login, over password login, as it's much more secure.  To learn how, follow this guide: https://www.digitalocean.com/docs/droplets/how-to/add-ssh-keys/ 

### Step 4-a. Log in with SSH as your new user and run the VerusPay Install script using the following commands (the install process will take 5 to 10 min to complete and may look like nothing is happening for a while, just let it complete):

`cd ~`

`wget https://veruspay.io/setup/veruspay_install.sh`

`chmod +x veruspay_install.sh`

`./veruspay_install.sh`

After the install finishes, it will display IMPORTANT information for you to write down in a secure location. BE SURE TO WRITE THIS INFORMATION DOWN. 

### Step 4-b (Optional/Conditional if you already have a WooCommerce VPS store running). Already have a VPS or Dedicated server running a live WooCommerce store, log in as your SSH user and run the VerusPay Setup script using the following commands (the install process may take 5 to 10 min to complete and may look as though nothing is happening for a while, just let it complete):

`cd ~`

`wget https://veruspay.io/setup/veruspay_setup.sh`

`chmod +x veruspay_setup.sh`

`./veruspay_setup.sh`

After the install finishes, it will display IMPORTANT information for you to write down in a secure location. BE SURE TO WRITE THIS INFORMATION DOWN. 

### Step 5 - Install WooCommerce & VerusPay

From your "Offline" Verus wallet server or computer, run the script to generate many additional transparent VRSC addresses.  Download the appropriate script for your OS to your "Offline" Verus wallet system from this link: https://veruspay.io/setup/scripts/

Place the script in your "offline" wallet's main folder (verus-cli) and execute it.  In Linux or Mac run with: `./getaddresses.sh 500` where "500" is the number of addresses to generate (I recommend a min of 500).  In Windows run it with `getaddresses.bat 500`

The script will create a file in the same folder called VerusPayGeneratedAddresses.txt. You'll copy and paste these addresses in the VerusPay settings in a later step.

### Step 6 - Setup the WooCommerce and VerusPay Plugins and Configure VerusPay

Log into WordPres and from Plugins->Add New search for, install and activate WooCommerce.  WOOCOMMERCE MUST BE INSTALLED, ACTIVATED, AND CONFIGURED BEFORE VERUSPAY IS INSTALLED

From Plugins->Add New search for, install and activate VerusPay. Access VerusPay settings via the VerusPay menu item on the left Admin menu bar.

Configure VerusPay with RPC, store addresses, and any additional configurations you want to make.

Use the RPC User and RPC Pass displayed in Step 3 in your RPC Settings, leave the other fields as they are and save the settings.

Paste the transparent addresses you created in Step 4 into the Store VRSC Addresses field under Store Addresses heading. and save the settings.

Lastly, customize store messages and any other settings and options to your liking.

#### Notes:

* Although both the Install and Setup scripts will install a cron job to make sure Verus daemon stays "alive" every 5 min, if you reboot your server you will need to SSH in and confirm Verus is running (or check from the VerusPay settings within WordPress)
