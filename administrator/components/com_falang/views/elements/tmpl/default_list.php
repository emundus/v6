<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2021. Faboba.com All rights reserved.
 */
defined('_JEXEC') or die;

// No direct access to this file
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

$user = Factory::getUser();
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th style="min-width: 150px;" nowrap>&nbsp;</th>
                        <th class="title text-left"
                            style="min-width: 150px;"><?php echo JText::_('COM_FALANG_ELEMENTS_TITLE_NAME'); ?></th>
                        <th class="text-left"
                            style="min-width: 150px;"><?php echo JText::_('COM_FALANG_ELEMENTS_TITLE_AUTHOR'); ?></th>
                        <th class="text-left"
                            style="min-width: 150px;"><?php echo JText::_('COM_FALANG_ELEMENTS_TITLE_VERSION'); ?></th>
                        <th class="text-left" style="min-width: 150px;"
                            nowrap="nowrap"><?php echo JText::_('COM_FALANG_ELEMENTS_TITLE_DESCRIPTION'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $elements = $this->falangManager->getContentElements();
                    $k = 0;
                    $i = 0;
                    $element_values = array_values($elements);
                    for ($i = $this->pageNav->limitstart;
                    $i < $this->pageNav->limitstart + $this->pageNav->limit && $i < $this->pageNav->total;
                    $i++) {
                    $element = $element_values[$i];
                    $key = $element->referenceInformation['tablename'];
                    ?>
                    <tr class="<?php echo "row$k"; ?>">
                        <td width="20">
                            <?php if ($element->checked_out && $element->checked_out != $user->id) { ?>
                                &nbsp;
                            <?php } else { ?>
                                <input type="radio" id="cb<?php echo $i; ?>" name="cid[]" value="<?php echo $key; ?>"
                                       onclick="Joomla.isChecked(this.checked);"/>
                            <?php } ?>
                        </td>
                        <td>
                            <a href="<?php echo Route::_('index.php?option=com_falang&task=elements.detail&element=' . $key); ?>"><?php echo $element->Name; ?></a>
                        </td>
                        <td><?php echo $element->Author ? $element->Author : '&nbsp;'; ?></td>
                        <td><?php echo $element->Version ? $element->Version : '&nbsp;'; ?></td>
                        <td><?php echo $element->Description ? $element->Description : '&nbsp;'; ?></td>
                        <?php
                        $k = 1 - $k;
                        }
                        ?>
                    </tr>
                    </tbody>
                </table>

                <?php echo $this->pageNav->getListFooter(); ?>

            </div>
        </div>
        <input type="hidden" name="option" value="com_falang"/>
        <input type="hidden" name="task" value="elements.show"/>
        <input type="hidden" name="boxchecked" value="0"/>
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
