<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidator');

$document = JFactory::getDocument();
$document->addStyleSheet("templates/g5_helium/html/com_users/reset/style/com_users_reset.css");

$params = JComponentHelper::getParams('com_users');
$min_length = $params->get('minimum_length');
$min_int = $params->get('minimum_integers');
$min_sym = $params->get('minimum_symbols');
$min_up = $params->get('minimum_uppercase');
$min_low = $params->get('minimum_lowercase');

$tip_text = JText::sprintf('USER_PASSWORD_MIN_LENGTH', $min_length);

if ((int)$min_int > 0) {
	$tip_text .= ','.JText::sprintf('USER_PASSWORD_MIN_INT', $min_int);
}
if ((int)$min_sym > 0) {
	$tip_text .= ','.JText::sprintf('USER_PASSWORD_MIN_SYM', $min_sym);
}
if ((int)$min_up > 0) {
	$tip_text .= ','.JText::sprintf('USER_PASSWORD_MIN_UPPER', $min_up);
}
if ((int)$min_low > 0) {
	$tip_text .= ','.JText::sprintf('USER_PASSWORD_MIN_LOWER', $min_low);
}

require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'settings.php');
$m_settings = new EmundusModelsettings();

$favicon = $m_settings->getFavicon();

?>
<div class="reset-complete<?php echo $this->pageclass_sfx; ?>">
	<?php if ($this->params->get('show_page_heading')) : ?>
		<div class="page-header">
            <?php if (file_exists($favicon)) : ?>
                <a href="index.php" alt="Logo" class="em-profile-picture mb-8" style="width: 50px;height: 50px;background-image: url(<?php echo $favicon ?>)">
                </a>
            <?php endif; ?>
            <h3 class="em-mb-8">
                <?php echo $this->escape($this->params->get('page_heading')); ?>
            </h3>
		</div>
	<?php endif; ?>
	<form action="<?php echo JRoute::_('index.php?option=com_users&task=reset.complete'); ?>" method="post" class="form-validate form-horizontal well">
		<?php foreach ($this->form->getFieldsets() as $fieldset) : ?>
			<fieldset>
				<?php if (isset($fieldset->label)) : ?>
					<p class="mb-4">
                        <?php echo JText::_($fieldset->label); ?>
                        <br/>
                        <?php echo $tip_text ?>
                    </p>
				<?php endif; ?>

				<?php echo $this->form->renderFieldset($fieldset->name); ?>
			</fieldset>
		<?php endforeach; ?>
		<div class="control-group">
			<div class="controls">
				<button type="submit" class="btn btn-primary validate">
					<?php echo JText::_('JSUBMIT'); ?>
				</button>
			</div>
		</div>
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
