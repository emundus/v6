<?php
/**
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');
?>
<script language="javascript" type="text/javascript">
// Disable right-click
var isNS = (navigator.appName == "Netscape") ? 1 : 0;
if(navigator.appName == "Netscape") document.captureEvents(Event.MOUSEDOWN||Event.MOUSEUP);
function mischandler(){
 return false;
}
function mousehandler(e){
	var myevent = (isNS) ? e : event;
	var eventbutton = (isNS) ? myevent.which : myevent.button;
  if((eventbutton==2)||(eventbutton==3)) return false;
}
document.oncontextmenu = mischandler;
document.onmousedown = mousehandler;
document.onmouseup = mousehandler;

// Disable CTRL-C, CTRL-V
function onKeyDown() {
	return false;
}

document.onkeydown = onKeyDown;
</script>
<?php

// Importamos las clases necesarias
jimport('joomla.filesystem.file');
jimport( 'joomla.application.component.helper' );

// Obtenemos la ruta alfichero de logs, que vendrá marcada por la entrada 'log_path' del fichero 'configuration.php'
$app = JFactory::getApplication();
$logName = $app->getCfg('log_path');
$logName = $logName . DIRECTORY_SEPARATOR ."change_permissions.log.php";

@ob_end_clean();

if(!JFile::exists($logName))
{
	// El fichero no existe
	echo '<p>'.JText::_('COM_SECURITYCHECKPRO_LOG_ERROR_LOGFILENOTEXISTS').'</p>';
	return;
}
else
{
	// Abrimos el fichero
	$fp = fopen( $logName, "rt" );
	if ($fp === FALSE)
	{
		// El fichero no se puede leer
		echo '<p>'.JText::_('COM_SECURITYCHECKPRO_LOG_ERROR_UNREADABLE').'</p>';
		return;
	}

	while( !feof($fp) ) {
		// Indica si la línea del log tiene un formato válido, ya que en el fichero de logs existen líneas que no son propias de los logs, como la cabecera php 
		$valid = true;
		$line = fgets( $fp );
		if(!$line) return;
		$exploded = explode( "|", $line, 3 );
		if ( count($exploded)>1 ) {  // Se han devuelto datos; los chequeamos para ver si son válidos
			unset( $line );
			switch( trim($exploded[1]) )
			{
				case "ERROR":
					$fmtString = "<span style=\"color: red; font-weight: bold;\">[";
					break;
				case "WARNING":
					$fmtString = "<span style=\"color: #D8AD00; font-weight: bold;\">[";
					break;
				case "INFO":
					$fmtString = "<span style=\"color: black;\">[";
					break;
				case "DEBUG":
					$fmtString = "<span style=\"color: #666666; font-size: small;\">[";
					break;
				case "OK":
					$fmtString = "<span style=\"color: 2EB21F; font-weight: bold;\">[";
					break;
				default:
					$valid = false;
					break;
			}
			if ( $valid ) {
				$fmtString .= $exploded[0] . "] " . htmlspecialchars($exploded[2]) . "</span><br/>\n";
				unset( $exploded );
				echo $fmtString;
				unset( $fmtString );
			}
		}
	}
}

@ob_start();