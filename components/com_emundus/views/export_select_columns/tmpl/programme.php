<?php
defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.tooltip');
JHTML::_('behavior.modal');
//JHTML::stylesheet('media/com_emundus/css/emundus.css' );
$document = JFactory::getDocument();
$document->addStyleSheet("media/com_emundus/css/emundus_export_select_columns.css" );
$eMConfig = JComponentHelper::getParams('com_emundus');
$current_user = JFactory::getUser();
$view = JRequest::getVar('v', null, 'GET', 'none',0);
//$view = JRequest::getVar('view', null, 'GET', 'none',0);
$comments = JRequest::getVar('comments', null, 'POST', 'none', 0);
$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
// Starting a session.
$session = JFactory::getSession();
$s_elements = $session->get('s_elements');
$comments = $session->get('comments');

if (!empty($s_elements)) {
	foreach ($s_elements as $s) {
		$t = explode('.', $s);
		$table_name[] = $t[0];
		$element_name[] = $t[1];
	}
}
?>
<style>
    .component-content legend {
         border: 0px;
         padding: 0px;
         margin-left: -6px;
         border-radius: 0px;
         background: inherit;
         border-bottom: 0px;
         margin-bottom: 0;
         line-height: 0;
    }
    .panel-info legend {
        color: #000000;
        font-size: 16px;
    }
    .panel-info.excel {
        min-height: inherit!important;
    }
    .panel-primary {
        border-color: #00316b;
    }
    .panel-primary.excel>.panel-heading {
        background-color: #00316b;
    }
    .panel-heading label {
        color: #FFFFFF!important;
        font-weight: bold!important;
        text-shadow: none ;
        font-size: 18px;
    }
    .label-element {
        color: #000000!important;
        font-weight: normal!important;
    }
    .panel-info>.panel-heading label {
        color: #000000!important;
        font-weight: bold!important;
        text-shadow: none ;
        font-size: 14px;
    }

    .em-element-id, .em-element-title-id {
        width: 120px;
        display: inline-block;
    }

    .em-element-label, .em-element-title-label {
        width: auto;
        display: inline-block;
    }

</style>

	<?php if (count($this->elements) > 0) : ?>
        <div class="em-program-title">
            <h1><?php echo $this->program; ?></h1>
        </div>
        <div id="emundus_elements">
        <?php
            $tbl_tmp='';
		    $grp_tmp='';
        ?>

		<?php foreach ($this->elements as $t) : ?>
			<?php if ($tbl_tmp == '') : ?>
                <?php
                $label = explode("-", $t->table_label);
                $label = $label[1];
                ?>
				<div class="panel panel-primary excel" id="emundus_table_<?php echo$t->table_id; ?>">
                    <div class="panel-heading">
                        <legend>
                            <label for="emundus_checkall_tbl_<?php echo$t->table_id; ?>"><?php echo JText::_($label); ?></label>
                        </legend>
                    </div>

                    <div class="panel-body">
					  <div class="panel panel-info excel" id="emundus_grp_<?php echo$t->group_id; ?>">
						<div class="panel-heading">
                            <legend>
                                <label for="emundus_checkall_grp_'<?php echo $t->group_id; ?>"><?php echo JText::_($t->group_label); ?></label>
                            </legend>
                        </div>

                        <div class="panel-body">
                            <div class="em-element-title">
                                <div class="em-element-title-id">
                                    <p>ID</p>
                                </div>
                                <div class="em-element-title-label">
                                    <p>Label utilisé</p>
                                </div>
                            </div>
            <?php elseif ($t->table_id != $tbl_tmp && $tbl_tmp != '') : ?>
                <?php
                    $label = explode("-", $t->table_label);
                    $label = $label[1];
                ?>
                        </div>
                      </div>
                    </div>
                </div>
                </div>
                <div class="panel panel-primary excel" id="emundus_table_<?php echo$t->table_id; ?>">
                    <div class="panel-heading">
                        <legend>
                            <label for="emundus_checkall_tbl_<?php echo $t->table_id; ?>"><?php echo JText::_($label); ?></label>
                        </legend>
                    </div>

                    <div class="panel-body">
						<div class="panel panel-info excel" id="emundus_grp_<?php echo$t->group_id; ?>'">
							<div class="panel-heading">
                                <legend>
                                    <label for="emundus_checkall_grp_<?php echo$t->group_id; ?>"><?php echo JText::_($t->group_label); ?></label>
                                </legend>
                            </div>
                            <div class="panel-body">
                                <div class="panel-body">
                                    <div class="em-element-title">
                                        <div class="em-element-title-id">
                                            <p></p>
                                        </div>
                                        <div class="em-element-title-label">
                                            <p></p>
                                        </div>
                                    </div>
                                </div>
			<?php else : ?>
				<?php if ($t->group_id != $grp_tmp && $grp_tmp != '') : ?>
						    </div>
                        </div>

                        <div class="panel panel-info excel" id="emundus_grp_<?php echo$t->group_id; ?>">
                            <div class="panel-heading">
                                <legend>
                                    <label for="emundus_checkall_grp_<?php echo$t->group_id; ?>"><?php echo JText::_($t->group_label); ?></label>
                                </legend>
                            </div>
                            <div class="panel-body">
                                <div class="panel-body">
                                    <div class="em-element-title">
                                        <div class="em-element-title-id">
                                            <p></p>
                                        </div>
                                        <div class="em-element-title-label">
                                            <p></p>
                                        </div>
                                    </div>
				<?php endif; ?>
			<?php endif; ?>
                            <div class="em-element">
                                <div class="em-element-id">
                                    <?php echo '${'.$t->id.'}'; ?>
                                </div>
                                <div class="em-element-label">
                                    <?php echo JText::_($t->element_label); ?>
                                </div>
                            </div>
                            <br>

            <?php
                $tbl_tmp=$t->table_id;
                $grp_tmp=$t->group_id;
            ?>
		<?php endforeach; ?>
		                    </div>
                        </div>
                    </div>
                </div>
		    </div>
     <?php else: ?>
        <?php echo JText::_('NO_FORM_DEFINED'); ?>
    <?php endif; ?>

