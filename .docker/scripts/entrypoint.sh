#!/bin/bash
set -e

if [ -n "$JOOMLA_DB_PASSWORD_FILE" ] && [ -f "$JOOMLA_DB_PASSWORD_FILE" ]; then
        JOOMLA_DB_PASSWORD=$(cat "$JOOMLA_DB_PASSWORD_FILE")
fi

if [[ "$1" == apache2* ]] || [ "$1" == php-fpm ]; then
        uid="$(id -u)"
        gid="$(id -g)"
        if [ "$uid" = '0' ]; then
                case "$1" in
                apache2*)
                        user="${APACHE_RUN_USER:-www-data}"
                        group="${APACHE_RUN_GROUP:-www-data}"

                        # strip off any '#' symbol ('#1000' is valid syntax for Apache)
                        pound='#'
                        user="${user#$pound}"
                        group="${group#$pound}"

                        # set user if not exist
                        if ! id "$user" &>/dev/null; then
                                # get the user name
                                : "${USER_NAME:=www-data}"
                                # change the user name
                                [[ "$USER_NAME" != "www-data" ]] &&
                                        usermod -l "$USER_NAME" www-data &&
                                        groupmod -n "$USER_NAME" www-data
                                # update the user ID
                                groupmod -o -g "$user" "$USER_NAME"
                                # update the user-group ID
                                usermod -o -u "$group" "$USER_NAME"
                        fi
                        ;;
                *) # php-fpm
                        user='www-data'
                        group='www-data'
                        ;;
                esac
        else
                user="$uid"
                group="$gid"
        fi

        if [ -n "$MYSQL_PORT_3306_TCP" ]; then
                if [ -z "$JOOMLA_DB_HOST" ]; then
                        JOOMLA_DB_HOST='mysql'
                else
                        echo >&2 "warning: both JOOMLA_DB_HOST and MYSQL_PORT_3306_TCP found"
                        echo >&2 "  Connecting to JOOMLA_DB_HOST ($JOOMLA_DB_HOST)"
                        echo >&2 "  instead of the linked mysql container"
                fi
        fi

        if [ -z "$JOOMLA_DB_HOST" ]; then
                echo >&2 "error: missing JOOMLA_DB_HOST and MYSQL_PORT_3306_TCP environment variables"
                echo >&2 "  Did you forget to --link some_mysql_container:mysql or set an external db"
                echo >&2 "  with -e JOOMLA_DB_HOST=hostname:port?"
                exit 1
        fi

        # If the DB user is 'root' then use the MySQL root password env var
        : "${JOOMLA_DB_USER:=root}"
        if [ "$JOOMLA_DB_USER" = 'root' ]; then
                : ${JOOMLA_DB_PASSWORD:=$MYSQL_ENV_MYSQL_ROOT_PASSWORD}
        fi
        : "${JOOMLA_DB_NAME:=joomla}"

        if [ -z "$JOOMLA_DB_PASSWORD" ] && [ "$JOOMLA_DB_PASSWORD_ALLOW_EMPTY" != 'yes' ]; then
                echo >&2 "error: missing required JOOMLA_DB_PASSWORD environment variable"
                echo >&2 "  Did you forget to -e JOOMLA_DB_PASSWORD=... ?"
                echo >&2
                echo >&2 "  (Also of interest might be JOOMLA_DB_USER and JOOMLA_DB_NAME.)"
                exit 1
        fi

        if [ ! -e index.php ] && [ ! -e libraries/src/Version.php ]; then
                # if the directory exists and Joomla doesn't appear to be installed AND the permissions of it are root:root, let's chown it (likely a Docker-created directory)
                if [ "$uid" = '0' ] && [ "$(stat -c '%u:%g' .)" = '0:0' ]; then
                        chown "$user:$group" .
                fi

                echo >&2 "Joomla not found in $PWD - copying now..."
                if [ "$(ls -A)" ]; then
                        echo >&2 "WARNING: $PWD is not empty - press Ctrl+C now if this is an error!"
                        (
                                set -x
                                ls -A
                                sleep 10
                        )
                fi
                # use full commands
                # for clearer intent
                sourceTarArgs=(
                        --create
                        --file -
                        --directory /usr/src/joomla
                        --one-file-system
                        --owner "$user" --group "$group"
                )
                targetTarArgs=(
                        --extract
                        --file -
                )
                if [ "$uid" != '0' ]; then
                        # avoid "tar: .: Cannot utime: Operation not permitted" and "tar: .: Cannot change mode to rwxr-xr-x: Operation not permitted"
                        targetTarArgs+=(--no-overwrite-dir)
                fi

                tar "${sourceTarArgs[@]}" . | tar "${targetTarArgs[@]}"

                if [ ! -e .htaccess ]; then
                        # NOTE: The "Indexes" option is disabled in the php:apache base image so remove it as we enable .htaccess
                        sed -r 's/^(Options -Indexes.*)$/#\1/' htaccess.txt >.htaccess
                        chown "$user":"$group" .htaccess
                fi

                echo >&2 "Complete! Joomla has been successfully copied to $PWD"
                #TODO: In 4.3 we can instanciate Joomla with CLI
                # php installation/joomla.php
                cp installation/configuration.php-dist configuration.php

                # configuration.php
