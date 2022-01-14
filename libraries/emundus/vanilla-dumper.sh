#!/bin/bash

set -o pipefail   # Unveils hidden failures


# ================= Helper ===============================

Help()
{
   # Display Help
   echo "[eMundus Vanilla Dump Tool]"
   echo
   echo "An eMundus tool to generate a sqlfile dump with cleanup tasks for 'vanilla_emjmd' database versionning "
   echo "Syntax: ./vanilla-dumper.sh [-h|t]"
   echo "options:"
   echo "h     Display Help"
   echo "t     Gitlab Token <string> (with read and write permissions)"
   echo
   exit 1
}

# =========================================================

# ================= Bash Getops Vars ======================

gitlab_token=""


# =========================================================

# ================= Getops ================================

# Get the options
while getopts ":ht:" option; do
   case $option in
      h) Help exit      ;;
      t) gitlab_token=$OPTARG   ;;
      \?) echo "Error: Invalid option" >&2
         exit  ;;
   esac
done

# If gitlab_token is *empty*
if [[ -z $gitlab_token ]]; then
    echo "Option -t is missing with a validate personal token. Check Buttercup > eMundusSaaS to get it" >&2
    exit 1
fi

# =========================================================

# ================= Configuration =========================

# --------- Vanilla Vars ----------

vanilla_git_url="https://gitlab-ci:${gitlab_token}@git.emundus.io/emundus/cms/vanilla_emjmd.git"
random_hash=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 16 | head -n 1)

# --------- Platform Vars ---------

configuration_file=${0%/*}/../../configuration.php # Need this script is located in an eMundus project at libraries/emundus directory

declare -A git_platform

git_platform[recent_tag]=`git describe --tag | awk -F'-' '{print $1}'`
git_platform[commits_on_top]=`git describe --tag | awk -F'-' '{print $2}'`
git_platform[current_commit_hash]=`git describe --tag | awk -F'-' '{print $3}'`


# --------- Database Vars ---------

declare -A db_config

db_config[database]=`cat $configuration_file | grep -Po "(?<=public .db = ')[^']+(?=')"`
db_config[user]=`cat $configuration_file | grep -Po "(?<=public .user = ')[^']+(?=')"`
db_config[password]=`cat $configuration_file | grep -Po "(?<=public .password = ')[^']+(?=')"`
db_config[hostname]=`cat $configuration_file | grep -Po "(?<=public .host = ')[^'|:]{3,}"`



if [[ -z `cat $configuration_file | grep -Po "(?<=public .host = '${db_config[hostname]}:)[0-9]{2,5}"` ]]; then

    db_config[port]="3306"

else

    db_config[port]=`cat $configuration_file | grep -Po "(?<=public .host = '${db_config[hostname]}:)[0-9]{2,5}"`
fi



# =========================================================

echo -e "[eMundus Vanilla Dumper] \n"


# -- Exporting and cleaning the database

echo -e "[TASK No.1] : Clone a fresh vanilla repository at /tmp/vanilla_dump_$(date +"%Y-%m-%d")_${random_hash} \n"
git clone $vanilla_git_url /tmp/vanilla_dump_$(date +"%Y-%m-%d")_${random_hash}



echo -e "[TASK No.2] : Create branch 'release/${git_platform[recent_tag]}' on emundus/cms/vanilla_emjmd repository \n"
git -C /tmp/vanilla_dump_$(date +"%Y-%m-%d")_${random_hash} checkout -b release/${git_platform[recent_tag]}
git -C /tmp/vanilla_dump_$(date +"%Y-%m-%d")_${random_hash} push -u origin release/${git_platform[recent_tag]}



echo -e "[TASK No.3] : Execute clean-db.sql in '${db_config[database]}' hosted at '${db_config[hostname]}:${db_config[port]}' \n"
mysql -u "${db_config[user]}" -p"${db_config[password]}" -P "${db_config[port]}" -h "${db_config[hostname]}" ${db_config[database]} < /tmp/vanilla_dump_$(date +"%Y-%m-%d")_${random_hash}/clean-DB.sql




echo -e "[TASK No.4] : mysqldump '${db_config[database]}' to /tmp/vanilla_dump_$(date +"%Y-%m-%d")_${random_hash}/vanilla_emjmd.sql \n"
mysqldump -u "${db_config[user]}" -p"${db_config[password]}" -P "${db_config[port]}" -h "${db_config[hostname]}" ${db_config[database]} > /tmp/vanilla_dump_$(date +"%Y-%m-%d")_${random_hash}/vanilla_emjmd.sql




echo -e "[TASK No.5] : Remove every lines which contain DEFINER \n"
sed -i '/DEFINER/d' /tmp/vanilla_dump_$(date +"%Y-%m-%d")_${random_hash}/vanilla_emjmd.sql




# -- Pushing the code (new vanilla database)

echo -e "[TASK No.6] : Create new commit for emundus/cms/vanilla_emjmd Gitlab repository \n"
git -C /tmp/vanilla_dump_$(date +"%Y-%m-%d")_${random_hash} add .
git -C /tmp/vanilla_dump_$(date +"%Y-%m-%d")_${random_hash} commit -m "[UPDATE] new database 🔥 | associated_release: ${git_platform[recent_tag]} | associated_commit_hash: ${git_platform[current_commit_hash]}"
git -C /tmp/vanilla_dump_$(date +"%Y-%m-%d")_${random_hash} push




# -- Clean

echo -e "[TASK No.7] : Clean /tmp \n"
rm -rf /tmp/vanilla_dump_$(date +"%Y-%m-%d")_${random_hash}/
