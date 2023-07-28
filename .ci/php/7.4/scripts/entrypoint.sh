#!/bin/bash

if [ ! -f /scripts/init-done ] ; then

sed -i "s:\$offline = '.*':\$offline = '$TCHOOZ_OFFLINE':g" configuration.php
sed -i "s:\$offline_message = '.*':\$offline_message = '$TCHOOZ_OFFLINE_MESSAGE':g" configuration.php
sed -i "s:\$display_offline_message = '.*':\$display_offline_message = '$TCHOOZ_DISPLAY_OFFLINE_MESSAGE':g" configuration.php
sed -i "s:\$offline_image = '.*':\$offline_image = '$TCHOOZ_OFFLINE_IMAGE':g" configuration.php
sed -i "s:\$sitename = '.*':\$sitename = '$TCHOOZ_SITENAME':g" configuration.php
sed -i "s:\$editor = '.*':\$editor = '$TCHOOZ_EDITOR':g" configuration.php
sed -i "s:\$debug = '.*':\$debug = '$TCHOOZ_DEBUG':g" configuration.php
sed -i "s:\$debug_lang = '.*':\$debug_lang = '$TCHOOZ_DEBUG_LANG':g" configuration.php
sed -i "s:\$host = '.*':\$host = '$TCHOOZ_DB_HOST':g" configuration.php
sed -i "s:\$user = '.*':\$user = '$TCHOOZ_DB_USER':g" configuration.php
sed -i "s:\$password = '.*':\$password = '$TCHOOZ_DB_PASSWORD':g" configuration.php
sed -i "s:\$db = '.*':\$db = '$TCHOOZ_DB_NAME':g" configuration.php
sed -i "s:\$live_site = '.*':\$live_site = '$TCHOOZ_LIVE_SITE':g" configuration.php
sed -i "s:\$secret = '.*':\$secret = '$TCHOOZ_SECRET':g" configuration.php
sed -i "s:\$offset = '.*':\$offset = '$TCHOOZ_OFFSET':g" configuration.php
sed -i "s:\$mailer = '.*':\$mailer = '$TCHOOZ_MAILER':g" configuration.php
sed -i "s:\$mailfrom = '.*':\$mailfrom = '$TCHOOZ_MAIL_FROM':g" configuration.php
sed -i "s:\$fromname = '.*':\$fromname = '$TCHOOZ_MAIL_FROM_NAME':g" configuration.php
sed -i "s:\$smtpauth = '.*':\$smtpauth = '$TCHOOZ_MAIL_SMTP_AUTH':g" configuration.php
sed -i "s:\$smtpuser = '.*':\$smtpuser = '$TCHOOZ_MAIL_SMTP_USER':g" configuration.php
sed -i "s:\$smtppass = '.*':\$smtppass = '$TCHOOZ_MAIL_SMTP_PASS':g" configuration.php
sed -i "s:\$smtphost = '.*':\$smtphost = '$TCHOOZ_MAIL_SMTP_HOST':g" configuration.php
sed -i "s:\$smtpsecure = '.*':\$smtpsecure = '$TCHOOZ_MAIL_SMTP_SECURITY':g" configuration.php
sed -i "s:\$smtpport = '.*':\$smtpport = '$TCHOOZ_MAIL_SMTP_PORT':g" configuration.php
sed -i "s:\$caching = '.*':\$caching = '$TCHOOZ_CACHING':g" configuration.php
sed -i "s:\$cache_handler = '.*':\$cache_handler = '$TCHOOZ_CACHE_HANDLER':g" configuration.php
sed -i "s:\$cachetime = '.*':\$cachetime = '$TCHOOZ_CACHE_LIFETIME':g" configuration.php
sed -i "s:\$MetaDesc = '.*':\$MetaDesc = '$TCHOOZ_META_DESCRIPTION':g" configuration.php
sed -i "s:\$MetaKeys = '.*':\$MetaKeys = '$TCHOOZ_META_KEYS':g" configuration.php
sed -i "s:\$log_path = '.*':\$log_path = '$TCHOOZ_LOG_PATH':g" configuration.php
sed -i "s:\$tmp_path = '.*':\$tmp_path = '$TCHOOZ_TMP_PATH':g" configuration.php
sed -i "s:\$lifetime = '.*':\$lifetime = '$TCHOOZ_SESSION_LIFETIME':g" configuration.php
sed -i "s:\$session_handler = '.*':\$session_handler = '$TCHOOZ_SESSION_HANDLER':g" configuration.php
sed -i "s:\$force_ssl = '.*':\$force_ssl = '$TCHOOZ_FORCE_SSL':g" configuration.php
sed -i "s:\$redis_persist = '.*':\$redis_persist = '$TCHOOZ_REDIS_PERSIST':g" configuration.php
sed -i "s:\$redis_server_host = '.*':\$redis_server_host = '$TCHOOZ_REDIS_HOST':g" configuration.php
sed -i "s:\$redis_server_port = '.*':\$redis_server_port = '$TCHOOZ_REDIS_PORT':g" configuration.php
sed -i "s:\$redis_server_db = '.*':\$redis_server_db = '$TCHOOZ_REDIS_DB':g" configuration.php
sed -i "s:\$replyto = '.*':\$replyto = '$TCHOOZ_MAIL_REPLY_TO':g" configuration.php
sed -i "s:\$replytoname = '.*':\$replytoname = '$TCHOOZ_MAIL_REPLY_TO_NAME':g" configuration.php
sed -i "s:\$session_redis_persist = '.*':\$session_redis_persist = '$TCHOOZ_SESSION_REDIS_PERSIST':g" configuration.php
sed -i "s:\$session_redis_server_host = '.*':\$session_redis_server_host = '$TCHOOZ_SESSION_REDIS_HOST':g" configuration.php
sed -i "s:\$session_redis_server_port = '.*':\$session_redis_server_port = '$TCHOOZ_SESSION_REDIS_PORT':g" configuration.php
sed -i "s:\$session_redis_server_db = '.*':\$session_redis_server_db = '$TCHOOZ_SESSION_REDIS_DB':g" configuration.php
sed -i "s:\$prospect = '.*':\$prospect = '$TCHOOZ_PROSPECT':g" configuration.php
sed -i "s:\$plan_limit_app_forms = '.*':\$plan_limit_app_forms = '$TCHOOZ_PLAN_LIMIT_APP_FORMS':g" configuration.php
sed -i "s:\$plan_limit_storage_space = '.*':\$plan_limit_storage_space = '$TCHOOZ_PLAN_LIMIT_STORAGE_SPACE':g" configuration.php
sed -i "s:\$plan_limit_forms = '.*':\$plan_limit_forms = '$TCHOOZ_PLAN_LIMIT_FORMS':g" configuration.php
sed -i "s:\$behind_loadbalancer = '.*':\$behind_loadbalancer = '$TCHOOZ_BEHIND_LOADBALANCER':g" configuration.php

