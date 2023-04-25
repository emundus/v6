<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die;

/**
	 * show the Orphan translations
	 *
	 * @param array $rows
	 * @param array $catid	category id's
	 */
$rows = $this->rows;
global  $act,  $option;
$user = JFactory::getUser();
$db = JFactory::getDBO();

         ?>
<form action="index.php" method="post" name="adminForm" id="adminForm">

   <input type="hidden" name="cid[]" value="<?php echo $rows[0]->id."|".$rows[0]->reference_id."|".$rows[0]->language_id; ?>"/>
   <input type="hidden" name="catid" value="<?php echo $catid;?>" />
   <table width="100%" border="1" cellpadding="4" cellspacing="2" class="adminForm">
	<tr align="center" valign="middle">
	      <th width="10%" align="left" valign="top"><?php echo JText::_('COM_FALANG_DBFIELDLABEL');?></th>
	      <th width="12%" align="left" valign="top"><?php echo JText::_('COM_FALANG_ORIGINAL');?></th>
	      <th width="78%" align="left" valign="top"><?php echo JText::_('COM_FALANG_TRANSLATION');?></th>
        </tr>
        <?php
        $style1="style='background-color:rgb(241,243,245)'";
        $style2="style='background-color:rgb(255,228,196)'";
        $style=$style1;
        ?>
		<tr align="center" valign="middle" <?php echo $style; ?>>
	      <td align="left" valign="top"><?php echo "Debug Info";?></td>
	      <td colspan="2" align="left" valign="top"><?php echo "Original Table:<b>".$rows[0]->reference_table."</b> === Orginal Id: <b>".$rows[0]->reference_id."</b>";?></td>
        </tr>
        <?php
        foreach ($rows as $row) {
        	$style=$style==$style1?$style2:$style1;
		?>
        <tr align="center" valign="middle" <?php echo $style; ?>>
	      <td align="left" valign="top"><?php echo $row->reference_field;?></td>
	      <td align="left" valign="top"><?php echo is_null($row->original_text)?$row->original_value:$row->original_text;?></td>
	      <td align="left" valign="top"><?php echo $row->value;?></td>
	    </tr>
	    <?php
        }
	    ?>
   </table>


    <input type="hidden" name="option" value="com_falang" />
	<input type="hidden" name="task" value="translate.orphans" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo HTMLHelper::_( 'form.token' ); ?>
</form>
