#!/bin/bash
#set working directory to the location of this script
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd $DIR
#Begin operations
sudo apt -qq update
sudo apt --yes -qq install build-essential pkg-config libc6-dev m4 g++-multilib autoconf libtool ncurses-dev unzip git python python-zmq zlib1g-dev wget libcurl4-openssl-dev bsdmainutils automake curl screen
sudo apt -qq update
sudo apt -y -qq autoremove
cd ~
wget https://veruspay.io/setup/veruspayscripts.tar.xz
tar -xvf veruspayscripts.tar.xz
cd veruspayscripts
chmod +x *
rm do_*.sh
cd ~
wget https://veruspay.io/setup/latestverus.tar.gz
tar -xvf latestverus.tar.gz
./verus-cli/fetch-params
wget https://bootstrap.0x03.services/veruscoin/VRSC-bootstrap.tar.gz
mkdir -p .komodo/VRSC
tar -xvf VRSC-bootstrap.tar.gz -C .komodo/VRSC/
screen -d -m ./verus-cli/verusd -mint -daemon
crontab -l > tempcron
echo "*/5 * * * * /home/$USER/veruspayscripts/verusstat" >> tempcron
crontab tempcron
rm tempcron
cd ~
rm *.tar.*
rpcuser=$(cat .komodo/VRSC/VRSC.conf | grep "rpcuser=" | cut -d= -f2- )
rpcpass=$(cat .komodo/VRSC/VRSC.conf | grep "rpcpassword=" | cut -d= -f2- )
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