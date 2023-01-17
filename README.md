# CALLSTATS
----------------------------------------------------------
Collect & display call statistics for HiPath OpenOffice ME

Everything here is before 2010.

![Screenshot](callstats-screenshot.png?raw=true "Screenshot")

#REQUIREMENTS
---------------------------------------------------------
-sqlite 3.x
-Apache (tested with 2.2)
-PHP 5.2.x (or later) with sqlite3 PDO support

#INSTALLATION
---------------------------------------------------------
1)put everything under a directory accessible from your web server
2)compile & install netlog 

inside the  contrib/netlog folder run the following:
make clean;make
mkdir -p /usr/local/sbin/
cp netlog /usr/local/sbin/

3)install contrib/calllog.initd as init startup script. 
##For Redhat/CentOS:

cp  callog.initd /etc/init.d/calllog  
chkconfig --add calllog   
chkconfig calllog on

* the last two lines create the appropriate links to start calllog on each boot
* if your HiPath sends data to a different port than the default, 
* then edit /etc/init.d/calllog and change the default port (24274).

4)Start the call logging. As user root run the following:
/etc/init.d/calllog start

You should see log entries appended in the file /var/log/call.log. If not:

* check that netlog is running (ps -ef|grep netlog)
* check that your firewall is not preventing connections to port 24274 (/etc/init.d/iptables status)
* check that your Siemens system is configured to send logs to your host

5)Try to append the log file to your database manualy:
Run the following: From the command line, *not* from the web!
dbupdate.php 

This will append /var/log/call.log into calls.db sqlite file.

6)Update root's crontab to append the log file to your database daily:
Edit the line to reflect your web server path, and add it to root crontab.  
(10 4) means this will run every day at 4:10 (am)

```
10 4 * * * /usr/local/apache2/htdocs/callstats/dbupdate.php
```


