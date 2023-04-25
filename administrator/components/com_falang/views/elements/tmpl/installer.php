<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

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

