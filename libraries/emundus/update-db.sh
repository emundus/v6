#!/bin/bash

set -o pipefail   # Unveils hidden failures

current_date=`date +"%m/%d/%Y %H:%M:%S"`

# -------- paths settings ---------

configuration_file=../../configuration.php
sql_update_path=../../administrator/components/com_admin/sql/updates/mysql/
log_path=../../logs/emundus-db-update.log

# ---------------------------------

# ------ /tmp clean settings ------

tmp_log_path=../../logs/emundus-clean-tmp.log
tmp_path=../../tmp/

# ---------------------------------


# --------- Database Vars ---------

declare -A db_config

db_config[database]=`cat $configuration_file | grep -Po "(?<=public .db = ')[^']+(?=')"`
db_config[user]=`cat $configuration_file | grep -Po "(?<=public .user = ')[^']+(?=')"`
db_config[password]=`cat $configuration_file | grep -Po "(?<=public .password = ')[^']+(?=')"`
db_config[hostname]=`cat $configuration_file | grep -Po "(?<=public .host = ')[^'|:]{3,}"`
db_config[prefix]="jos"

if [[ -z `cat $configuration_file | grep -Po "(?<=public .host = '${db_config[hostname]}:)[0-9]{2,5}"` ]]; then
    db_config[port]="3306"
else
    db_config[port]=`cat $configuration_file | grep -Po "(?<=public .host = '${db_config[hostname]}:)[0-9]{2,5}"`
fi

# ---------------------------------

# TMP CLEANER
        echo "-----------------------------------------------------------------------------" >> $tmp_log_path
        echo "Begin clean up for : $tmp_path" >> $tmp_log_path
        ### search and remove patern for xls pdf and zip files in $i with a +7 mtime parameter
        find $tmp_path -name '*.xls' -mtime +7 -exec rm {} \;
        find $tmp_path -name '*.xlsx' -mtime +7 -exec rm {} \;
        find $tmp_path -name '*.pdf' -mtime +7 -exec rm {} \;
        find $tmp_path -name '*.zip' -mtime +7 -exec rm {} \;
        echo "Folder ` $tmp_path ` is cleaned at $current_date" >> $tmp_log_path
        echo "-----------------------------------------------------------------------------" >> $tmp_log_path



#debug vars

# echo $db_config[database]
# echo $db_config[hostname]
# echo $db_config[port]
# echo $db_config[user]
# echo $db_config[password]


# Fix replace #_ to $dbprefix (by default: jos_)
sed -i "s:"#_":"$db_prefix":g" $sql_update_path*

# Init an Array that will contain all sql update files
emundus_tableau=()

# Get present version id value by SQL Query 
actual_version_id="$(mysql -u "${db_config[user]}" -p"${db_config[password]}" -h "${db_config[hostname]}" -P "${db_config[port]}" -e "SELECT version_id FROM \`${db_config[database]}\`.jos_schemas WHERE extension_id=700" | grep -E "[0-9]").sql"

echo -e "Emundus SQL Update Tool \n\n"

# Display present version id

echo -e "Actual version_id: ${actual_version_id%.sql} \n"


# Aggregate array with sql update files from Emundus dedicated folder
for version_id in `ls $sql_update_path | sort -V` 
do
        emundus_tableau+=($version_id)
done

# display amount of array entries
echo -e "Array contain ${#emundus_tableau[@]} versions\n"


# Debug with echo by string
for i in "${emundus_tableau[@]}"
do
    if [ "$i" == "$actual_version_id" ] ; then
        echo -e "Found result with : $i \n"
    fi
done

## Search position to begin scripts execution

for i in "${!emundus_tableau[@]}";
do

        # Manage case --> No need to update something, we just check if $actual_version_id correspond to last array key
        if [[ "${emundus_tableau[$((${#emundus_tableau[@]}-1))]}" == "${actual_version_id}" ]];
        then
                echo "You are Up-To-Date ! "
                break
        fi

    # Manage all others
        if [[ "${emundus_tableau[$i]}" == "${actual_version_id}" ]];
        then
                echo -e "Array index position to begin update is : $((${i} + 1)) \n"
                position_update="$((${i} + 1))"

                echo -e "All theses files will be executed : \n"

                        for j in "${emundus_tableau[@]:$position_update}";
                        do
                                echo -e "- $j"
                        done

                        # Execute .sql files one by one with log
                        for sqlfile in "${emundus_tableau[@]:$position_update}";
                        do
                                echo -e "Starting update with : $sqlfile at $current_date" >> $log_path

                                exec_sql=$(mysql -u "${db_config[user]}" -p"${db_config[password]}" -h "${db_config[hostname]}" -P "${db_config[port]}" ${db_config[database]} < $sql_update_path$sqlfile)
                                # "$?" Return 0 or 1 if mysql command failed 0 = success 1 = error
                                exitstatus=$?

                                if [ "$exitstatus" -eq "0" ]
                                then
                                        update_version=$(mysql -u "${db_config[user]}" -p"${db_config[password]}" -h "${db_config[hostname]}" -P "${db_config[port]}" -e "UPDATE \`$db_config[database]\`.jos_schemas SET version_id = '${sqlfile%.sql}' WHERE extension_id=700" 2>&1 | tee -a $log_path)
                                        echo -e "Finishing update successfuly with : $sqlfile at $current_date"  >> $log_path
                                else
                                        echo -e "Error during update with : $sqlfile at $current_date"  >> $log_path
                                        break
                                fi
                        done
        fi

done



# debug display array content
#declare -p emundus_tableau
