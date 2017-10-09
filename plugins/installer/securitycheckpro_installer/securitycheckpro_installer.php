<?php
/**
 * @package        Joomla
 * @subpackage     Securitycheck Pro
 * @author         Jose A. Luque
 * @copyright      Copyright (C) 2012 - 2017 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

class plgInstallerSecuritycheckPro_Installer extends JPlugin
{
	public function onInstallerBeforePackageDownload(&$url, &$headers)
	{
		
		// Chequeamos si la url de actualización es la de Securitycheck, ya que este plugin se lanza en todas las actualizaciones	
		$uri = JUri::getInstance($url);
		
		$host       = $uri->getHost();
		$validHosts = array('protegetuordenador.com', 'securitycheck.protegetuordenador.com');
		if (!in_array($host, $validHosts))
		{
			return true;
		}
		
		// Get Download ID and append it to the URL
		
		// fetch download id from extension parameters, or
		// wherever you want to store them
		// Get the component information from the #__extensions table
		JLoader::import('joomla.application.component.helper');
		$component = JComponentHelper::getComponent("com_securitycheckpro");
		 
		// assuming the download id provided by user is stored in component params
		// under the "update_credentials_download_id" key
		$downloadId = $component->params->get('downloadid', '');
				 
		// bind credentials to request by appending it to the download url
		if (!empty($downloadId)) {
			$separator = strpos($url, '?') !== false ? '&' : '?';
			$url .= $separator . 'dlid=' . $downloadId;
		}
		
		return true;

	}
}
