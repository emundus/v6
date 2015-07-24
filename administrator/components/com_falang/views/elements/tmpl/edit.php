<?php
/**
 * Joom!Fish - Multi Lingual extention and translation manager for Joomla!
 * Copyright (C) 2003 - 2011, Think Network GmbH, Munich
 *
 * All rights reserved.  The Joom!Fish project is a set of extentions for
 * the content management system Joomla!. It enables Joomla!
 * to manage multi lingual sites especially in all dynamic information
 * which are stored in the database.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307,USA.
 *
 * The "GNU General Public License" (GPL) is available at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * -----------------------------------------------------------------------------
 * $Id: edit.php 1551 2011-03-24 13:03:07Z akede $
 * @package joomfish
 * @subpackage Views
 *
*/

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

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