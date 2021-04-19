#!/bin/bash

current_date=`date +"%m/%d/%Y %H:%M:%S"`

## Folder SETTINGS
configuration_file=../../configuration.php
sql_file=../../modules/mod_emundusmenu/install_tchooz_menu.sql
log_path=../../logs/emundus-db-install_tchooz_menu.log

## Database SETTINGS
mysql_db=`cat $configuration_file | grep -Po "(?<=public .db = ')[^']+(?=')"`
mysql_user=`cat $configuration_file | grep -Po "(?<=public .user = ')[^']+(?=')"`
mysql_pass=`cat $configuration_file | grep -Po "(?<=public .password = ')[^']+(?=')"`
mysql_host=`cat $configuration_file | grep -Po "(?<=public .host = ')[^']+(?=:)"`
mysql_port="4008"
db_prefix="jos"

if [[ -z $mysql_host ]];
then
	mysql_host="localhost"
	mysql_port="3306"
fi


# Fix replace #_ to $dbprefix (by default: jos_)
sed -i "s:"#_":"$db_prefix":g" $sql_file

echo -e "Emundus SQL Install Tchooz Menu \n\n"

# Display present version id

echo -e "Installing Tchooz Menu... \n"

exec_sql=$(mysql -u $mysql_user -p$mysql_pass -h $mysql_host -P $mysql_port $mysql_db < $sql_file)
# "$?" Return 0 or 1 if mysql command failed 0 = success 1 = error
exitstatus=$?

if [ "$exitstatus" -eq "0" ]
then
	echo -e "Finishing to install Tchooz menu successfuly with : $sql_file at $current_date"  >> $log_path
else
	echo -e "Error during the installation with : $sql_file at $current_date"  >> $log_path
fi