#                sed -i "s:\$offline = '.*':\$offline = '$TCHOOZ_OFFLINE':g" configuration.php
#                sed -i "s:\$offline_message = '.*':\$offline_message = '$TCHOOZ_OFFLINE_MESSAGE':g" configuration.php
#                sed -i "s:\$display_offline_message = '.*':\$display_offline_message = '$TCHOOZ_DISPLAY_OFFLINE_MESSAGE':g" configuration.php
#                sed -i "s:\$offline_image = '.*':\$offline_image = '$TCHOOZ_OFFLINE_IMAGE':g" configuration.php
#                sed -i "s:\$sitename = '.*':\$sitename = '$TCHOOZ_SITENAME':g" configuration.php
#                sed -i "s:\$editor = '.*':\$editor = '$TCHOOZ_EDITOR':g" configuration.php
#                sed -i "s:\$debug = '.*':\$debug = '$TCHOOZ_DEBUG':g" configuration.php
#                sed -i "s:\$debug_lang = '.*':\$debug_lang = '$TCHOOZ_DEBUG_LANG':g" configuration.php
                sed -i "s:\$host = '.*':\$host = '$JOOMLA_DB_HOST':g" configuration.php
                sed -i "s:\$user = '.*':\$user = '$JOOMLA_DB_USER':g" configuration.php
                sed -i "s:\$password = '.*':\$password = '$JOOMLA_DB_PASSWORD':g" configuration.php
                sed -i "s:\$db = '.*':\$db = '$JOOMLA_DB_NAME':g" configuration.php
