<?php
/*
 * A plugin that sanitize input on all external request
 * Plugin for Joomla 1.5 / 1.6 - Version 1.1.0
 * License: http://www.gnu.org/copyleft/gpl.html
 * Authors: marco maria leoni
 * Copyright (c) 2010 - 2011 marco maria leoni web consulting - http: www.mmleoni.net
 * Project page at http://www.mmleoni.net/sql-iniection-lfi-protection-plugin-for-joomla
 * *** Last update: Mar 9th, 2011 ***
*/
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.plugin.plugin' );
class plgSystemMarcosinterceptor extends JPlugin{
	function plgSystemMarcosinterceptor( &$subject, $config ){
		parent::__construct( $subject, $config );
	}

	function onAfterInitialise(){
		$app = JFactory::getApplication();
		$p_dbprefix = $app->getCfg('dbprefix');
		$p_raiseError = $this->params->get('raiseerror', 1);
		$p_errorCode = intval($this->params->get('errorcode', 500));
		$p_errorMsg = $this->params->get('errormsg', 'Internal Server Error');
		$p_strictLFI = $this->params->get('strictlfi', 1);
		$p_levelLFI = intval($this->params->get('levellfi', 1));
		$p_frontEndOnly = $this->params->get('frontendonly', 1);
		$p_ignoredExts = $this->params->get('ignoredexts','');
		$p_sendNotification = $this->params->get('sendnotification',0);
		$p_nameSpaces = $this->params->get('namespaces','GET,POST');
		
		$p_ipBlock  = $this->params->get('ipblock', 0);
		$p_ipBlockTime  = intval($this->params->get('ipblocktime', 300));
		$p_ipBlockCount  = intval($this->params->get('ipblockcount', 3));
		$p_ipBlockCount = ($p_ipBlockCount < 1 ? 1 : $p_ipBlockCount);
		$remoteIP = ip2long($_SERVER['REMOTE_ADDR']);
		
		if($p_ipBlock){
			$db = JFactory::getDBO();
			$sql = "DELETE FROM `#__mi_iptable` WHERE DATE_ADD(`lasthacktime`, INTERVAL {$p_ipBlockTime} SECOND) < NOW() AND `autodelete`=1;";
			$db->setQuery( $sql );
			
			if (!$db->query()) {
				/* Create table: the dirty way */
				if ( $db->getErrorNum() == 1146 ) {
					$sql = 'CREATE TABLE `#__mi_iptable` ( ';
					$sql .= '`ip` BIGINT NOT NULL COMMENT \'ip to long\', ';
					$sql .= '`firsthacktime` DATETIME NOT NULL , ';
					$sql .= '`lasthacktime` DATETIME NOT NULL , ';
					$sql .= '`hackcount` INT NOT NULL DEFAULT \'1\', ';
					$sql .= '`autodelete` TINYINT NOT NULL DEFAULT \'1\', ';
					$sql .= 'PRIMARY KEY ( `ip` ) ';
					$sql .= ') ENGINE = MYISAM ; ';
					$db->setQuery( $sql );
					if(!$db->query()){
						JError::raiseError(500, 'CAN\'T CREATE IPTABLE: '.$db->getErrorMsg() );
					}
				}else{
					JError::raiseError(500, $db->getErrorMsg() );
				}
			}
			
			$sql = "SELECT COUNT(*) from `#__mi_iptable` WHERE ip = {$remoteIP} AND `hackcount` >= {$p_ipBlockCount}" ;
			$db->setQuery( $sql );
			$db->query( $sql );
			if($db->loadResult()){
				// unceremoniously shut down connection
				ob_end_clean();
				header('HTTP/1.0 403 Forbidden');
				header('Status: 403 Forbidden');
				header('Content-Length: 0',true);
				header('Connection: Close');
				exit;
			}
		
		}
		
		if (($p_frontEndOnly) AND (strpos($_SERVER['REQUEST_URI'], '/administrator') === 0)) return;
		
		$p_ignoredExts = explode(',', preg_replace('/\s*/', '', $p_ignoredExts));
		if (isset($_REQUEST['option']) AND in_array($_REQUEST['option'], $p_ignoredExts)) return;

		$wr=array();
		foreach(explode(',', $p_nameSpaces) as $nsp){
			switch ($nsp){
				case 'GET':
					$nameSpace = $_GET;
					break;
				case 'POST':
					$nameSpace = $_POST;
					break;
				case 'COOKIE':
					$nameSpace = $_COOKIE;
					break;
				case 'REQUEST':
					$nameSpace = $_REQUEST;
					break;
			}
			foreach($nameSpace as $k => &$v){
			
				if(is_numeric($v)) continue;
				if(is_array($v)) continue;
			
				/* SQL injection */
				// strip /* comments */
				$a = preg_replace('!/\*.*?\*/!s', ' ', $v); 
				/* union select ... jos_users */
				if (preg_match('/UNION(?:\s+ALL)?\s+SELECT/i', $a)){
					$wr[] = "** Union Select [$nsp:$k] => $v"; 
					if(!$p_raiseError){
						$v = preg_replace('/UNION(?:\s+ALL)?\s+SELECT/i', '--', $a);
					}
				}

				/* table name */
				//$ta = array ('/\s`?+(#__)/', '/\s+`?(jos_)/i', "/\s+`?({$p_dbprefix}_)/i");
				$ta = array ('/(\s+|\.|,)`?(#__)/', '/(\s+|\.|,)`?(jos_)/i', "/(\s+|\.|,)`?({$p_dbprefix}_)/i");
				foreach ($ta as $t){
					if (preg_match($t, $v)){
						$wr[] = "** Table name in url [$nsp:$k] => $v";
						if(!$p_raiseError){
							$v = preg_replace($t, ' --$1', $v);
						}
					}
				}
				
				/* LFI */
				if ($p_strictLFI){
					if (!in_array($k, array('controller', 'view', 'model', 'template'))) continue;
				}
				$recurse = str_repeat('\.\.\/', $p_levelLFI+1);
				$i=0;
				while (preg_match("/$recurse/", $v)){
					if(!$i) $wr[] = "** Local File Inclusion [$nsp:$k] => $v";
					if(!$p_raiseError){
						$v = preg_replace('/\.\.\//', '', $v);
					}else{
						break;
					}
					$i++;
				}
				unset($v);
			} // namespace
		} //namespaces

		
		if(($p_ipBlock) AND ($wr)){
			$db = JFactory::getDBO();
			$sql = "INSERT INTO `#__mi_iptable` (`ip`, `firsthacktime`, `lasthacktime` ) VALUES ({$remoteIP}, NOW(), NOW()) ON DUPLICATE KEY UPDATE `lasthacktime` = NOW(), `hackcount` = `hackcount` + 1;";
			$db->setQuery( $sql );
			$db->query( $sql );
		}
		
		if(($p_sendNotification) AND ($wr)) $this->sendNotification($wr);
		if(($p_raiseError) AND ($wr)){
			JError::raiseError($p_errorCode, $p_errorMsg);
		}
		

	}
	function sendNotification($warnings){
		$app = JFactory::getApplication();
		$p_sendTo = $this->params->get('sendto','');
		if(!$p_sendTo) $p_sendTo = $app->getCfg('mailfrom');
		
		$warning = implode("\r\n", $warnings);
		$warning .= "\r\n\r\n";

		$warning .= "**PAGE / SERVER INFO\r\n";
		$warning .= "\r\n\r\n";
		foreach(explode(',', 'REMOTE_ADDR,HTTP_USER_AGENT,REQUEST_METHOD,QUERY_STRING,HTTP_REFERER') as $sg){
			if(!isset($_SERVER[$sg])) continue;
			$warning .= "*{$sg} :\r\n{$_SERVER[$sg]}\r\n\r\n";
		}
		$warning .= "\r\n\r\n";
		
		$warning .= "** SUPERGLOBALS DUMP (sanitized)\r\n";
		
		$warning .= "\r\n\r\n";
		$warning .= '*$_GET DUMP';
		$warning .= "\r\n";
		foreach($_GET as $k => $v){
			$warning .= " -[$k] => $v\r\n";
		}

		$warning .= "\r\n\r\n";
		$warning .= '*$_POST DUMP';
		$warning .= "\r\n";
		foreach($_POST as $k => $v){
			$warning .= " -[$k] => $v\r\n";
		}

		$warning .= "\r\n\r\n";
		$warning .= '*$_COOKIE DUMP';
		$warning .= "\r\n";
		foreach($_COOKIE as $k => $v){
			$warning .= " -[$k] => $v\r\n";
		}

		$warning .= "\r\n\r\n";
		$warning .= '*$_REQUEST DUMP';
		$warning .= "\r\n";
		foreach($_REQUEST as $k => $v){
			$warning .= " -[$k] => $v\r\n";
		}
		
		/*jimport('joomla.mail.mail');
		$mail = new JMail();
		$mail->setsender($app->getCfg('mailfrom'));
		$mail->addRecipient($p_sendTo);
		$mail->setSubject($app->getCfg('sitename') . ' Marco\'s interceptor warning ' );
		$mail->setbody($warning);
		$mail->send();*/
		JUtility::sendMail($app->getCfg('mailfrom'), $obj[0]->name, $p_sendTo, ' HACKING ATTEMPT - '.$app->getCfg('sitename'), $warning, 0);
	}
}