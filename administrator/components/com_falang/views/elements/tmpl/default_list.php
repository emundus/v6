<?php
/**
 * @package     FaLang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2012-2013. All rights reserved.
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)): ?>
<div id="j-sidebar-container" class="span2">
    <?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>

    <?php if (FALANG_J30) { ?>
    	<table class="table table-striped">
    <?php } else { ?>
    	<table class="adminlist" cellspacing="1">
    <?php } ?>
		<thead>
		    <tr>
		      <th width="20" nowrap>&nbsp;</th>
		      <th class="title" width="35%" align="left"><?php echo JText::_('COM_FALANG_ELEMENTS_TITLE_NAME');?></th>
		      <th width="15%" align="left"><?php echo JText::_('COM_FALANG_ELEMENTS_TITLE_AUTHOR');?></th>
		      <th width="15%" nowrap="nowrap" align="left"><?php echo JText::_('COM_FALANG_ELEMENTS_TITLE_VERSION');?></th>
		      <th nowrap="nowrap" align="left"><?php echo JText::_('COM_FALANG_ELEMENTS_TITLE_DESCRIPTION');?></th>
		    </tr>
	    </thead>
		<tfoot>
		    <tr>
              <?php if (FALANG_J30) { ?>
    		      <td align="left" colspan="5">
              <?php } else { ?>
    		      <td align="center" colspan="5">
              <?php } ?>
				<?php echo $this->pageNav->getListFooter(); ?>
			  </td>
		    </tr>
	    </tfoot>
	    <tbody>
		    <?php
		    $elements = $this->falangManager->getContentElements();
		    $k=0;
		    $i=0;
		    $element_values = array_values($elements);
		    for ( $i=$this->pageNav->limitstart; $i<$this->pageNav->limitstart + $this->pageNav->limit && $i<$this->pageNav->total; $i++ ) {
		    	$element = $element_values[$i];
		    	$key = $element->referenceInformation['tablename'];
						?>
		    <tr class="<?php echo "row$k"; ?>">
		      <td width="20">
		        <?php		if ($element->checked_out && $element->checked_out != $user->id) { ?>
		        &nbsp;
		        <?php		} else { ?>
		        <input type="radio" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $key; ?>" onclick="Joomla.isChecked(this.checked);" />
		        <?php		} ?>
		      </td>
		      <td>
		      	<a href="#detail" onclick="return listItemTask('cb<?php echo $i;?>','elements.detail')"><?php echo $element->Name; ?></a>
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
	<input type="hidden" name="option" value="com_falang" />
	<input type="hidden" name="task" value="elements.show" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
