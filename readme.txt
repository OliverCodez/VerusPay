## VerusPay for WooCommerce

 - Contributors: John Westbrook
 - Tags: woocommerce, payment gateway, gateway, cryptocurrency, blockchain, verus, verus coin, vrsc
 - Requires at least: 3.8
 - Tested up to: 5.0.3
 - Requires WooCommerce at least: 2.1
 - Tested WooCommerce up to: 3.5.4
 - Stable Tag: 0.1.0
 * ====================
 * 
 * The MIT License (MIT)
 * 
 * Copyright (c) 2019 John Westbrook
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

== Description ==

> **Requires: WooCommerce 2.1+**

This plugin extends WooCommerce, adding the ability to accept cryptocurrency payments in Verus Coin (VRSC) using either an on-store wallet daemon (best for VPS or dedicated hosting stores) or manually configured VRSC addresses (best for shared hosting stores).

When an order is submitted via the VerusPay gateway, the order will be placed "on-hold" while awaiting payment from the customer. The customer has a limited time wherein to send the payment and the store monitors the wallet/address to confirm payment received before releasing the order and redirecting the customer to the Thank You page.

== More Details ==
 - Learn about the [Verus Coin official site](https://veruscoin.io) for more information about the community project
 - Join the [Verus Coin Discord](https://discord.gg/VRKMP2S) for support. 
 - More documentation coming in a next release.
 
== Requirements ==

There are two modes in which you can run VerusPay: Live or Manual

Live Mode
In Live Mode you connect directly with the RPC daemon of a Verus wallet on your e-commerce VPS or similar server.  This is NOT intended for shared hosting plans, you must be on a VPS or similar where you have SSH access and the ability to run applications per your hosting providers terms.

Suggested Server Minimum: 2GB RAM, 1 CPU, CentOS or Ubuntu

Manual Mode
Manual Mode is always a "fallback", even for Live Mode operation, but also allows shared hosting store owners to use VerusPay without the additional step of setting up and configuring a Verus wallet on their web store.  You still need access to a Verus wallet you own however.

== Plugin Installation ==

1. Be sure you're running WooCommerce 2.1+ in your store.
2. Either: (1) upload the entire `woocommerce-gateway-veruspay` folder to the `/wp-content/plugins/` directory, or (2) upload the .zip file with the plugin under **Plugins &gt; Add New &gt; Upload**
3. Activate the plugin through the **Plugins** menu in WordPress
4. Go to **WooCommerce &gt; Settings &gt; Checkout** or **WooCommerce &gt; Settings &gt; Payments** in newer versions, and select "VerusPay" to configure

== Verus Wallet Installation == 

1. From the web server in an SSH session first get the dependencies with: `sudo apt --yes install build-essential pkg-config libc6-dev m4 g++-multilib autoconf libtool ncurses-dev unzip git python python-zmq zlib1g-dev wget libcurl4-openssl-dev bsdmainutils automake curl`
2. Next, get the latest version of Verus CLI for Linux here: `https://github.com/VerusCoin/VerusCoin/releases/latest`
3. Unzip the file in your server's home directory with: `tar -xvf Verus-CLI-Linux-version.tar.gz`
4. For faster install, create the VRSC hidden directory and download the latest Verus bootstrap. Create the directory with: `mkdir -p .komodo/VRSC` and download and unzip the bootstrap into that directory: `wget https://bootstrap.0x03.services/veruscoin/VRSC-bootstrap.tar.gz`
5. Install the Zcash params, from within your home directory with: `./verus-cli/fetch-params`
6. After this completes, you can start the daemon which will begin syncing and create a new Verus wallet for your store with: `./verus-cli/verusd -daemon`

== Configuration of VerusPay ==

1. Import Verus Transparent Addresses
For either Live or Manual mode, you MUST input many Verus transparent addresses.  The number you enter depends on the sale volume of your store and you'll want to keep an eye on it to see if you need to add more.  It's important to use addresses from an OFFLINE wallet, not your same store wallet if you're running in Live Mode.  To do so, from the offline/non-store computer where you have a wallet setup (I recommend setting up a new wallet for this purpose) use the script included with the plugin in the scripts folder, called get500t, and copy it into your "verus-cli" folder, then run it after giving it execute permission with: chmod +x get500t. It will generate a file in that same folder called "taddresses.txt" containing 500 new transparent Verus addresses for your wallet.  Copy these, exactly as they are in the text file, and paste into the Store Addresses section of the VerusPay settings in WooCommerce, in the "Store VRSC Addresses" text field, then save your settings.  If addresses are ever used, due to the store operating in Manual Mode, they will be moved from this text field to the Used Addresses field.
2. RPC Settings for Live Mode
If you're operating in Live Mode, you'll need to enter the login information for the Verus daemon running on your web store.  To get this information, SSH to your web store and in the home directory for the user with which you installed and are running the Verus daemon, go to the .komodo/VRSC folder and then open the file called VRSC.conf using a program like "nano".  Within this file, copy the user, pass, host, and port info and paste it into the VerusPay RPC Settings fields for each and save your settings in WooCommerce.
3. Optional Settings
There are many optional settings to play with, I recommend playing with and tweaking everything before using it in a live scenario.

== Support ==
You can contact me via the Verus Coin official Discord at https://discord.gg/VRKMP2S

== Frequently Asked Questions ==

**What is the text domain for translations?**
The text domain is `wc-gateway-veruspay`.

== Changelog ==

= 2019.02.11 - version 0.1.0 =
 * Initial Release
