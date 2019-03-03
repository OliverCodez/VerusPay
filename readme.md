=== VerusPay Verus Gateway ===

Contributors: veruspay, joliverwestbrook
Donate link: https://veruspay.io/donate/
Tags: woocommerce, payment gateway, gateway, cryptocurrency, blockchain, verus, verus coin, vrsc
Requires at least: 3.8
Tested up to: 5.1
Requires PHP: 7.0
Stable tag: 0.1.2
Requires WooCommerce at least: 2.1
Tested WooCommerce up to: 3.5.5
License: MIT
License URI: https://opensource.org/licenses/MIT

 * ====================
 * 
 * The MIT License (MIT)
 * 
 * Copyright (c) 2019 John Oliver Westbrook
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * 
 * ====================

=== Description ===

> **Requires: WooCommerce 2.1+**

- The Latest Release of VerusPay is Available Here: https://veruspay.io/latest/

This plugin extends WooCommerce and integrates with the Verus blockchain, adding the ability to accept cryptocurrency payments in Verus Coin (VRSC) using either an on-store wallet daemon (best for VPS or dedicated hosting stores) or manually configured VRSC addresses (best for shared hosting stores).

When an order is submitted via the VerusPay gateway, the order will be placed "on-hold" while awaiting payment from the customer. The customer has a limited time wherein to send the payment and the store monitors the wallet/address to confirm payment received before releasing the order and redirecting the customer to the Thank You page.

VerusPay uses limited API functionality for Manual Mode, to communicate with the blockchain explorer in verifying payments and with the veruspay.io API to get up-to-date, volume-weighted price data.  These API's do not receive any private data either about the store owner, store, or customer.  The only data sent to the block explorer API is the public/transparent blockchain transaction and address used.  For VerusPay.io API price data, only the store-set currency is sent to retrieve the current fiat exchange rate for Verus Coin.

API's used periodically by VerusPay:

1 - https://veruspay.io/api/

2 - https://explorer.veruscoin.io

