<?php
/**
 * Created by PhpStorm.
 * User: yoan
 * Date: 22/05/14
 * Time: 10:16
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
JHTML::_('behavior.tooltip');
JFactory::getDocument()->addStyleSheet('https://use.fontawesome.com/releases/v5.4.1/css/all.css');


//var_dump($this->users);
?>

<div class="container-fluid">
<div class="row">
<div class="col-md-3 side-panel">
	<div class="panel panel-info em-containerFilter" id="em-user-filters">
		<div class="panel-heading em-containerFilter-heading" style="height:55px">
			<div>
				<h3 class="panel-title"><?php echo JText::_('FILTERS')?></h3> &ensp;&ensp;
			</div>
            <div class="buttons" style="float:right; margin-top:0px">
                <label for="clear-search">
                    <img src="<?php echo JURI::base()?>/media/com_emundus/images/icones/clear-filters.png" style="width: 25px;filter: invert(1);"/>
                </label>

                <input type="button" style="display: none" id="clear-search" title="<?php echo JText::_('CLEAR_BTN');?>"/>
            </div>
		</div>
		<div class="panel-body em-containerFilter-body">

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
	<div id="em-hide-filters" class="em-close-filter">
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
		<!--
		<div class="navbar-collapse collapse navbar-inverse-collapse">
			<ul class="nav navbar-nav">
				<?php foreach ($this->actions as $key => $action): ?>
					<li class="dropdown">
						<a href="#" class="em-dropdown" id="em-menu-<?php echo $key?>" nba="<?php echo count($action) ?>">
							<?php echo JTEXT::_(strtoupper($key)) ?><b class="caret"></b>
						</a>
						<ul class="dropdown-menu" id="em-dp-<?php echo $key?>" role="menu" aria-labelledby="em-menu-<?php echo $key ?>">
							<?php foreach ($action as $k => $a):?>
								<li class="em-actions" id="<?php echo $a['id'] ?>" multi="<?php echo $a['multi'] ?>" <?php if($a['multi'] != -1){echo 'style="display:none;"';}?>>
									<a href="#"><?php echo JText::_(strtoupper($a['label'])) ?></a>
								</li>
							<?php endforeach; ?>
						</ul>
					</li>
				<?php endforeach;?>
			</ul>
		</div> -->
	</div>
	<div class="panel panel-default">
	</div>
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

	var itemId = "<?php echo $this->itemId;?>";
	var filterName = "<?php echo JText::_('FILTER_NAME');?>";
	var filterEmpty = "<?php echo JText::_('ALERT_EMPTY_FILTER');?>";
	var nodelete = "<?php echo JText::_('CAN_NOT_DELETE_FILTER');?>";
	var jtextArray = ["<?php echo JText::_('ENTER_COMMENT')?>",
	                  "<?php echo JText::_('TITLE')?>",
	                  "<?php echo JText::_('COMMENT_SENT')?>"];
	var loading = "<?php echo JURI::base().'media/com_emundus/images/icones/loader.gif'?>";
	var loadingLine = "<?php echo JURI::base().'media/com_emundus/images/icones/loader-line.gif'?>";
	$(document).ready(function() {
          $('#rt-mainbody-surround').children().addClass('mainemundus');
          $('#rt-main').children().addClass('mainemundus');
          $('#rt-main').children().children().addClass('mainemundus');

          $('.chzn-select').chosen({width:'75%'});
          $('body').on('hidden.bs.modal', '.modal', function() {
              var itemid = getCookie("application_itemid");
              $('#em-appli-menu .list-group-item#'+itemid).trigger('click');
              $(this).removeData('bs.modal');
              $('#em-modal-form .modal-content').html('<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h4 class="modal-title" id="em-modal-actions-title"><?php echo JText::_('LOADING');?></h4></div><div class="modal-body"><img src="<?php echo JURI::base(); ?>media/com_emundus/images/icones/loader-line.gif"></div><div class="modal-footer"><button type="button" class="btn btn-danger" data-dismiss="modal"><?php echo JText::_('CANCEL')?></button></div>');
          });
      });
	reloadActions('files');
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>
