
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

### Step 4 - Run the VerusPay install script

From your SSH login in your new server, issue the following commands to begin the install:

`cd ~`
`wget https://veruspay.io/setup/veruspay_install.sh`
`chmod +x veruspay_install.sh`
`./veruspay_install.sh`

Wait for the install to complete (takes 5 to 10 min and will look like it's not working sometimes, just let it finish). 

After the install completes, it will display your DB login information and RPC user and password.   IMPORTANT: Write this information down and keep it safe!

### Step 5 - Install WooCommerce & VerusPay

Visit your new domain in a browser and complete the WordPress setup and config steps.  

Once at your Dashboard, go to the Plugins section and search for WooCommerce, then install and activate it.  Go through the store setup steps.

Once WooCommerce is setup, download the latest VerusPay release from https://veruspay.io/latest and at the Plugin install screen of WordPress, go to Upload Plugin and Browse to find your downloaded zip file, then click Install Now.  After install, click Activate. 

You'll see a VerusPay icon on the left menu toward the bottom, click it and you can configure VerusPay.

Use the RPC User and Pass that was displayed at the completion of the VerusPay install in Step 4, within the RPC Settings of VerusPay and save. You'll notice when it saves it will show "hidden" again in those fields, this is intentional.

### Step 15 - Generate 500+ Store Addresses

Last, generate many transparent (not private) Verus addresses from a DIFFERENT Verus wallet...NOT THE STORE WALLET.  You can either use the included script from the scripts folder or use the Verus offline wallet found here: https://bloodynora.github.io/VerusPaperWallet/

Generate a minimum of 500. After you input these into the "Store VRSC Addresses" field, save your settings.  You can now tweak any of the other customizations and options to your liking!


#### Notes:

* If you reboot your server you will need to SSH in and confirm Verus is running (or check from the VerusPay settings within WordPress)
