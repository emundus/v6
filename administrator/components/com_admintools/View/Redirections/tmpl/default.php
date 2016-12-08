<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

/** @var $this \Akeeba\AdminTools\Admin\View\Redirections\Form */
use Akeeba\AdminTools\Admin\Helper\Select;

defined('_JEXEC') or die;

$model = $this->getModel();

// Let's check if the system plugin is correctly installed AND published
echo $this->loadAnyTemplate('admin:com_admintools/WebApplicationFirewall/plugin_warning');

?>
<form name="enableForm" action="index.php" method="post">
	<input type="hidden" name="option" id="option" value="com_admintools"/>
	<input type="hidden" name="view" id="view" value="Redirections"/>
	<input type="hidden" name="task" id="task" value="applypreference"/>

	<div class="well">
		<div class="form-inline">
			<label for="urlredirection"><?php echo JText::_('COM_ADMINTOOLS_LBL_REDIRECTION_PREFERENCE'); ?></label>
			<?php echo Select::booleanlist('urlredirection', array('class' => 'input-mini'), $this->urlredirection) ?>
			<input class="btn btn-small btn-inverse" type="submit"
				   value="<?php echo JText::_('COM_ADMINTOOLS_LBL_REDIRECTION_PREFERENCE_SAVE') ?>"/>
		</div>
	</div>
</form>

<?php
	echo $this->getRenderedForm();
?>