<?php
/* Args:
0 => makedb.php
1 => "$JOOMLA_DB_HOST"
2 => "$JOOMLA_DB_USER"
3 => "$JOOMLA_DB_PASSWORD"
4 => "$JOOMLA_DB_NAME"
5 => "$JOOMLA_DB_PREFIX"
6 => "$TCHOOZ_COORD_USERNAME"
7 => "$TCHOOZ_COORD_MAIL"
8 => "$TCHOOZ_COORD_FIRST_NAME"
9 => "$TCHOOZ_COORD_LAST_NAME"
10 => "$TCHOOZ_SYSADMIN_PASSWORD"
11 => "$TCHOOZ_SYSADMIN_USERNAME"
12 => "$TCHOOZ_SYSADMIN_MAIL"
13 => "$TCHOOZ_SYSADMIN_FIRST_NAME"
14 => "$TCHOOZ_SYSADMIN_LAST_NAME"
15 => "$TCHOOZ_SYSADMIN_PASSWORD"
16 => "$TCHOOZ_COORD_PASSWORD"
*/
$stderr = fopen('php://stderr', 'w');
fwrite($stderr, "\nEnsuring Joomla database is present\n");

if (strpos($argv[1], ':') !== false)
{
    list($host, $port) = explode(':', $argv[1], 2);
}
else
{
    $host = $argv[1];
    $port = 3306;
}

$maxTries = 10;

// set original default behaviour for PHP 8.1 and higher
// see https://www.php.net/manual/en/mysqli-driver.report-mode.php
mysqli_report(MYSQLI_REPORT_OFF);
do
{
    $mysql = new mysqli($host, $argv[2], $argv[3], $argv[4], (int) $port);

    if ($mysql->connect_error)
    {
        fwrite($stderr, "\nMySQL Connection Error: ({$mysql->connect_errno}) {$mysql->connect_error}\n");
        --$maxTries;

        if ($maxTries <= 0)
        {
            exit(1);
        }

        sleep(3);
    }
}
while ($mysql->connect_error);

$commands_1 = file_get_contents('installation/sql/mysql/base.sql');
$commands_2 = file_get_contents('installation/sql/mysql/extensions.sql');
$commands_3 = file_get_contents('installation/sql/mysql/supports.sql');
$commands = $commands_1 . "\n" . $commands_2 . "\n" . $commands_3;
$commands = str_replace('#__', 'jos_', $commands);
$commands = str_replace('0=auto delete; 1=keep', '0=auto delete, 1=keep', $commands);

//delete comments
$lines = explode("\n",$commands);
$commands = '';
foreach($lines as $line){
    $line = trim($line);
    if($line){
        $length = strlen('--');
        $starts_with = (substr($line, 0, $length) === '--');
        if(!$starts_with) {
            $commands .= $line . "\n";
        }
    }
}
//convert to array
$commands = explode(";", $commands);

//run commands
$total = $success = 0;
foreach($commands as $command){
    if(trim($command)){
        if($mysql->query($command . ';')){
            $success += 1;
            $total += 1;
        } else {
            fwrite($stderr, "\nMySQL Error: " . $mysql->error . 'in command ' . $command . "\n");
            $mysql->close();
            exit(1);
        }

    }
}

$query_coord = 'INSERT INTO jos_users (name, username, email, password, block, sendEmail, registerDate, lastvisitDate, activation, params, lastResetTime, resetCount, otpKey, otep, requireReset, authProvider) VALUES ("' . $argv[9] . " " . $argv[8] .'","' . $argv[6] . '","' . $argv[7] . '", "' . md5($argv[16]) . '", 0, " ", "' . date("Y-m-d H:i:s") . '", null, "1", "{}", null, 0, " ", " ", 0, " ")';
$coordinator = $mysql->query($query_coord);
if(!$coordinator){
    fwrite($stderr, "\nMySQL Error: " . $mysql->error . 'in command ' . $query_coord . "\n");
    $mysql->close();
    exit(1);
}
$query_sysadmin = 'INSERT INTO jos_users (name, username, email, password, block, sendEmail, registerDate, lastvisitDate, activation, params, lastResetTime, resetCount, otpKey, otep, requireReset, authProvider) VALUES ("' . $argv[14] . " " . $argv[13] .'","' . $argv[11] . '", "' . $argv[12] . '", "' . md5($argv[15]) . '", 0, " ", "' . date("Y-m-d H:i:s") . '", null, "1", "{}", null, 0, " ", " ", 0, " ")';
$sysadmin = $mysql->query($query_sysadmin);
if(!$sysadmin){
    fwrite($stderr, "\nMySQL Error: " . $mysql->error . 'in command ' . $query_sysadmin . "\n");
    $mysql->close();
    exit(1);
}

fwrite($stderr, "\nMySQL Database Filled\n");

$mysql->close();