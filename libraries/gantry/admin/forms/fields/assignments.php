<?php
/**
 * @version   $Id: assignments.php 2355 2012-08-14 01:04:50Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('GANTRY_VERSION') or die();
/**
 * @package     gantry
 * @subpackage  admin.elements
 */
gantry_import('core.config.gantryformfield');

class GantryFormFieldAssignments extends GantryFormField
{


	protected $type = 'assignments';
	protected $basetype = 'checkbox';

	public function getInput()
	{
		// Initiasile related data.
		require_once JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php';
		$menuTypes = MenusHelper::getMenuLinks();
		$user      = JFactory::getUser();
		ob_start();
		?>
	<button type="button" class="jform-rightbtn" onclick="$$('.chk-menulink').each(function(el) { el.checked = !el.checked; });">
		<?php echo JText::_('JGLOBAL_SELECTION_INVERT'); ?>
	</button>
	<div class="clr"></div>
	<div id="menu-assignment">
		<?php foreach ($menuTypes as &$type) : ?>
		<ul class="menu-links">
			<h3><?php echo $type->title ? $type->title : $type->menutype; ?></h3>
			<?php foreach ($type->links as $link) : ?>
			<li class="menu-link">
				<input type="checkbox" name="jform[assigned][]" value="<?php echo (int)$link->value;?>" id="link<?php echo (int)$link->value;?>"<?php if ($link->template_style_id == $this->form->getValue('current_id')): ?> checked="checked"<?php endif;?><?php if ($link->checked_out && $link->checked_out != $user->id): ?> disabled="disabled"<?php else: ?> class="chk-menulink "<?php endif;?> />
				<label for="link<?php echo (int)$link->value;?>">
					<?php

					$text = $link->text;
					preg_match("/^(- )*/", $text, $tmp);

					$text    = str_replace($tmp[0], "", $text);
					$counter = strlen(str_replace(" ", "", $tmp[0]));

					echo '<span class="menu-padder" style="width:' . (($counter - 1) * 15) . 'px"></span>' . $text;

					?>
				</label>
			</li>
			<?php endforeach; ?>
		</ul>
		<?php endforeach; ?>
	</div>
	<?php
		$html = ob_get_clean();
		return $html;
	}

	public function getLabel()
	{
		$language = JFactory::getLanguage();
		$language->load('com_gantry');
		return '<h2>' . JText::_('Assign to Menu Items') . ':</h2>';


	}
}