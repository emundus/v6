<?php
JFactory::getSession()->set('application_layout', 'assoc_files');

if (!empty((array)$this->assoc_files)) : ?>
	<?php foreach ($this->assoc_files->camps as $camp):?>
		<div class = "panel <?php if($this->assoc_files->fnumInfos['fnum'] == $camp->fnum){echo 'panel-primary';}else{echo 'panel-default';} ?> em-container-assocFiles">
			<div class = "panel-heading em-container-assocFiles-heading">
				<div class = "panel-title">
					<a data-toggle="collapse" data-parent="#accordion" href="#<?php echo $camp->fnum?>-collapse">
						<span class="label label-<?php echo $camp->class?>"> <?php echo $camp->step_value?></span>
						<div class="pull-right btn-group">
							<?php if (EmundusHelperAccess::asAccessAction(1, 'd', $this->_user->id, $camp->fnum)): ?>
								<button id="em-delete-files" class = "btn btn-danger btn-xs pull-right" title="<?php echo JText::_('COM_EMUNDUS_APPLICATION_DELETE_APPLICATION_FILE')?>">
									<span class="material-icons">delete_outline</span>
								</button>
							<?php endif; ?>
							<?php if (EmundusHelperAccess::asAccessAction(1, 'r', $this->_user->id, $camp->fnum)): ?>
								<button id="em-see-files" class = "btn btn-info btn-xs pull-right" title="<?php echo JText::_('COM_EMUNDUS_APPLICATION_OPEN_APPLICATION_FILE')?>">
									<span class="material-icons">visibility</span>
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
			<div id="<?php echo $camp->fnum?>-collapse" class="panel-collapse collapse <?php if($this->assoc_files->fnumInfos['fnum'] == $camp->fnum){echo 'in';} ?>">
				<div class="panel-body em-container-assocFiles-body">
					<div>
						<ul>
							<li><span><strong><?php echo JText::_('COM_EMUNDUS_ACADEMIC_YEAR')?> :</strong> <?php echo $camp->year?></span></li>
							<li><span><?php echo $camp->training?></span></li>
							<li><span><strong><?php echo JText::_('COM_EMUNDUS_FILE_F_NUM')?> :</strong> <?php echo $camp->fnum?></span></li>

							<?php if($camp->submitted==1):?>
								<li><span><strong><?php echo JText::_('COM_EMUNDUS_APPLICATION_SUBMITTED')?> :</strong> <?php echo JText::_('JYES');?></span></li>
								<li><span><strong><?php echo JText::_('COM_EMUNDUS_APPLICATION_DATE_SUBMITTED')?> :</strong> <?php echo JFactory::getDate($camp->date_submitted)->format(JText::_('DATE_FORMAT_LC2'));?></span></li>
							<?php else:?>
								<li><span><strong><?php echo JText::_('COM_EMUNDUS_APPLICATION_SUBMITTED')?> :</strong> <?php echo JText::_('JNO');?></span></li>
							<?php endif;?>
						</ul>

					</div>
				</div>
			</div>
		</div>
	<?php endforeach;?>
<?php endif; ?>
