<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('formbehavior.chosen', 'select');

$document = Factory::getDocument();
$document->addScript('components/com_falang/assets/js/falang.js', array('version' => 'auto', 'relative' => true));

$user = Factory::getUser();
$db = Factory::getDBO();

if (isset($this->filterlist) && count($this->filterlist)>0){
    $filterOptions = '<div id="filter-bar" class="btn-toolbar">';
    foreach ($this->filterlist as $fl){
        if (is_array($fl) && !empty($fl['position']) && $fl['position'] != 'sidebar')		$filterOptions .= "<div class='btn-group pull-left' style='margin-right: auto'>".$fl["html"]."</div>";
    }
    //inverted due to the float right
    $filterOptions .= '<div class="btn-group pull-left">';
	$filterOptions .= '<div class="mr-2">'.$this->clist.'</div>';
	$filterOptions .= $this->langlist;
    $filterOptions .= '</div>';
	$filterOptions .= '</div>';

} else {
    $filterOptions = '<div id="filter-bar" class="btn-toolbar">';
    //inverted due to the float right
    $filterOptions .= '<div class="btn-group pull-right">'.$this->clist.'</div>';
    $filterOptions .= '<div class="btn-group pull-right">'.$this->langlist.'</div>';
    $filterOptions .= '</div>';
}


?>
<form action="<?php echo Route::_('index.php?option=com_falang'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
            <?php if(!empty( $this->sidebar)): ?>
            <div id="j-sidebar-container" class="col-md-2">
                <?php echo $this->sidebar; ?>
            </div>
                <div id="j-main-container" class="j-main-container col-md-10">
            <?php else : ?>
                <div id="j-main-container" class="j-main-container col-md-12">
            <?php endif;?>

        <?php echo $filterOptions; ?>

        <div class="clearfix"> </div>
        <table id="translateList" class="table table-striped">
        <thead>
            <tr>
              <th width="20"><input type="checkbox" name="checkall-toggle" value="" onclick="Joomla.checkAll(this);" /></th>
              <th class="title" width="20%" align="left"  nowrap="nowrap"><?php echo Text::_('COM_FALANG_TRANSLATE_TITLE_TITLE');?></th>
              <th width="10%" align="left" nowrap="nowrap"><?php echo Text::_('COM_FALANG_TRANSLATE_TITLE_LANGUAGE');?></th>
              <th width="20%" align="left" nowrap="nowrap"><?php echo Text::_('COM_FALANG_TRANSLATE_TITLE_TRANSLATION');?></th>
              <th width="15%" align="left" nowrap="nowrap"><?php echo Text::_('COM_FALANG_TRANSLATE_TITLE_DATECHANGED');?></th>
              <th width="15%" nowrap="nowrap" align="center"><?php echo Text::_('COM_FALANG_TRANSLATE_TITLE_STATE');?></th>
              <th align="center" nowrap="nowrap"><?php echo Text::_('COM_FALANG_TRANSLATE_TITLE_PUBLISHED');?></th>
              <th align="center" nowrap="nowrap"><?php echo Text::_('COM_FALANG_TRANSLATE_TITLE_ID');?></th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                  <td align="left" colspan="7">
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
                <span class="icon-checkedout" title="<?php echo Text::_('COM_FALANG_TRANSLATE_ITEM_LOCKED');?>"></span>

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
          <td nowrap><?php echo $row->language ? $row->language : Text::_('COM_FALANG_NOTRANSLATIONYET') ; ?></td>
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
          <td><?php echo $row->lastchanged ? HTMLHelper::_('date', $row->lastchanged, Text::_('DATE_FORMAT_LC2')):"" ;?></td>
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
                           $href = HTMLHelper::_('jgrid.published', $row->published, $i, 'translate.');
                    }
                    else {
                           $href = HTMLHelper::_('jgrid.published', $row->published , $i, 'translate.',false);
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
        <td> <img src="components/com_falang/assets/images/status_g.png" width="12" height="12" border=0 alt="<?php echo Text::_('STATE_OK');?>" />
        </td>
        <td> <?php echo Text::_('COM_FALANG_TRANSLATION_UPTODATE');?> |</td>
        <td> <img src="components/com_falang/assets/images/status_y.png" width="12" height="12" border=0 alt="<?php echo Text::_('STATE_CHANGED');?>" />
        </td>
        <td> <?php echo Text::_('COM_FALANG_TRANSLATION_INCOMPLETE');?> |</td>
        <td> <img src="components/com_falang/assets/images/status_r.png" width="12" height="12" border=0 alt="<?php echo Text::_('COM_FALANG_STATE_NOTEXISTING');?>" />
        </td>
        <td> <?php echo Text::_('COM_FALANG_TRANSLATION_NOT_EXISTING');?></td>
      </tr>
      <tr align="center">
        <td>
                <i class="icon-publish"></i>
        </td>
        <td> <?php echo Text::_('COM_FALANG_TRANSLATION_PUBLISHED');?>  |</td>
        <td>
            <i class="icon-unpublish"></i>
        </td>
        <td> <?php echo Text::_('COM_FALANG_TRANSLATION_NOT_PUBLISHED');?></td>
        <td>
            <i class="icon-unpublish disabled"></i>
        </td>
        <td> <?php echo Text::_('COM_FALANG_STATE_TOGGLE');?></td>
      </tr>
    </table>

        <input type="hidden" name="option" value="com_falang" />
        <input type="hidden" name="task" value="translate.show" />
        <input type="hidden" name="boxchecked" value="0" />
        <?php echo HTMLHelper::_( 'form.token' ); ?>
  </div>
</form>