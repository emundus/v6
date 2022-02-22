<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Miniorange_saml
 * @author     meenakshi <meenakshi@miniorange.com>
 * @copyright  2016 meenakshi
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;
 require_once JPATH_COMPONENT . '/helpers/mo-saml-utility.php';
 require_once JPATH_COMPONENT . '/helpers/mo-saml-customer-setup.php';
 require_once JPATH_COMPONENT . '/helpers/mo_saml_support.php';
 require_once JPATH_COMPONENT . '/helpers/miniorange_saml.php';
 require_once JPATH_COMPONENT . '/helpers/MoConstants.php';

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_miniorange_saml'))
{
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
}

// Include dependancies
jimport('joomla.application.component.controller');

JLoader::registerPrefix('Miniorange_saml', JPATH_COMPONENT_ADMINISTRATOR);

$controller = JControllerLegacy::getInstance('Miniorange_saml');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
