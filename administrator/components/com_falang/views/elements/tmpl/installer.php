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
 * $Id: installer.php 1551 2011-03-24 13:03:07Z akede $
 * @package joomfish
 * @subpackage Views
 *
*/
// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );
JHtml::_('behavior.tooltip');

/**
 * shows the element installer dialog
 */
global $option;
?>
<?php if (FALANG_J30) {  ?>
<form enctype="multipart/form-data" action="index.php" method="post" name="filename" class="adminForm" id="adminForm">
        <div class="row-fluid">
            <!-- Begin Content -->
            <div class="span12">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#upload" data-toggle="tab"><?php echo JText::_('COM_FALANG_CONTENT_ELEMENT_INSTALL');?></a></li>
                    <li><a href="#installed" data-toggle="tab"><?php echo JText::_('COM_FALANG_CONTENT_ELEMENTS_INSTALLED');?></a></li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane active" id="upload">
                        <fieldset class="uploadform">
                            <div class="control-group">
                                <label for="userfile" class="control-label"><?php echo JText::_('COM_FALANG_CONTENT_ELEMENT_UPLOAD'); ?></label>
                                <div class="controls">
                                    <input class="input_box" id="userfile" name="userfile" type="file" size="57" />
                                </div>
                            </div>
                            <div class="form-actions">
                                <input class="btn btn-primary" type="button" value="<?php echo JText::_('COM_FALANG_CONTENT_ELEMENT_UPLOAD_AND_INSTALL'); ?>" onclick="Joomla.submitbutton()" />
                            </div>
                        </fieldset>
                    </div>
                      <div class="tab-pane" id="installed">
                          <?php if( $this->cElements != null ) { ?>

                        <table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminlist">
                        <tr>
                            <th class="title" width="35%" align="left"><?php echo JText::_('COM_FALANG_ELEMENTS_TITLE_NAME');?></th>
                            <th width="15%" align="left"><?php echo JText::_('COM_FALANG_ELEMENTS_TITLE_AUTHOR');?></th>
                            <th width="15%" nowrap="nowrap" align="left"><?php echo JText::_('COM_FALANG_ELEMENTS_TITLE_VERSION');?></th>
                            <th nowrap="nowrap" align="left"><?php echo JText::_('COM_FALANG_ELEMENTS_TITLE_DESCRIPTION');?></th>
                        </tr>
                              <?php
                              $k=0;
                              $i=0;
                              foreach (array_values($this->cElements) as $element ) {
                                  $key = $element->referenceInformation['tablename'];
                                  ?>
                                  <tr class="<?php echo "row$k"; ?>">
                                      <td><?php echo $element->Name; ?></td>
                                      <td><?php echo $element->Author ? $element->Author : '&nbsp;'; ?></td>
                                      <td><?php echo $element->Version ? $element->Version : '&nbsp;'; ?></td>
                                      <td><?php echo $element->Description ? $element->Description : '&nbsp;'; ?></td>
                                  </tr>
                                  <?php
                                  $k = 1 - $k;
                                  $i++;
                              }
                          } else {
                              ?>
                              <tr><td class="small">
                                  There are no custom elements installed
                              </td></tr>
                              <?php
                          }
                              ?>
                          </table>

                    </div>
            </div>
    </div>
    <div style="clear: both;"></div>

  <input type="hidden" name="task" value="elements.uploadfile"/>
  <input type="hidden" name="option" value="com_falang"/>
</form>
<?php } else { ?>
    <form enctype="multipart/form-data" action="index.php" method="post" name="filename" class="adminForm">
        <table class="adminheading">
            <tr>
                <th class="install"><?php echo JText::_('Install');?> <?php echo JText::_('Content Elements');?></th>
            </tr>
        </table>
        <table class="adminform">
            <tr>
                <th><?php echo JText::_('Upload XML file');?></th>
            </tr>
            <tr>
                <td align="left"><?php echo JText::_('File name');?>:
                    <input class="text_area" name="userfile" type="file" size="70"/>
                    <input class="button" type="submit" value="<?php echo JText::_('Upload file and install');?>" />
                </td>
            </tr>
        </table>

        <input type="hidden" name="task" value="elements.uploadfile"/>
        <input type="hidden" name="option" value="com_falang"/>
    </form>
    <p>&nbsp;</p>
    <?php if( $this->cElements != null ) { ?>
    <form action="index.php" method="post" name="adminForm">
        <table class="adminheading">
            <tr>
                <th class="install"><?php echo JText::_('COM_FALANG_CONTENT_ELEMENTS_INSTALLED');?></th>
            </tr>
        </table>

        <table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminlist">
        <tr>
            <th width="20" nowrap>&nbsp;</th>
            <th class="title" width="35%" align="left"><?php echo JText::_('COM_FALANG_ELEMENTS_TITLE_NAME');?></th>
            <th width="15%" align="left"><?php echo JText::_('COM_FALANG_ELEMENTS_TITLE_AUTHOR');?></th>
            <th width="15%" nowrap="nowrap" align="left"><?php echo JText::_('COM_FALANG_ELEMENTS_TITLE_VERSION');?></th>
            <th nowrap="nowrap" align="left"><?php echo JText::_('COM_FALANG_ELEMENTS_TITLE_DESCRIPTION');?></th>
        </tr>
            <?php
            $k=0;
            $i=0;
            foreach (array_values($this->cElements) as $element ) {
                $key = $element->referenceInformation['tablename'];
                ?>
                <tr class="<?php echo "row$k"; ?>">
                    <td width="20">
                        <?php		if ($element->checked_out && $element->checked_out != $user->id) { ?>
                        &nbsp;
                        <?php		} else { ?>
                        <input type="radio" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $key; ?>" onclick="isChecked(this.checked);">
                        <?php		} ?>
                    </td>
                    <td><?php echo $element->Name; ?></td>
                    <td><?php echo $element->Author ? $element->Author : '&nbsp;'; ?></td>
                    <td><?php echo $element->Version ? $element->Version : '&nbsp;'; ?></td>
                    <td><?php echo $element->Description ? $element->Description : '&nbsp;'; ?></td>
                </tr>
                <?php
                $k = 1 - $k;
                $i++;
            }
        } else {
            ?>
            <tr><td class="small">
                There are no custom elements installed
            </td></tr>
            <?php
        }
        ?>
    </table>
    <input type="hidden" name="task" value="elements.uploadfile"/>
        <input type="hidden" name="option" value="com_falang"/>
        <input type="hidden" name="boxchecked" value="0" />
    </form>
<?php } ?>