=== More Details ===

 - Learn about the [Verus Coin official site](https://veruscoin.io) for more information about the community project
 - Join the [Verus Coin Discord](https://discord.gg/VRKMP2S) for support. 
 - More documentation is available at https://veruspay.io 
 
=== Requirements ===

There are two modes in which you can run VerusPay: Live or Manual

Live Mode

Store VPS or Dedicated Server
Suggested Store Server Minimum: 2GB RAM, 1 CPU, CentOS or Ubuntu

Additional "Offline" Verus Wallet
I recommend using a seperate Verus CLI (command line interface) wallet just for use with your store, not your primary/personal wallet.  Consider setting up a DigitalOcean $5 server for this using this guide: http://bit.ly/2Ca6LIK

In Live Mode VerusPay integrates with the Verus blockchain on your e-commerce VPS or dedicated server.  This is NOT intended for shared hosting plans, you must be on a VPS or dedicated server where you have SSH access and the ability to run applications per your hosting provider terms.

Manual Mode

Store-dedicated "Offline" Verus Wallet
I recommend using a seperate Verus CLI (command line interface) wallet just for use with your store, not your primary/personal wallet. I call this an "offline" wallet simply to differentiate it from the store-online wallet.  It should be connected to the network. Consider setting up a DigitalOcean $5 server for this using this guide: http://bit.ly/2Ca6LIK

Manual Mode is always a "fallback", even for Live Mode operation, but also allows shared hosting store owners to use VerusPay without the additional step of setting up and configuring a Verus wallet on their web store.  You still need access to a Verus wallet you own however.

=== Installation ===

*If you already have a WooCommerce store running on a VPS or dedicated server with a min of 2GB RAM, skip to Step 3-b.

1. Setup a VPS with a minimum of 2GB RAM. Use the following link for $100 of free hosting credit: https://m.do.co/c/13c092042583

2. After setup, SSH to your server and create a new user with the following commands (replace USERNAME with the username you choose):

- `adduser USERNAME`
- `usermod -aG sudo USERNAME`

Log off as root, and log in as the new user to your server. Then follow this guide to switch to ssh-key logins: https://www.digitalocean.com/docs/droplets/how-to/add-ssh-keys/

3-a. Log in with SSH as your new user and run the VerusPay Install script using the following commands (the install process will take 5 to 10 min to complete and may look like nothing is happening for a while, just let it complete):

- `cd ~`
- `wget https://veruspay.io/setup/veruspay_install.sh`
- `chmod +x veruspay_install.sh`
- `./veruspay_install.sh`

After the install finishes, it will display IMPORTANT information for you to write down in a secure location. BE SURE TO WRITE THIS INFORMATION DOWN. 

3-b (Optional/Conditional). Already have a VPS or Dedicated server running a live WooCommerce store, log in as your SSH user and run the VerusPay Setup script using the following commands (the install process may take 5 to 10 min to complete and may look as though nothing is happening for a while, just let it complete):

- `cd ~`
- `wget https://veruspay.io/setup/veruspay_setup.sh`
- `chmod +x veruspay_setup.sh`
- `./veruspay_setup.sh`

After the install finishes, it will display IMPORTANT information for you to write down in a secure location. BE SURE TO WRITE THIS INFORMATION DOWN. 

4. From your "Offline" Verus wallet server or computer, run the script to generate many additional transparent VRSC addresses.  Download the appropriate script for your OS to your "Offline" Verus wallet system from this link: https://veruspay.io/scripts/ 

Place the script in your "offline" wallet's main folder (verus-cli) and execute it.  In Linux or Mac run with: `./generate 500` where "500" is the number of addresses to generate (I recommend a min of 500).  In Windows run it with `generate 500`

The script will create a file in the same folder called VerusPayGeneratedAddresses.txt. You'll copy and paste these addresses in the VerusPay settings in a later step.

5. Log into WordPres and from Plugins->Add New search for, install and activate WooCommerce.  WOOCOMMERCE MUST BE INSTALLED, ACTIVATED, AND CONFIGURED BEFORE VERUSPAY IS INSTALLED

6. From Plugins->Add New search for, install and activate VerusPay. Access VerusPay settings via the VerusPay menu item on the left Admin menu bar.

7. Configure VerusPay with RPC, store addresses, and any additional configurations you want to make.

Use the RPC User and RPC Pass displayed in Step 3 in your RPC Settings, leave the other fields as they are and save the settings.

Paste the transparent addresses you created in Step 4 into the Store VRSC Addresses field under Store Addresses heading. and save the settings.

Lastly, customize store messages and any other settings and options to your liking.

=== Support ===

If you encounter any errors during install and configuration of VerusPay, please report them to me via email at johnwestbrook@pm.me or you can contact me via the Verus Coin official Discord at https://discord.gg/VRKMP2S

=== Frequently Asked Questions ===

**What is the text domain for translations?**
The text domain is `veruspay-verus-gateway`.

**How can I change the styling (CSS) of the checkout pages?**
CSS options are being added in a later release to the Admin section. For now, most themes come with a section where you can add your own CSS styling and you can add customizations this way.

**Does this plugin work with a shared hosting plan or a host that does not have SSH access?**
Yes! But it will be "Manual Mode" only, meaning it is not directly connected or integrated with the blockchain and relies on transparent addresses you enter in the store settings (zs Sapling addresses are not allowed).

**Can I "enforce" privacy Sapling payments?**
Yes, there is an option in the payment gateway settings within WooCommerce->Settings->Payments to enforce privacy "zs" payments.  This works in Live Mode only.

== Screenshots ==
 
1. This is the main settings area for VerusPay within WooCommerce's `Settings->Payments` section. Each of the headings expand upon clicking.

2. The RPC Settings section is where the store owner enters the connection settings for Verus blockchain integration.

3. Store owners input VRSC transparent addresses in the "Store VRSC Addresses" text field which are used when the store is in manual mode, either by the store owner's choice or as a fallback if there is any issue in connecting with the blockchain.

4. The Message and Content Customizations section allows store owners to create custom messages to be used throughout the purchase process when a customer pays with Verus.

5. Store Options allow the store owner to define many of the attributes of the checkout and purchase process with regards to timeouts, privacy, and QR codes.

6. The Discount/Fee option allows a store owner to define either an additional fee or a discount which is applied to customers who checkout using Verus.

7. At checkout, this is what the Verus payment option looks like to the customer, although CSS may be applied by advanced users to slightly alter this.

8. After a customer proceeds to purchase, this is the screen they are presented with where they are able to pay and see payment receipt occur live.

9. After payment is detected on the blockchain, the purchase waits for the store-set minimum confirmations and delivers the digital product or completes the sale when it's reached.

=== Changelog ===

= 2019.02.28 - version 0.1.2 =

- Simplify code functions
- Remove cURL, phpexttools, easybitcoin
- Create wp_remote_post and get functions for all cURL requests
- Consolidate all blockchain integration functions into `wc-veruspay-chaintools.php`
- Rename all functions to unique names
- Edit text domain and slug to match as veruspay-verus-gateway
- Include uninstall script for clean uninstallation of plugin
- Improve readme file
- Rename screenshots

= 2019.02.25 - version 0.1.1-a =

- Fix bug with QR Invoice format
- Add hide function of QR Invoice if Sapling purchase (sapling not currently supported in Verus Mobile)


= 2019.02.24 - version 0.1.1 =

- Add Verus Mobile Invoice compatible QR codes for orders
- Add admin customization for memo and image url within Invoice QRs


= 2019.02.11 - version 0.1.0 =

 * Initial Release Includes the Following Features:
 
- Verus Coin (VRSC) support
- Sapling zk-SNARK private payment support as an option to the customer or enforced by the store
- Verus blockchain integration in Live mode allows dynamic address generation and live status monitoring of payments for both transparent and private.
- Manual mode allows use of the plugin with a pool of addresses imported by the store owner.
- A script is included for exporting a list of transparent addresses to a text file to import into the store.
- Fallback to manual mode if Live mode fails
- Enforce Sapling option for store owner removes the option at checkout, all Verus payments must be Sapling.
- Message customization for store owners.
- VRSC Decimal definition for store owners.
- Price timeout allows the store owner to define for how many minutes a Verus / Fiat price will display before getting a new price at checkout.
- Order wait time allows the store owner to define how long an order will remain open before cancelled while waiting for the full payment amount to arrive from the customer at the VRSC address.
- Confirmations required allows the store owner to define how many blockchain confirmations are required before a payment is considered final and the customer's order is considered completed.
- QR Code size option in PX for the store owner
- Discount or Fee option for store owners allows a discount or fee % to be set for when a customer chooses to pay in Verus Coin (VRSC).
- Test mode to allow enabling the plugin only for logged in Admins

== Upgrade Notice ==

= 0.1.2 =
Code improvements

= 0.1.1-a =
Bug fixes

= 0.1.1 =
Add QR Invoice functionality
