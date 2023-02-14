<?php
/**
 * @package         SCLogin
 * @copyright (c)   2009-2021 by SourceCoast - All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version         Release v9.0.215
 * @build-date      2022/09/06
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormField;

jimport('joomla.form.helper');

$factoryPath = JPATH_SITE . '/components/com_jfbconnect/libraries/factory.php';
if (File::exists($factoryPath))
    require_once($factoryPath);

if (class_exists('JFBCFactory')) {
    if (method_exists('JFBCFactory', 'initializeJoomla'))
        JFBCFactory::initializeJoomla();
    else {
        jimport('sourcecoast.utilities'); // Call no longer necessary in v6.5, but left for compatibility with older JFBC versions
    }
    $jlang = Factory::getLanguage();
    $jlang->load('com_jfbconnect', JPATH_ADMINISTRATOR, 'en-GB', true); // Load English (British)
    $jlang->load('com_jfbconnect', JPATH_ADMINISTRATOR, null, true); // Load the currently selected language

    class JFormFieldJFBConnectSettings extends FormField
    {
        public function getInput()
        {
            $form = Form::getInstance('mod_sclogin.jfbconnectsettings', JPATH_SITE . '/modules/mod_sclogin/fields/jfbconnectsettings.xml', array('control' => 'jform'));
            // J3.2+ compatible way. Need lengthier code for J2.5
            //$form->bind($this->form->getData());
            $params = $this->form->getValue('params');
            $p['params'] = $params;
            $form->bind($p);

            foreach ($form->getFieldsets() as $fiedsets => $fieldset) {
                if (version_compare(JVERSION, '3.2.3', '<='))
                    $html[] = '<ul class="adminformlist">';
                foreach ($form->getFieldset($fieldset->name) as $field) {
                    if (version_compare(JVERSION, '3.2.3', '<=')) {
                        $label = $field->getLabel();
                        $input = $field->getInput();
                        $html[] = '<li>' . $label . $input . '</li>';
                    } else
                        $html[] = $field->renderField();
                }
                if (version_compare(JVERSION, '3.2.3', '<='))
                    $html[] = '</ul>';
            }

            return implode('', $html);
        }

        public function getLabel()
        {
            return "";
        }
    }
} else {
    class JFormFieldJFBConnectSettings extends JFormFieldList
    {
        public function getInput()
        {
            Factory::getDocument()->addStyleDeclaration(
                '.jfbcButtonImg {margin-bottom:10px;}
                    .jfbcLearnMore {clear:left;margin-top:30px;}
                    .jfbcLearnMore a {color:#FFFFFF;}
                    .jfbc-btn-buynow{background-color:#F79C4B; padding:16px 20px; font-size:14px;text-decoration:none;border-radius:5px}
                    .jfbc-btn-buynow:hover{background-color:rgba(247,130,60,0.6);text-decoration:none;border-radius:5px}
                ');

            $jfbcNotDetected = '<h3>' . Text::_('MOD_SCLOGIN_SOCIAL_JFBC_NOT_DETECTED') . '</h3>';
            $jfbcInstructions = '<div style="clear:left"><p>' . Text::_('MOD_SCLOGIN_SOCIAL_JFBC_LEARN_MORE1') . '</p><p>' . Text::_('MOD_SCLOGIN_SOCIAL_JFBC_LEARN_MORE2') . '</p></div>';
            $loginImage = '<div class="jfbcButtonImg"><img src="' . Uri::root() . 'modules/mod_sclogin/fields/images/socialloginbuttons.png' . '"/></div>';
            $buyNow = '<div class="jfbcLearnMore"><a class="jfbc-btn-buynow" href="https://www.sourcecoast.com/l/jfbconnect-for-sclogin" target="_blank">' . Text::_('MOD_SCLOGIN_SOCIAL_JFBC_LEARN_MORE') . '</a></div>';

            return $jfbcNotDetected . $loginImage . $jfbcInstructions . $buyNow;
        }

        public function getLabel()
        {
            return "";
        }
    }
}