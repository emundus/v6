<?php
/**
 * Check Password Validation Rule
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.validationrule.checkpassword
 * @copyright   Copyright (C) 2015-2023  eMundus - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin classes
require_once COM_FABRIK_FRONTEND . '/models/validation_rule.php';

/**
 * Is Numeric Validation Rule
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.validationrule.checkpassword
 * @since       3.0
 */
class PlgFabrik_ValidationruleCheckPassword extends PlgFabrik_Validationrule
{
	/**
	 * Plugin name
	 *
	 * @var string
	 */
	protected $pluginName = 'checkpassword';

	/**
	 * Validate the elements data against the rule
	 *
	 * @param   string  $data           To check
	 * @param   int     $repeatCounter  Repeat group counter
	 *
	 * @return  bool  true if validation passes, false if fails
	 */
	public function validate($data, $repeatCounter)
	{
		// Could be a drop-down with multi-values
		if (is_array($data))
		{
			$data = implode('', $data);
		}

		$params = $this->getParams();
		$allow_empty = $params->get('checkpassword-allow_empty',0);

		if ($allow_empty == '1' and empty($data))
		{
			return true;
		}
		elseif (empty($data))
		{
			return false;
		}

		if (!is_null(JPATH_LIBRARIES . '/cms/form/rule'))
		{
			JFormHelper::addRulePath(JPATH_LIBRARIES . '/cms/form/rule');
		}

		$rule = JFormHelper::loadRuleType('password', true);
		$xml  = new SimpleXMLElement('<xml></xml>');
		$this->lang->load('com_users');

		return $rule->test($xml, $data);
	}

	/**
	 * Does the validation allow empty value?
	 * Default is false, can be overridden on per-validation basis (such as isnumeric)
	 *
	 * @return bool
	 */

	protected function allowEmpty()
	{
		$params = $this->getParams();
		$allow_empty = $params->get('checkpassword-allow_empty');

		return $allow_empty == '1';
	}
}
