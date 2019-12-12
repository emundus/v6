<?php
/**
 * Sponsorship Reward Rule
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 *
 * @package   Sponsorship Reward
 * @copyright Copyright (C) 2012 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */

// no direct access
defined('_JEXEC') || die;
jimport('joomla.form.formrule');

/**
 * Form Rule class for the Joomla Framework.
 */
class JFormRuleInteger extends JFormRule
{
    /**
     * Method to test the username for uniqueness.
     *
     * @param SimpleXMLElement         $element The JXMLElement object representing the <field /> tag for the
     *                                          form field object.
     * @param mixed                    $value   The form field value to validate.
     * @param string                   $group   The field name group control value. This acts as as an array
     *                                          container for the field. For example if the field has name="foo"
     *                                          and the group value is set to "bar" then the full field name
     *                                          would end up being "bar[foo]".
     * @param Joomla\Registry\Registry $input   An optional JRegistry object with the entire data set to validate
     *                                          against the entire form.
     * @param Joomla\CMS\Form\Form     $form    The form object for which the field is being tested.
     *
     * @return boolean               True if the value is valid, false otherwise.
     * @since  1.6
     * @throws JException On invalid rule.
     */
    public function test(SimpleXMLElement $element, $value, $group = null, Joomla\Registry\Registry $input = null, Joomla\CMS\Form\Form $form = null)
    {
        if ($value === '' || $value === null || $this->isInteger($value)) {
            return true;
        }
        return false;
    }

    /**
     * Check integer
     *
     * @param string|integer $v Value
     *
     * @return boolean
     * @since  version
     */
    private function isInteger($v)
    {
        $i = intval($v);
        if (strval($i) === $v) {
            return true;
        } else {
            return false;
        }
    }
}
