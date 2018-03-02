<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

	global  $act, $task, $option;
	$user = JFactory::getUser();
	$db = JFactory::getDBO();
	$contentElement = $this->falangManager->getContentElement( $this->id );
?>
<?php if ($this->showMessage) : ?>
<?php echo $this->loadTemplate('message'); ?>
<?php endif; ?>
<form action="index.php" method="post" name="adminForm" id="adminForm">

    <?php if (FALANG_J30) {  ?>
    <div class="row-fluid">
        <!-- Begin Content -->
        <div class="span10">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#configuration" data-toggle="tab"><?php echo JText::_('COM_FALANG_ELEMENTS_CONFIGURATION');?></a></li>
                <li><a href="#reference" data-toggle="tab"><?php echo JText::_('COM_FALANG_ELEMENT_DB_REFERENCE');?></a></li>
                <li><a href="#sample" data-toggle="tab"><?php echo JText::_('COM_FALANG_ELEMENT_SAMPLE_DATA');?></a></li>
            </ul>
            <div class="tab-content">
                <!-- Begin Tabs -->
                <div class="tab-pane active" id="configuration">
                    <?php echo $this->loadTemplate('configuration'); ?>
                </div>
                <div class="tab-pane " id="reference">
                    <?php echo $this->loadTemplate('reference'); ?>
                </div>
                <div class="tab-pane " id="sample">
                    <?php echo $this->loadTemplate('sample'); ?>
                </div>
                <!-- End Tabs -->
            </div>
        </div>
        <!-- End Content -->
    </div>

    <?php } else {

    jimport('joomla.html.pane');
    $tabs =  JPane::getInstance('tabs');
    echo $tabs->startPane("contentelements");
    echo $tabs->startPanel(JText::_('COM_FALANG_ELEMENTS_CONFIGURATION'),"ElementConfig-page");
        echo $this->loadTemplate('configuration');
    echo $tabs->endPanel();
    echo $tabs->startPanel(JText::_('COM_FALANG_ELEMENT_DB_REFERENCE'),"ElementReference-page");
        echo $this->loadTemplate('reference');
    echo $tabs->endPanel();
    echo $tabs->startPanel(JText::_('COM_FALANG_ELEMENT_SAMPLE_DATA'),"ElementSamples-page");
        echo $this->loadTemplate('sample');
    echo $tabs->endPanel();
    echo $tabs->endPane();

}  ?>

	<input type="hidden" name="option" value="com_falang" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />

    <div style="clear:both;" class="clr"></div>
</form>