<?php
/**
 * @version		$Id: default.php 14401 2014-09-16 14:10:00Z brivalland $
 * @package		Joomla
 * @subpackage	Emundus
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
JHTML::_('behavior.tooltip');
JHTML::stylesheet('media/com_emundus/css/emundus_panel.css' );
?>

<div class="container-fluid">
	<div class="row">
		<div class="col-md-3 side-panel">
			<div class="panel panel-info em-containerFilter" id="em-files-filters">
				<div class="panel-heading em-containerFilter-heading">
					<div>
						<h3 class="panel-title"><?php echo JText::_('FILTERS')?></h3> &ensp;&ensp;
					</div>
					<div class="buttons" style="float:right; margin-top:0px">
						<input value="&#xe003" type="button" class="btn btn-sm btn-info glyphicon glyphicon-search" name="search" id="search"  title="<?php echo JText::_('SEARCH_BTN');?>"/>&ensp;
						<input value="&#xe090" type="button" class="btn btn-sm btn-danger glyphicon glyphicon-ban-circle" name="clear-search" id="clear-search" title="<?php echo JText::_('CLEAR_BTN');?>"/>&ensp;
						<button class="btn btn-sm btn-warning" id="save-filter" style="width:50px;" title="<?php echo JText::_('SAVE_FILTER');?>"><i class="ui save icon"></i></button><br/><br/>
					</div>
				</div>
				<div class="panel-body em-containerFilter-body">
					<?php echo @$this->filters ?>
				</div>
			</div>

			<div class="panel panel-info em-hide" id="em-appli-menu">
				<div class="panel-heading em-hide-heading">
					<h3 class="panel-title"><?php echo JText::_('APPLICATIONS_ACTIONS')?></h3>
				</div>
				<div class="panel-body em-hide-body">
					<div class="list-group">
					</div>
				</div>
			</div>

			<div class="panel panel-info em-hide" id="em-synthesis">
				<div class="panel-heading em-hide-heading">
					<h3 class="panel-title"><?php echo JText::_('APPLICATION_SYNTHESIS')?></h3>
				</div>
				<div class="panel-body em-hide-body">
				</div>
			</div>

			<div class="panel panel-info em-hide" id="em-assoc-files">
				<div class="panel-heading em-hide-heading">
					<h3 class="panel-title"><?php echo JText::_('LINKED_APPLICATION_FILES')?></h3>
				</div>
				<div class="panel-body em-hide-body">

				</div>
			</div>



			<div class="clearfix"></div>
			<div class="panel panel-info em-hide" id="em-last-open">
				<div class="panel-heading em-hide-heading">
					<h3 class="panel-title"><?php echo JText::_('LAST_OPEN_FILES')?></h3>
				</div>
				<div class="panel-body em-hide-body">
					<div class="list-group">
					</div>
				</div>
			</div>
		</div>

		<div class="col-md-9 main-panel">
			<div id="em-hide-filters" class="em-close-filter" data-toggle="tooltip" data-placement="top" title=<?php echo JText::_('HIDE_FILTERS'); ?>">
				<span class="glyphicon glyphicon-chevron-left"></span>
			</div>
			<div class="navbar navbar-inverse em-menuaction">
				<div class="navbar-header em-menuaction-header">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-inverse-collapse">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<span class="navbar-brand" href="#"><?php echo JText::_('ACTIONS')?></span>
				</div>

			</div>
			<div class="panel panel-default"></div>
		</div>
	</div>
</div>


<div class="modal fade" id="em-modal-actions" style="z-index:99999" tabindex="-1" role="dialog" aria-labelledby="em-modal-actions" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="em-modal-actions-title"><?php echo JText::_('TITLE');?></h4>
      </div>
      <div class="modal-body">

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal"><?php echo JText::_('CANCEL')?></button>
        <button type="button" class="btn btn-success"><?php echo JText::_('OK');?></button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="em-modal-form" style="z-index:99999" tabindex="-1" role="dialog" aria-labelledby="em-modal-actions" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="em-modal-actions-title"><?php echo JText::_('LOADING');?></h4>
      </div>
      <div class="modal-body">
      	<img src="<?php echo JURI::base(); ?>media/com_emundus/images/icones/loader-line.gif">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal"><?php echo JText::_('CANCEL')?></button>
      </div>
    </div>
  </div>
</div>



<script type="text/javascript">
	var itemId = '<?php echo @$this->itemId;?>';
	var cfnum = '<?php echo @$this->cfnum;?>';
	var filterName = '<?php echo JText::_('FILTER_NAME');?>';
	var filterEmpty = '<?php echo JText::_('ALERT_EMPTY_FILTER');?>';
	var nodelete = '<?php echo JText::_('CAN_NOT_DELETE_FILTER');?>';
	var jtextArray = ['<?php echo JText::_('ENTER_COMMENT')?>',
	                  '<?php echo JText::_('TITLE')?>',
	                  '<?php echo JText::_('COMMENT_SENT')?>'];
	var loading = '<?php echo JURI::base().'media/com_emundus/images/icones/loader.gif'?>';
	var loadingLine = '<?php echo JURI::base().'media/com_emundus/images/icones/loader-line.gif'?>';
	$(document).ready(function()
	{
		$('.chzn-select').chosen({width:'75%'});
       // reloadData();
		refreshFilter();
       // search();
        reloadActions();
	})

    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
</script>
