<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

global  $act, $task, $option;
$user = JFactory::getUser();
$db = JFactory::getDBO();
$contentElement = $this->falangManager->getContentElement( $this->element );

?>
<?php if ($this->showMessage) : ?>
<?php echo $this->loadTemplate('message'); ?>
<?php endif; ?>
<form action="index.php" method="post" name="adminForm" id="adminForm">

    <div class="row">
        <!-- Begin Content -->
        <div class="col-md-12">
            <?php echo HTMLHelper::_('uitab.startTabSet', 'elemensTabs', array('active' => 'configuration')); ?>

            <?php echo HTMLHelper::_('uitab.addTab', 'elemensTabs', 'configuration', Text::_('COM_FALANG_ELEMENTS_CONFIGURATION')); ?>
                <?php echo $this->loadTemplate('configuration'); ?>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>

            <?php echo HTMLHelper::_('uitab.addTab', 'elemensTabs', 'reference', Text::_('COM_FALANG_ELEMENT_DB_REFERENCE')); ?>
                <?php echo $this->loadTemplate('reference'); ?>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>

            <?php echo HTMLHelper::_('uitab.addTab', 'elemensTabs', 'sample', Text::_('COM_FALANG_ELEMENT_SAMPLE_DATA')); ?>
                <?php echo $this->loadTemplate('sample'); ?>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>

            <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
        </div>
        <!-- End Content -->
    </div>


	<input type="hidden" name="option" value="com_falang" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />

    <div style="clear:both;" class="clr"></div>
</form>