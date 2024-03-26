<?php
/**
 * Not Empty Validation Rule
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.validationrule.notempty
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
use Joomla\CMS\Factory;

defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/validation_rule.php';

/**
 * Not Empty Validation Rule
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.validationrule.notempty
 * @since       3.0
 */
class PlgFabrik_ValidationruleNotempty extends PlgFabrik_Validationrule
{
	/**
	 * Plugin name
	 *
	 * @var string
	 */
	protected $pluginName = 'notempty';

	/**
	 * @param string $data
	 * @param int $repeatCounter
	 *
	 * @return bool
	 *
	 * @since version
	 */
	public function shouldValidate($data, $repeatCounter = 0)
	{
		$formData = $this->formModel->formData;
		$elt_name = $this->elementModel->getElement()->name;

		$shouldValidate = parent::shouldValidate($data, $repeatCounter);

		if($shouldValidate) {
			return $this->checkEmundusCondition($elt_name, $formData, $this->formModel->id, $repeatCounter);
		}
	}

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
		if (method_exists($this->elementModel, 'dataConsideredEmptyForValidation'))
		{
			$ok = $this->elementModel->dataConsideredEmptyForValidation($data, $repeatCounter);
		}
		else
		{
			$ok = $this->elementModel->dataConsideredEmpty($data, $repeatCounter);
		}

		return !$ok;
	}

	private function checkEmundusCondition($elt,$formData,$form_id, $repeatCounter = 0)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select([$db->quoteName('esfrja.parent_id'),$db->quoteName('esfrja.action')])
			->from($db->quoteName('#__emundus_setup_form_rules_js_actions','esfrja'))
			->leftJoin($db->quoteName('#__emundus_setup_form_rules_js_actions_fields','esfrjaf').' ON '.$db->quoteName('esfrjaf.parent_id').' = '.$db->quoteName('esfrja.id'))
			->where($db->quoteName('esfrjaf.fields') . ' = ' . $db->quote($elt))
			->where($db->quoteName('esfrja.action') . ' IN (' . implode(',',$db->quote(['set_optional','set_mandatory'])) . ')');
		$db->setQuery($query);
		$rule = $db->loadObject();

		if(!empty($rule->parent_id))
		{
			$query->clear()
				->select($db->quoteName(['esfrjc.field','esfrjc.state','esfrjc.values','esfr.group']))
				->from($db->quoteName('#__emundus_setup_form_rules_js_conditions','esfrjc'))
				->leftJoin($db->quoteName('#__emundus_setup_form_rules','esfr').' ON '.$db->quoteName('esfr.id').' = '.$db->quoteName('esfrjc.parent_id'))
				->where($db->quoteName('esfrjc.parent_id') . ' = '. $db->quote($rule->parent_id))
				->where($db->quoteName('esfr.form_id') . ' = '. $db->quote($form_id));
			$db->setQuery($query);
			$conditions = $db->loadObjectList();

			$condition_state = [];
			foreach ($conditions as $condition) {
				foreach($formData as $key => $data) {
					if (strpos($key,$condition->field.'_raw')) {
						$value = $data;
						if(strpos($key, 'repeat')) {
							$value = $data[$repeatCounter];
						}

						switch ($condition->state) {
							case '=': // Equal
								if(is_array($value)) {
									$condition_state[] = in_array($condition->values, $value);
								} else {
									$condition_state[] = $value == $condition->values;
								}
								break;
							case '!=': // Not equal
								if(is_array($value)) {
									$condition_state[] = !in_array($condition->values, $value);
								} else {
									$condition_state[] = $value != $condition->values;
								}
								break;
						}
						break;
					}
				}
			}

			if (in_array(false, $condition_state, true)) {
				if($rule->action == 'set_optional') {
					return true;
				} else {
					return false;
				}
			} else {
				if($rule->action == 'set_optional') {
					return false;
				} else {
					return true;
				}
			}
		}

		return true;
	}
}
