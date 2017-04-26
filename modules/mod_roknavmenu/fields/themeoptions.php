<?php
/**
 * @version   $Id: themeoptions.php 4597 2012-10-27 04:37:59Z steph $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.filesystem.folder');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JHtml::_('behavior.framework', true);

/**
 * Supports an HTML select list of folder
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldThemeOptions extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	public $type = 'ThemeOptions';

    public function __construct($form = null){
        parent::__construct($form);
    }

    /**
     * Method to get the field input markup.
     *
     * @return    string    The field input markup.
     * @since    1.6
     */
    protected function getLabel()
    {
        $doc =JFactory::getDocument();
        $css="
            .rok-break {border-bottom:1px solid #eee;font-size:16px;color:#0088CC;margin-top:15px;padding:2px 0;width:100%;}
            div.themeset > div.control-label {margin-bottom:18px}
            div.themeset > div.controls {margin-bottom:18px}
         ";
        $doc->addStyleDeclaration($css);

        // Load SubfieldForm Class
        require_once(JPATH_ROOT . "/modules/mod_roknavmenu/lib/RokSubfieldForm.php");

        // Load 2x Catalog Themes
        require_once(JPATH_ROOT . "/modules/mod_roknavmenu/lib/RokNavMenu.php");
        RokNavMenu::loadCatalogs();

        $label = JText::_((string)$this->element['label']);
        $css   = (string)$this->element['class'];

        $buffer = '';
        $form = RokSubfieldForm::getInstanceFromForm($this->form);

        JForm::addFieldPath(dirname(__FILE__) . '/fields');

		$themesets = $form->getSubFieldsets('roknavmenu-themes');

        foreach($themesets as $themeset => $themeset_val)
        {
            $themeset_fields = $form->getSubFieldset('roknavmenu-themes', $themeset, 'params');
            ob_start();
            ?>
                <div class="control-group themeset" id="themeset-<?php echo $themeset;?>">
                    <?php foreach ($themeset_fields as $themeset_field): ?>
                <div class="control-label">
                    <?php echo $themeset_field->getLabel(); ?>
                </div>
                <div class="controls">
                     <?php echo $themeset_field->getInput(); ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php
            $buffer .= ob_get_clean();
        }

        return $buffer;
    }

    /**
     * @return string
     */
    protected function getInput()
    {
        return;
    }
}