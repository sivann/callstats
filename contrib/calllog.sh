#!/bin/bash

#read network data from call center and log to file
#sivann 2009

port=24274
file="/var/log/call.log"

touch "${file}"
chmod 660 "${file}"
chown root.root "${file}"

exec -a "$0"  /usr/bin/nc -d -k -l 24274 >> "${file}"
