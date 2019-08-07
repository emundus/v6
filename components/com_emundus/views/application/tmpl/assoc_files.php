<?php 
JFactory::getSession()->set('application_layout', 'assoc_files');
?>
<?php foreach ($this->synthesis->camps as $camp):?>
	<div class = "panel <?php if($this->synthesis->fnumInfos['fnum'] == $camp->fnum){echo 'panel-primary';}else{echo 'panel-default';} ?> em-container-assocFiles">
		<div class = "panel-heading em-container-assocFiles-heading">
			<div class = "panel-title">
				<a data-toggle="collapse" data-parent="#accordion" href="#<?php echo $camp->fnum?>-collapse">
                    <span class="label label-<?php echo $camp->class?>"> <?php echo $camp->step_value?></span>
                    <div class="pull-right btn-group">
                        <?php if (EmundusHelperAccess::asAccessAction(1, 'd', $this->_user->id, $camp->fnum)): ?>
                            <button id="em-delete-files" class = "btn btn-danger btn-xs pull-right" title="<?php echo JText::_('DELETE_APPLICATION_FILE')?>">
                                <span class="glyphicon glyphicon-trash"></span>
                            </button>
                        <?php endif; ?>
                        <?php if (EmundusHelperAccess::asAccessAction(1, 'r', $this->_user->id, $camp->fnum)): ?>
                            <button id="em-see-files" class = "btn btn-info btn-xs pull-right" title="<?php echo JText::_('OPEN_APPLICATION_FILE')?>">
                                <span class="glyphicon glyphicon-eye-open"></span>
                            </button>
                        <?php endif; ?>
                    </div>
					<h6>
                        <em><?php echo $camp->year?></em> - <strong><?php echo $camp->label?></strong>
					</h6>

					<div class="clearfix"></div>
				</a>
			</div>
		</div>
		<div id="<?php echo $camp->fnum?>-collapse" class="panel-collapse collapse <?php if($this->synthesis->fnumInfos['fnum'] == $camp->fnum){echo 'in';} ?>">
			<div class="panel-body em-container-assocFiles-body">
				<div>
					<ul>
						<li><span><?php echo JText::_('ACADEMIC_YEAR')?> : <?php echo $camp->year?></span></li>
						<li><span><?php echo $camp->training?></span></li>
						<li><span><?php echo JText::_('F_NUM')?> : <?php echo $camp->fnum?></span></li>
						
						<?php if($camp->submitted==1):?>
							<li><span><?php echo JText::_('SUBMITTED')?> : <?php echo JText::_('JYES');?></span></li>
							<li><span><?php echo JText::_('DATE_SUBMITTED')?> : <?php echo JFactory::getDate($camp->date_submitted)->format(JText::_('DATE_FORMAT_LC2'));?></span></li>
						<?php else:?>
							<li><span><?php echo JText::_('SUBMITTED')?> : <?php echo JText::_('JNO');?></span></li>
						<?php endif;?>
					</ul>

				</div>
			</div>
		</div>
	</div>
<?php endforeach;?>
