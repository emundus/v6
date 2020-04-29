<?php
/**
 * @package    Joomla
 * @subpackage Securitycheck Pro
 * @author     Jose A. Luque
 * @copyright Copyright (c) 2013 - Jose A. Luque
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri as JUri;
use Joomla\CMS\Component\ComponentHelper as JComponentHelper;

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
		// Fetch download id from extension parameters, or
		// wherever you want to store them
		// Get the component information from the #__extensions table
		$component = JComponentHelper::getComponent("com_securitycheckpro");

		// Assuming the download id provided by user is stored in component params
		// under the "update_credentials_download_id" key
		$downloadId = $component->params->get('downloadid', '');

		// Bind credentials to request by appending it to the download url
		if (!empty($downloadId))
		{
			$separator = strpos($url, '?') !== false ? '&' : '?';
			$url .= $separator . 'dlid=' . $downloadId;
		}

		return true;
	}
}
