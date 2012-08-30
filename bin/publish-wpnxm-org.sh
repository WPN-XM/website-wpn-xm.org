#!/bin/sh

#
# Publishes the "wpn-xm.org" website.
#
# This script pulls the WPN-XM master branch zip from github.
# Then moves the "website-wpm-xm.org" folder to the public folder of the webserver.
#
# Do not forget:
# a) to sync "develop" with "master"
# b) to chmod +x publish-wpnxm-org.sh
#

# pull master branch from github
wget https://github.com/WPN-XM/WPN-XM/zipball/master --no-check-certificate -O wpnxm-github-master.zip

# guess what...
unzip wpnxm-github-master.zip

# rename the crappy github hash directory name
mv jakoch-WPN-XM-* wpnxm

# publish website
# by copying "website-wpn-xm.org" sub-directory to the webservers public folder
cp -r wpnxm/website-wpn-xm.org/* /var/www/webs/KochSST/wpnxm

# add "updatecheck" and "get" (download redirection) scripts to the webfolder
cp -r wpnxm/updater/get.php /var/www/webs/KochSST/wpnxm
cp -r wpnxm/updater/updatecheck.php /var/www/webs/KochSST/wpnxm
# add the "wpnxm software registry"
cp -r wpnxm/updater/wpnxm-software-registry.php /var/www/webs/KochSST/wpnxm

# cleanup
rm wpnxm-github-master.zip
rm -r wpnxm