#!/bin/bash
# 
# Restarts n servers (specified as stations through command 
# arguments) through ssh under the following assumptions:
#
#   * The station exists 
#   * Is registered through indycast.net
#   * Public keys will work
#   * The server code is in DRR/server
#   

if [ "$0" == "./upgrade_and_restart_through_ssh.sh" ]; then
  echo "Doing upgrade"
  upgrade=";rm -f .git/refs/remotes/origin/master.lock;git pull;./bootstrap.sh"
else
  echo "NOT upgrading"
  upgrade=""
fi

pid_list=""
while [ $# -gt 0 ]; do
  station=$1
  ssh $station.indycast.net "cd DRR$upgrade;cd server;pkill $station;./indy_server.py -c configs/$station.txt --daemon &" &
  pid_list=$?" "$pid
  shift
done

sleep 120

for pid in $pid_list; do
  kill $pid
done
