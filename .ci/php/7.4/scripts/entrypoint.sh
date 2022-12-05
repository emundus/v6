#!/bin/bash

if [ ! -f /scripts/init-done ] ; then

# configuration.php
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

# init securitycheckpro
if [ ! -f /scripts/securitycheckpro-done ] ; then

mysql --user=$TCHOOZ_DB_USER --password=$TCHOOZ_DB_PASSWORD --database=$TCHOOZ_DB_NAME --host=$TCHOOZ_DB_HOST <<EOF
UPDATE jos_securitycheckpro_storage t SET t.storage_value = '{"check_header_referer":"0","duplicate_backslashes_exceptions":"*","line_comments_exceptions":"*","using_integers_exceptions":"*","escape_strings_exceptions":"*","email_active":"1","email_subject":"SCP alert! | iID : $TCHOOZ_INSTANCE_ID","email_body":"Securitycheck Pro has generated a new alert. Please, check your logs.","email_to":"admin@emundus.fr","email_from_domain":"$TCHOOZ_MAIL_FROM","email_from_name":"SecurityCheck | Site : $TCHOOZ_SITENAME cID : $TCHOOZ_CUSTOMER_ID  iID : $TCHOOZ_INSTANCE_ID","email_add_applied_rule":"1","email_max_number":"20","priority1":"Geoblock","priority2":"Whitelist","priority3":"DynamicBlacklist","priority4":"Blacklist","dynamic_blacklist":"1","dynamic_blacklist_time":"600","dynamic_blacklist_counter":"5","blacklist_email":"1","write_log_inspector":"1","action_inspector":"2","send_email_inspector":"1","inspector_forbidden_words":"wp-login.php,.git,owl.prev,tmp.php,home.php,Guestbook.php,aska.cgi,default.asp,jax_guestbook.php,bbs.cg,gastenboek.php,light.cgi,yybbs.cgi,wsdl.php,wp-content,cache_aqbmkwwx.php,.suspected,seo-joy.cgi,google-assist.php,wp-main.php,sql_dump.php,xmlsrpc.php","session_protection_active":"0","session_hijack_protection":"0","session_protection_groups":["8"],"track_failed_logins":"0","logins_to_monitorize":"2","write_log":"1","include_password_in_log":"0","actions_failed_login":"0","email_on_admin_login":"1","forbid_admin_frontend_login":"0","forbid_new_admins":"0","upload_scanner_enabled":"1","check_multiple_extensions":"1","extensions_blacklist":"php,js,exe,xml","delete_files":"1","actions_upload_scanner":"1","exclude_exceptions_if_vulnerable":"1","check_base_64":"1","base64_exceptions":"com_hikashop,com_emundus,com_fabrik","strip_all_tags":"1","tags_to_filter":"applet,body,bgsound,base,basefont,embed,frame,frameset,head,html,id,iframe,ilayer,layer,link,meta,name,object,script,style,title,xml","strip_tags_exceptions":"com_jdownloads,com_hikashop,com_phocaguestbook,com_emundus,com_fabrik","sql_pattern_exceptions":"","if_statement_exceptions":"","lfi_exceptions":"com_emundus,com_fabrik","second_level_exceptions":"com_emundus,com_fabrik","blacklist":"69.163.169.133,192.99.4.63","whitelist":"92.154.69.34","methods":"GET,POST,REQUEST","mode":"1","logs_attacks":"1","scp_delete_period":"60","log_limits_per_ip_and_day":"0","redirect_after_attack":"1","redirect_options":"1","redirect_url":"","custom_code":"<p>The webmaster has forbidden your access to this site<\\/p>","second_level":"1","second_level_redirect":"1","second_level_limit_words":"3","second_level_words":"ZHJvcCx1cGRhdGUsc2V0LGFkbWluLHNlbGVjdCx1c2VyLHBhc3N3b3JkLGNvbmNhdCxsb2dpbixsb2FkX2ZpbGUsYXNjaWksY2hhcix1bmlvbixmcm9tLGdyb3VwIGJ5LG9yZGVyIGJ5LGluc2VydCx2YWx1ZXMscGFzcyx3aGVyZSxzdWJzdHJpbmcsYmVuY2htYXJrLG1kNSxzaGExLHNjaGVtYSx2ZXJzaW9uLHJvd19jb3VudCxjb21wcmVzcyxlbmNvZGUsaW5mb3JtYXRpb25fc2NoZW1hLHNjcmlwdCxqYXZhc2NyaXB0LGltZyxzcmMsaW5wdXQsYm9keSxpZnJhbWUsZnJhbWUsJF9QT1NULGV2YWwsJF9SRVFVRVNULGJhc2U2NF9kZWNvZGUsZ3ppbmZsYXRlLGd6dW5jb21wcmVzcyxnemluZmxhdGUsc3RydHJleGVjLHBhc3N0aHJ1LHNoZWxsX2V4ZWMsY3JlYXRlRWxlbWVudA==","tasks":"alternate","launch_time":2,"periodicity":24,"control_center_enabled":"0","secret_key":"","add_geoblock_logs":"0","backend_exceptions":"","add_access_attempts_logs":"0","check_if_user_is_spammer":1,"spammer_action":1,"spammer_write_log":0,"spammer_limit":3,"spammer_what_to_check":["Email","IP","Username"],"delete_period":0,"ip_logging":0,"loggable_extensions":["com_banners","com_cache","com_categories","com_config","com_contact","com_content","com_installer","com_media","com_menus","com_messages","com_modules","com_newsfeeds","com_plugins","com_redirect","com_tags","com_templates","com_users"]}' WHERE t.storage_key LIKE 'pro_plugin';
EOF

date > /scripts/securitycheckpro-done
fi

# update Tchooz database
pushd libraries/emundus && ./update-db.sh $TCHOOZ_DB_HOST && popd

# Custom update script
php cli/update_cli.php -av

if [[ -n "$CI" ]]; then
    echo "this block will only execute in a CI environment"
    exec /bin/bash
else
    echo "Not in CI. Running the image normally"
    exec "$@"
fi
