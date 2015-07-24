<?php
/**
 * @package     FaLang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2012-2013. All rights reserved.
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

JHtml::_('behavior.tooltip');

if (FALANG_J30) {
    JHtml::_('formbehavior.chosen', 'select');
}


$user = JFactory::getUser();
$db = JFactory::getDBO();
if (FALANG_J30) {
    if (isset($this->filterlist) && count($this->filterlist)>0){
        $filterOptions = '<div id="filter-bar" class="btn-toolbar">';
        foreach ($this->filterlist as $fl){
            if (is_array($fl) && !empty($fl['position']) && $fl['position'] != 'sidebar')		$filterOptions .= "<div class='btn-group pull-left'>".$fl["html"]."</div>";
        }
        //inverted due to the float right
        $filterOptions .= '<div class="btn-group pull-right">'.$this->pageNav->getLimitBox().'</div>';
        $filterOptions .= '<div class="btn-group pull-right">'.$this->clist.'</div>';
        $filterOptions .= '<div class="btn-group pull-right">'.$this->langlist.'</div>';
        $filterOptions .= '</div>';

    } else {
        $filterOptions = '<div id="filter-bar" class="btn-toolbar">';
        //inverted due to the float right
        $filterOptions .= '<div class="btn-group pull-right">'.$this->pageNav->getLimitBox().'</div>';
        $filterOptions .= '<div class="btn-group pull-right">'.$this->clist.'</div>';
        $filterOptions .= '<div class="btn-group pull-right">'.$this->langlist.'</div>';
        $filterOptions .= '</div>';
    }
 } else {
    $filterOptions = '<table><tr><td width="100%"></td>';
    $filterOptions .= '<td  nowrap="nowrap" align="center">' .JText::_('COM_FALANG_SELECT_LANGUAGE'). ':<br/>' .$this->langlist. '</td>';
    $filterOptions .= '<td  nowrap="nowrap" align="center">' .JText::_('COM_FALANG_SELECT_CONTENT_ELEMENT'). ':<br/>' .$this->clist. '</td>';
    $filterOptions .= '</tr></table>';

    if (isset($this->filterlist) && count($this->filterlist)>0){
        $filterOptions .= '<table><tr><td width="100%"></td>';
        foreach ($this->filterlist as $fl){
            if (is_array($fl))		$filterOptions .= "<td nowrap='nowrap' align='center'>".$fl["title"].":<br/>".$fl["html"]."</td>";
        }
        $filterOptions .= '</tr></table>';
    }
 }



?>
<form action="<?php echo JRoute::_('index.php?option=com_falang');?>" method="post" name="adminForm" id="adminForm">
        <?php if(!empty( $this->sidebar)): ?>
        <div id="j-sidebar-container" class="span2">
            <?php echo $this->sidebar; ?>
        </div>
            <div id="j-main-container" class="span10">
        <?php else : ?>
            <div id="j-main-container">
        <?php endif;?>

    <?php  echo $filterOptions; ?>

    <?php if (FALANG_J30) { ?>
        <div class="clearfix"> </div>
        <table class="table table-striped">
    <?php } else { ?>
        <table class="adminlist" cellspacing="1">
    <?php } ?>
    <thead>
        <tr>
          <?php  if (FALANG_J30) { ?>
            <th width="20"><input type="checkbox" name="checkall-toggle" value="" onclick="Joomla.checkAll(this);" /></th>
          <?php } else { ?>
            <th width="20"><input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(<?php echo count($this->rows); ?>);" /></th>
          <?php }  ?>
          <th class="title" width="20%" align="left"  nowrap="nowrap"><?php echo JText::_('COM_FALANG_TRANSLATE_TITLE_TITLE');?></th>
          <th width="10%" align="left" nowrap="nowrap"><?php echo JText::_('COM_FALANG_TRANSLATE_TITLE_LANGUAGE');?></th>
          <th width="20%" align="left" nowrap="nowrap"><?php echo JText::_('COM_FALANG_TRANSLATE_TITLE_TRANSLATION');?></th>
          <th width="15%" align="left" nowrap="nowrap"><?php echo JText::_('COM_FALANG_TRANSLATE_TITLE_DATECHANGED');?></th>
          <th width="15%" nowrap="nowrap" align="center"><?php echo JText::_('COM_FALANG_TRANSLATE_TITLE_STATE');?></th>
          <th align="center" nowrap="nowrap"><?php echo JText::_('COM_FALANG_TRANSLATE_TITLE_PUBLISHED');?></th>
          <th align="center" nowrap="nowrap"><?php echo JText::_('COM_FALANG_TRANSLATE_TITLE_ID');?></th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <?php if (FALANG_J30) { ?>
    		      <td align="left" colspan="7">
              <?php } else { ?>
    		      <td align="center" colspan="7">
              <?php } ?>
			<?php echo $this->pageNav->getListFooter(); ?>
		  </td>
		</tr>
    </tfoot>
    
    <tbody>
    <?php
    $k=0;
    $i=0;
	foreach ($this->rows as $row ) {
				?>
    <tr class="<?php echo "row$k"; ?>">
      <td width="20">
        <?php		if ($row->checked_out && $row->checked_out != $user->id) { ?>
        &nbsp;
        <?php		} else { ?>
        <input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row->translation_id."|".$row->id."|".$row->language_id; ?>" onclick="Joomla.isChecked(this.checked);" />
        <?php		} ?>
      </td>
      <td>
      	<?php
      	$title = $row->title;
      	if(strlen($title) > 75) {
      		$title = '<span title="' .$title. '">';
      		$title .= substr($row->title,0, 75) .' ...';
      		$title .= '</span>';
      	}
      	?>
      	<a href="#edit" onclick="return listItemTask('cb<?php echo $i;?>','translate.edit');">
      	<?php
      	// Cutting the tile to a max number in order to support long title fields
      	 echo $title; 
      	?></a>
			</td>
      <td nowrap><?php echo $row->language ? $row->language : JText::_('COM_FALANG_NOTRANSLATIONYET') ; ?></td>
      <td><?php
      	$translation = $row->titleTranslation ? $row->titleTranslation : '&nbsp;';
      	$output = '';
      	if(strlen($translation) > 75) {
      		$output = '<span title="' .$translation. '">';
      		$output .= substr($translation,0, 75) .' ...';
      		$output .= '</span>';
      	} else {
      		$output = $translation;
      	}
      
       echo $output; 
       ?></td>
	  <td><?php echo $row->lastchanged ? JHTML::_('date', $row->lastchanged, JText::_('DATE_FORMAT_LC2')):"" ;?></td>
				<?php
				switch( $row->state ) {
					case 1:
						$img = 'status_g.png';
						break;
					case 0:
						$img = 'status_y.png';
						break;
					case -1:
					default:
						$img = 'status_r.png';
						break;
				}
				?>
      <td align="center"><img src="components/com_falang/assets/images/<?php echo $img;?>" width="12" height="12" border="0" alt="" /></td>
				<?php
				$href='';
				if( $row->state>=0 ) {
                       $href = JHtml::_('jgrid.published', $row->published, $i, 'translate.');
				}
				else {
                    if (FALANG_J30) {
                        $href = JHtml::_('jgrid.published', $row->published , $i, 'translate.',false);
                    } else {
                        $href = '<a class="jgrid">';
                        $href .= '<span class="state expired"><span class="text">'.JText::_('COM_FALANG_EXPIRED').'</span></span>';
                        $href .= '</a>';
                    }
				}
				?>
      <td align="center"><?php echo $href;?></td>
      <td> <?php echo $row->id ?></td>
    </tr>
		<?php
		$k = 1 - $k;
		$i++;
	}?>
	</tbody>
</table>
<table cellspacing="0" cellpadding="4" border="0" align="center">
  <tr align="center">
    <td> <img src="components/com_falang/assets/images/status_g.png" width="12" height="12" border=0 alt="<?php echo JText::_('STATE_OK');?>" />
    </td>
    <td> <?php echo JText::_('COM_FALANG_TRANSLATION_UPTODATE');?> |</td>
    <td> <img src="components/com_falang/assets/images/status_y.png" width="12" height="12" border=0 alt="<?php echo JText::_('STATE_CHANGED');?>" />
    </td>
    <td> <?php echo JText::_('COM_FALANG_TRANSLATION_INCOMPLETE');?> |</td>
    <td> <img src="components/com_falang/assets/images/status_r.png" width="12" height="12" border=0 alt="<?php echo JText::_('COM_FALANG_STATE_NOTEXISTING');?>" />
    </td>
    <td> <?php echo JText::_('COM_FALANG_TRANSLATION_NOT_EXISTING');?></td>
  </tr>
  <tr align="center">
    <td>
        <?php if (FALANG_J30) { ?>
            <i class="icon-publish"></i>
        <?php } else {
                    echo JHTML::_('image.administrator', 'admin/tick.png', NULL, NULL, NULL, JText::_('COM_FALANG_TRANSLATION_PUBLISHED'),array('style' => 'width:12px;height:12px'));
              } ?>
    </td>
    <td> <?php echo JText::_('COM_FALANG_TRANSLATION_PUBLISHED');?>  |</td>
    <td>
        <?php if (FALANG_J30) { ?>
            <i class="icon-unpublish"></i>
        <?php } else {
                    echo JHTML::_('image.administrator', 'admin/publish_x.png', NULL, NULL, NULL, JText::_('COM_FALANG_TRANSLATION_NOT_PUBLISHED'),array('style' => 'width:12px;height:12px'));
              } ?>
    </td>
    <td> <?php echo JText::_('COM_FALANG_TRANSLATION_NOT_PUBLISHED');?></td>
    <td>
        <?php if (FALANG_J30) { ?>
        <i class="icon-unpublish disabled"></i>
        <?php } else {
                echo JHTML::_('image.administrator', 'admin/publish_r.png', NULL, NULL, NULL, JText::_('COM_FALANG_STATE_TOGGLE'),array('style' => 'width:12px;height:12px'));
              } ?>
    </td>
    <td> <?php echo JText::_('COM_FALANG_STATE_TOGGLE');?></td>
  </tr>
</table>

	<input type="hidden" name="option" value="com_falang" />
	<input type="hidden" name="task" value="translate.show" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>