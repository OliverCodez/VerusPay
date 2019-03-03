#!/bin/bash
#set working directory to the location of this script
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd $DIR
for run in $(seq 1 "$@")
do
  addr=$(./resources/app/assets/bin/linux64/verusd/verus getnewaddress)
  echo $addr"," >> taddresses.txt
done
