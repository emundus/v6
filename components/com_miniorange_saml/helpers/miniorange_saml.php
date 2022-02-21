<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Miniorange_saml
 * @author     meenakshi <meenakshi@miniorange.com>
 * @copyright  2016 meenakshi
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Class Miniorange_samlFrontendHelper
 *
 * @since  1.6
 */
class Miniorange_samlHelpersMiniorange_saml
{
	/**
	 * Get an instance of the named model
	 *
	 * @param   string  $name  Model name
	 *
	 * @return null|object
	 */
	public static function getModel($name)
	{
		$model = null;

		// If the file exists, let's
		if (file_exists(JPATH_SITE . '/components/com_miniorange_saml/models/' . strtolower($name) . '.php'))
		{
			require_once JPATH_SITE . '/components/com_miniorange_saml/models/' . strtolower($name) . '.php';
			$model = JModelLegacy::getInstance($name, 'Miniorange_samlModel');
		}

		return $model;
	}
}