# .htaccess
sed -i "s:\RewriteCond \%{QUERY_STRING} \!\^.*\$:\RewriteCond \%{QUERY_STRING} \!\^$TCHOOZ_ADMIN_ACCESS_TOKEN\$:g" .htaccess

date > /scripts/init-done
fi

# fabrik connection
if [ ! -f /scripts/fabrik-done ] ; then

mysql --user=$TCHOOZ_DB_USER --password=$TCHOOZ_DB_PASSWORD --database=$TCHOOZ_DB_NAME --host=$TCHOOZ_DB_HOST <<EOF
UPDATE jos_fabrik_connections SET jos_fabrik_connections.host='$TCHOOZ_DB_HOST';
UPDATE jos_fabrik_connections SET jos_fabrik_connections.user='$TCHOOZ_DB_USER' WHERE id='1';
UPDATE jos_fabrik_connections SET jos_fabrik_connections.password='$TCHOOZ_DB_PASSWORD' WHERE id='1';
UPDATE jos_fabrik_connections SET jos_fabrik_connections.database='$TCHOOZ_DB_NAME' WHERE id='1';
UPDATE jos_fabrik_connections SET params= '{\"encryptedPw\":false}' WHERE id='1';
EOF

date > /scripts/fabrik-done
fi

# init coordinator
if [ ! -f /scripts/coordinator-done ] ; then

mysql --user=$TCHOOZ_DB_USER --password=$TCHOOZ_DB_PASSWORD --database=$TCHOOZ_DB_NAME --host=$TCHOOZ_DB_HOST <<EOF
UPDATE jos_users SET jos_users.name = '$TCHOOZ_COORD_FIRST_NAME $TCHOOZ_COORD_LAST_NAME', jos_users.username = '$TCHOOZ_COORD_USERNAME', jos_users.email = '$TCHOOZ_COORD_MAIL', jos_users.password = MD5("$TCHOOZ_COORD_PASSWORD") WHERE jos_users.id = 95;
UPDATE jos_emundus_users SET jos_emundus_users.firstname = '$TCHOOZ_COORD_FIRST_NAME', jos_emundus_users.lastname = '$TCHOOZ_COORD_LAST_NAME' WHERE jos_emundus_users.id = 95;
EOF

date > /scripts/coordinator-done
fi

# Custom update script
php cli/update_cli.php -av

exec "$@"
