<?php
/**
 * Is Alpha Numeric Validation Rule
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.validationrule.isalphanumeric
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/validation_rule.php';

/**
 * Is Alpha Numeric Validation Rule
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.validationrule.isalphanumeric
 * @since       3.0
 */
class PlgFabrik_ValidationruleIslatin extends PlgFabrik_Validationrule
{
    /**
     * Plugin name
     *
     * @var string
     */
    protected $pluginName = 'islatin';

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
        if ($data == '')
        {
            return false;
        }

        // Not a latin character
        preg_match("/[^A-zÀ-ÖØ-öø-ÿ[:punct:] ]/", $data, $matches);

        return empty($matches) ? true : false;
    }
}
