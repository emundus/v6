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

require_once (JPATH_SITE.'/components/com_emundus/helpers/cache.php');
$hash = EmundusHelperCache::getCurrentGitHash();

$document = JFactory::getDocument();
$document->addStyleSheet("templates/g5_helium/html/com_users/reset/style/com_users_reset.css?" . $hash);
$username = JFactory::getApplication()->input->getString('username');

$this->form->setValue('username', '', $username);

require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'settings.php');
$m_settings = new EmundusModelsettings();

$favicon = $m_settings->getFavicon();

?>
<div class="reset-confirm<?php echo $this->pageclass_sfx; ?>">

		<div class="page-header">
            <?php if (file_exists($favicon)) : ?>
                <a href="index.php" alt="Logo" class="em-profile-picture mb-8" style="width: 50px;height: 50px;background-image: url(<?php echo $favicon ?>)">
                </a>
            <?php endif; ?>
            <h1 class="em-mb-8">
                <?php echo $this->escape($this->params->get('page_heading')); ?>
            </h1>
		</div>

        <form action="<?php echo JRoute::_('index.php?option=com_users&task=reset.confirm'); ?>" method="post" class="form-validate form-horizontal well">
            <?php foreach ($this->form->getFieldsets() as $fieldset) : ?>
                <fieldset>
                    <?php if (isset($fieldset->label)) : ?>
                        <p class="mb-4"><?php echo JText::_($fieldset->label); ?></p>
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

