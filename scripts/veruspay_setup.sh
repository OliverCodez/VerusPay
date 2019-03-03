#!/bin/bash
#set working directory to the location of this script
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd $DIR
echo "===================================================="
echo "          WELCOME TO THE VERUSPAY SETUP!            "
echo "                                                    "
echo "This setup script is intended for sites which have  "
echo "WordPress and WooCommerce already installed on a VPS"
echo "or Dedicated server.  If you are wanting to install "
echo "VerusPay on a new server, please exit this script   "
echo "using CTRL-Z and instead use the veruspay_install.sh"
echo "script.                                             "
echo "                                                    "
echo "Installer will begin in 10 seconds...               "
echo "                                                    "
echo "===================================================="
echo ""
sleep 10
echo ""
echo "Beginning VerusPay setup..."
echo ""
#Begin operations
sleep 3
clear
echo "Installing dependencies for Verus CLI wallet..."
sudo apt -qq update
sudo apt --yes -qq install build-essential pkg-config libc6-dev m4 g++-multilib autoconf libtool ncurses-dev unzip git python python-zmq zlib1g-dev wget libcurl4-openssl-dev bsdmainutils automake curl screen
sudo apt -qq update
sudo apt -y -qq autoremove
sleep 3
clear
echo "Downloading and unpacking VerusPay scripts..."
echo ""
echo ""
cd ~
wget https://veruspay.io/setup/veruspayscripts.tar.xz
tar -xvf veruspayscripts.tar.xz
cd veruspayscripts
chmod +x *
rm do_*.sh
echo "Downloading and unpacking latest Verus CLI release..."
echo ""
echo ""
cd ~
wget https://veruspay.io/setup/latestverus.tar.gz
tar -xvf latestverus.tar.gz
sleep 3
clear
echo "Fetching Zcash parameters..."
echo ""
echo ""
./verus-cli/fetch-params
sleep 3
clear
echo "Downloading and unpacking VRSC bootstrap..."
echo ""
echo ""
wget https://bootstrap.0x03.services/veruscoin/VRSC-bootstrap.tar.gz
mkdir -p .komodo/VRSC
tar -xvf VRSC-bootstrap.tar.gz -C .komodo/VRSC/
sleep 3
clear
echo "Starting new screen and running Verus daemon to begin Verus blockchain sync..."
echo ""
echo ""
screen -d -m ./verus-cli/verusd -mint -daemon
echo "Installing cron job to run verusstat script every 5 min to check Verus daemon status and start if it stops..."
echo ""
echo ""
crontab -l > tempcron
echo "*/5 * * * * /home/$USER/veruspayscripts/verusstat" >> tempcron
crontab tempcron
rm tempcron
cd ~
echo ""
echo ""
echo "Cleaning up..."
rm *.tar.*
sleep 3
clear
rpcuser=$(cat .komodo/VRSC/VRSC.conf | grep "rpcuser=" | cut -d= -f2- )
rpcpass=$(cat .komodo/VRSC/VRSC.conf | grep "rpcpassword=" | cut -d= -f2- )
clear
echo ""
echo ""
echo "=================================="
echo "IMPORTANT! Below Are Your New Credentials and Details. Write This Information Down in a Secure Place:"
echo "=================================="
echo "  -----------------------------   "
echo "  - RPC Credentials for Verus -   "
echo ""
echo "  RPC User: "$rpcuser
echo "  RPC Pass: "$rpcpass
echo "=================================="
echo ""