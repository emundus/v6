<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var $this Akeeba\AdminTools\Admin\View\MasterPassword\Html */

// Protect from unauthorized access
defined('_JEXEC') or die;

use Akeeba\AdminTools\Admin\Helper\Select;

?>
<form action="index.php" method="post" name="adminForm" id="adminForm" class="akeeba-form--horizontal">
    <div class="akeeba-panel--primary">
        <header class="akeeba-block-header">
            <h3><?php echo \JText::_('COM_ADMINTOOLS_LBL_MASTERPASSWORD_PASSWORD'); ?></h3>
        </header>

        <div class="akeeba-form-section--horizontal">
			<label for="masterpw"><?php echo \JText::_('COM_ADMINTOOLS_LBL_MASTERPASSWORD_PWPROMPT'); ?></label>

			<div>
				<input id="masterpw" type="password" name="masterpw" value="<?php echo $this->escape($this->masterpw); ?>"/>
			</div>
		</div>
	</div>

    <div class="akeeba-panel--primary">
        <header class="akeeba-block-header">
            <h3><?php echo \JText::_('COM_ADMINTOOLS_LBL_MASTERPASSWORD_PROTVIEWS'); ?></h3>
        </header>

		<div class="akeeba-form-group">
			<label><?php echo \JText::_('COM_ADMINTOOLS_LBL_MASTERPASSWORD_QUICKSELECT'); ?>&nbsp;</label>

			<div>
                <button class="akeeba-btn--primary--small"
                        onclick="return admintools.MasterPassword.doMassSelect(1);"><?php echo \JText::_('COM_ADMINTOOLS_LBL_MASTERPASSWORD_ALL'); ?></button>
                <button class="akeeba-btn--dark--small"
                        onclick="return admintools.MasterPassword.doMassSelect(0);"><?php echo \JText::_('COM_ADMINTOOLS_LBL_MASTERPASSWORD_NONE'); ?></button>
			</div>
		</div>
		<?php foreach ($this->items as $view => $x):
			list($locked, $langKey) = $x;
			?>
			<div class="akeeba-form-group">
				<label for="views[<?php echo $this->escape($view); ?>]"
					  ><?php echo \JText::_($langKey); ?></label>

                <?php echo \JHtml::_('FEFHelper.select.booleanswitch', 'views[' . $view . ']', ($locked ? 1 : 0), array('class' => 'masterpwcheckbox')); ?>
			</div>
		<?php endforeach; ?>
    </div>

    <input type="hidden" name="option" value="com_admintools"/>
    <input type="hidden" name="view" value="MasterPassword"/>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="<?php echo $this->container->platform->getToken(true); ?>" value="1"/>
</form>
