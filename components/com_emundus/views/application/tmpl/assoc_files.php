<?php
JFactory::getSession()->set('application_layout', 'assoc_files');

if (!empty((array)$this->assoc_files)) :

   foreach ($this->assoc_files->camps as $camp):

   	if($camp->published==1):  // files of published campaigns
    ?>
		<div class = "panel panel-primary em-container-assocFiles <?php if($this->assoc_files->fnumInfos['fnum'] == $camp->fnum){echo 'current-file';} ?>">
			<div class = "panel-heading em-container-assocFiles-heading">
				<div class = "panel-title">
					<a style="text-decoration: none" data-toggle="collapse" data-parent="#accordion" href="#<?php echo $camp->fnum?>-collapse" onclick="openAccordion('<?php echo $camp->fnum?>')">
                        <div class="em-flex-row em-flex-space-between em-mb-8">
                            <h6>
		                        <?php echo $camp->label?>
                            </h6>
                            <span id="<?php echo $camp->fnum?>-icon" class="material-icons-outlined">expand_less</span>
                        </div>
                        <div class="em-flex-row em-flex-space-between em-mb-8">
                            <span class="label label-<?php echo $camp->class?>"> <?php echo $camp->step_value?></span>
                            <div class="pull-right btn-group">
                                <?php if (EmundusHelperAccess::asAccessAction(1, 'd', $this->_user->id, $camp->fnum)): ?>
                                    <button id="em-delete-files" class = "btn btn-danger btn-xs pull-right" title="<?php echo JText::_('COM_EMUNDUS_APPLICATION_DELETE_APPLICATION_FILE')?>">
                                        <span class="material-icons-outlined">delete_outline</span>
                                    </button>
                                <?php endif; ?>
                                <?php if (EmundusHelperAccess::asAccessAction(1, 'r', $this->_user->id, $camp->fnum)): ?>
                                    <button id="em-see-files" class = "btn btn-info btn-xs pull-right" title="<?php echo JText::_('COM_EMUNDUS_APPLICATION_OPEN_APPLICATION_FILE')?>">
                                        <span class="material-icons-outlined">visibility</span>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

						<div class="clearfix"></div>
					</a>
				</div>
			</div>
			<div id="<?php echo $camp->fnum?>-collapse-item" class="in panel-collapse collapse <?php if($this->assoc_files->fnumInfos['fnum'] == $camp->fnum){echo 'current-file';} ?>" style="display: none">
				<div class="panel-body em-container-assocFiles-body em-mt-8">
					<div>
						<ul>
                            <li class="em-mb-4"><span><strong><?php echo JText::_('COM_EMUNDUS_ACADEMIC_YEAR')?> : </strong><?php echo $camp->year?></span></li>
                            <li class="em-mb-4"><span><strong><?php echo JText::_('COM_EMUNDUS_PROGRAMME')?> : </strong><?php echo $camp->training?></span></li>
							<li class="em-mb-4"><span><strong><?php echo JText::_('COM_EMUNDUS_FILE_F_NUM')?> : </strong><?php echo $camp->fnum?></span></li>

							<?php if($camp->submitted==1):?>
								<li class="em-mb-4"><span><strong><?php echo JText::_('COM_EMUNDUS_APPLICATION_DATE_SUBMITTED')?> : </strong><?php echo JFactory::getDate($camp->date_submitted)->format(JText::_('DATE_FORMAT_LC2'));?></span></li>
							<?php endif;?>
						</ul>

					</div>
				</div>
			</div>
		</div>
    <?php endif; ?>
    <?php endforeach; ?>

        <div class="unpublished_campaigns_tab em-flex-row em-flex-space-between" onclick="displayUnpublishedCampaignsContainer()">
            <p><?php echo JText::_('COM_EMUNDUS_APPLICATION_UNPUBLISHED_CAMPAIGNS');?></p>
            <span id="unpublished_campaigns_icon" class="material-icons-outlined">expand_less</span>
        </div>

      <?php  foreach ($this->assoc_files->camps as $camp):
         if($camp->published !=1):  // files of unpublished campaigns
            ?>

            <div id="unpublished_campaigns_container" class="unpublished_campaigns_container" style="display: none;">
                <div class = "panel panel-primary em-container-assocFiles <?php if($this->assoc_files->fnumInfos['fnum'] == $camp->fnum){echo 'current-file';} ?>">
                    <div class = "panel-heading em-container-assocFiles-heading">
                        <div class = "panel-title">
                            <a style="text-decoration: none" data-toggle="collapse" data-parent="#accordion" href="#<?php echo $camp->fnum?>-collapse" onclick="openAccordion('<?php echo $camp->fnum?>')">
                                <div class="em-flex-row em-flex-space-between em-mb-8">
                                    <h6>
                                        <?php echo $camp->label?>
                                    </h6>
                                    <span id="<?php echo $camp->fnum?>-icon" class="material-icons-outlined">expand_less</span>
                                </div>
                                <div class="em-flex-row em-flex-space-between em-mb-8">
                                    <span class="label label-<?php echo $camp->class?>"> <?php echo $camp->step_value?></span>
                                    <div class="pull-right btn-group">
                                        <?php if (EmundusHelperAccess::asAccessAction(1, 'd', $this->_user->id, $camp->fnum)): ?>
                                            <button id="em-delete-files" class = "btn btn-danger btn-xs pull-right" title="<?php echo JText::_('COM_EMUNDUS_APPLICATION_DELETE_APPLICATION_FILE')?>">
                                                <span class="material-icons-outlined">delete_outline</span>
                                            </button>
                                        <?php endif; ?>
                                        <?php if (EmundusHelperAccess::asAccessAction(1, 'r', $this->_user->id, $camp->fnum)): ?>
                                            <button id="em-see-files" class = "btn btn-info btn-xs pull-right" title="<?php echo JText::_('COM_EMUNDUS_APPLICATION_OPEN_APPLICATION_FILE')?>">
                                                <span class="material-icons-outlined">visibility</span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="clearfix"></div>
                            </a>
                        </div>
                    </div>
                    <div id="<?php echo $camp->fnum?>-collapse-item" class="in panel-collapse collapse <?php if($this->assoc_files->fnumInfos['fnum'] == $camp->fnum){echo 'current-file';} ?>" style="display: none">
                        <div class="panel-body em-container-assocFiles-body em-mt-8">
                            <div>
                                <ul>
                                    <li class="em-mb-4"><span><strong><?php echo JText::_('COM_EMUNDUS_ACADEMIC_YEAR')?> : </strong><?php echo $camp->year?></span></li>
                                    <li class="em-mb-4"><span><strong><?php echo JText::_('COM_EMUNDUS_PROGRAMME')?> : </strong><?php echo $camp->training?></span></li>
                                    <li class="em-mb-4"><span><strong><?php echo JText::_('COM_EMUNDUS_FILE_F_NUM')?> : </strong><?php echo $camp->fnum?></span></li>

                                    <?php if($camp->submitted==1):?>
                                        <li class="em-mb-4"><span><strong><?php echo JText::_('COM_EMUNDUS_APPLICATION_DATE_SUBMITTED')?> : </strong><?php echo JFactory::getDate($camp->date_submitted)->format(JText::_('DATE_FORMAT_LC2'));?></span></li>
                                    <?php endif;?>
                                </ul>

                            </div>
                        </div>
                    </div>
                </div>
             <?php endif; ?>
	    <?php endforeach;?>
<?php endif; ?>

<script>
    function openAccordion(fnum){
        let block = document.getElementById(fnum+'-collapse-item');
        let icon = document.getElementById(fnum+'-icon');

        block.toggle(200);

        if (block.style.display === 'none') {
            icon.style.transform = 'rotate(0deg)';
        } else {
            icon.style.transform = 'rotate(-180deg)';
        }
    }

    function displayUnpublishedCampaignsContainer(){
        let block = document.getElementById('unpublished_campaigns_container');
        let icon = document.getElementById('unpublished_campaigns_icon');

        block.toggle(200);

        if (block.style.display === 'none') {
            icon.style.transform = 'rotate(0deg)';
        } else {
            icon.style.transform = 'rotate(-180deg)';
        }
    }
</script>