#                sed -i "s:\$live_site = '.*':\$live_site = '$TCHOOZ_LIVE_SITE':g" configuration.php
#                sed -i "s:\$secret = '.*':\$secret = '$TCHOOZ_SECRET':g" configuration.php
#                sed -i "s:\$offset = '.*':\$offset = '$TCHOOZ_OFFSET':g" configuration.php
#                sed -i "s:\$mailer = '.*':\$mailer = '$TCHOOZ_MAILER':g" configuration.php
#                sed -i "s:\$mailfrom = '.*':\$mailfrom = '$TCHOOZ_MAIL_FROM':g" configuration.php
#                sed -i "s:\$fromname = '.*':\$fromname = '$TCHOOZ_MAIL_FROM_NAME':g" configuration.php
#                sed -i "s:\$smtpauth = '.*':\$smtpauth = '$TCHOOZ_MAIL_SMTP_AUTH':g" configuration.php
#                sed -i "s:\$smtpuser = '.*':\$smtpuser = '$TCHOOZ_MAIL_SMTP_USER':g" configuration.php
#                sed -i "s:\$smtppass = '.*':\$smtppass = '$TCHOOZ_MAIL_SMTP_PASS':g" configuration.php
#                sed -i "s:\$smtphost = '.*':\$smtphost = '$TCHOOZ_MAIL_SMTP_HOST':g" configuration.php
#                sed -i "s:\$smtpsecure = '.*':\$smtpsecure = '$TCHOOZ_MAIL_SMTP_SECURITY':g" configuration.php
#                sed -i "s:\$smtpport = '.*':\$smtpport = '$TCHOOZ_MAIL_SMTP_PORT':g" configuration.php
#                sed -i "s:\$caching = '.*':\$caching = '$TCHOOZ_CACHING':g" configuration.php
#                sed -i "s:\$cache_handler = '.*':\$cache_handler = '$TCHOOZ_CACHE_HANDLER':g" configuration.php
#                sed -i "s:\$cachetime = '.*':\$cachetime = '$TCHOOZ_CACHE_LIFETIME':g" configuration.php
#                sed -i "s:\$MetaDesc = '.*':\$MetaDesc = '$TCHOOZ_META_DESCRIPTION':g" configuration.php
#                sed -i "s:\$MetaKeys = '.*':\$MetaKeys = '$TCHOOZ_META_KEYS':g" configuration.php
#                sed -i "s:\$log_path = '.*':\$log_path = '$TCHOOZ_LOG_PATH':g" configuration.php
#                sed -i "s:\$tmp_path = '.*':\$tmp_path = '$TCHOOZ_TMP_PATH':g" configuration.php
#                sed -i "s:\$lifetime = '.*':\$lifetime = '$TCHOOZ_SESSION_LIFETIME':g" configuration.php
#                sed -i "s:\$session_handler = '.*':\$session_handler = '$TCHOOZ_SESSION_HANDLER':g" configuration.php
#                sed -i "s:\$force_ssl = '.*':\$force_ssl = '$TCHOOZ_FORCE_SSL':g" configuration.php
#                sed -i "s:\$redis_persist = '.*':\$redis_persist = '$TCHOOZ_REDIS_PERSIST':g" configuration.php
#                sed -i "s:\$redis_server_host = '.*':\$redis_server_host = '$TCHOOZ_REDIS_HOST':g" configuration.php
#                sed -i "s:\$redis_server_port = '.*':\$redis_server_port = '$TCHOOZ_REDIS_PORT':g" configuration.php
#                sed -i "s:\$redis_server_db = '.*':\$redis_server_db = '$TCHOOZ_REDIS_DB':g" configuration.php
#                sed -i "s:\$replyto = '.*':\$replyto = '$TCHOOZ_MAIL_REPLY_TO':g" configuration.php
#                sed -i "s:\$replytoname = '.*':\$replytoname = '$TCHOOZ_MAIL_REPLY_TO_NAME':g" configuration.php
#                sed -i "s:\$session_redis_persist = '.*':\$session_redis_persist = '$TCHOOZ_SESSION_REDIS_PERSIST':g" configuration.php
#                sed -i "s:\$session_redis_server_host = '.*':\$session_redis_server_host = '$TCHOOZ_SESSION_REDIS_HOST':g" configuration.php
#                sed -i "s:\$session_redis_server_port = '.*':\$session_redis_server_port = '$TCHOOZ_SESSION_REDIS_PORT':g" configuration.php
#                sed -i "s:\$session_redis_server_db = '.*':\$session_redis_server_db = '$TCHOOZ_SESSION_REDIS_DB':g" configuration.php
#                sed -i "s:\$prospect = '.*':\$prospect = '$TCHOOZ_PROSPECT':g" configuration.php
#                sed -i "s:\$plan_limit_app_forms = '.*':\$plan_limit_app_forms = '$TCHOOZ_PLAN_LIMIT_APP_FORMS':g" configuration.php
#                sed -i "s:\$plan_limit_storage_space = '.*':\$plan_limit_storage_space = '$TCHOOZ_PLAN_LIMIT_STORAGE_SPACE':g" configuration.php
#                sed -i "s:\$plan_limit_forms = '.*':\$plan_limit_forms = '$TCHOOZ_PLAN_LIMIT_FORMS':g" configuration.php
#                sed -i "s:\$behind_loadbalancer = '.*':\$behind_loadbalancer = '$TCHOOZ_BEHIND_LOADBALANCER':g" configuration.php

                #TODO: Adding php script file to run sql files by replacing prefix
                php /initdb.php "$JOOMLA_DB_HOST" "$JOOMLA_DB_USER" "$JOOMLA_DB_PASSWORD" "$JOOMLA_DB_NAME" "$JOOMLA_DB_PREFIX" "$TCHOOZ_COORD_USERNAME" "$TCHOOZ_COORD_MAIL" "$TCHOOZ_COORD_FIRST_NAME" "$TCHOOZ_COORD_LAST_NAME" "$TCHOOZ_SYSADMIN_PASSWORD" "$TCHOOZ_SYSADMIN_USERNAME" "$TCHOOZ_SYSADMIN_MAIL" "$TCHOOZ_SYSADMIN_FIRST_NAME" "$TCHOOZ_SYSADMIN_LAST_NAME" "$TCHOOZ_SYSADMIN_PASSWORD" "$TCHOOZ_COORD_PASSWORD"

                rm -rf installation/
        else
           php cli/joomla.php extension:list
        fi

        # Ensure the MySQL Database is created
        php /makedb.php "$JOOMLA_DB_HOST" "$JOOMLA_DB_USER" "$JOOMLA_DB_PASSWORD" "$JOOMLA_DB_NAME"

        echo >&2 "========================================================================"
        echo >&2
        echo >&2 "This server is now configured to run Joomla!"
        echo >&2
        echo >&2 "NOTE: You will need your database server address, database name,"
        echo >&2 "and database user credentials to install Joomla."
        echo >&2
        echo >&2 "========================================================================"
fi

exec "$@"