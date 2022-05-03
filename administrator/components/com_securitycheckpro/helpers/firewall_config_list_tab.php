<!-- Lists -->
    <div class="card mb-3">                                            
        <div class="card-body">
            <div class="row">
                <div class="col-xl-3 mb-3">
                    <div class="card-header text-white bg-primary">
						<?php echo JText::_('PLG_SECURITYCHECKPRO_DYNAMIC_BLACKLIST_LABEL') ?>
                    </div>
                    <div class="card-body">
                        <h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_DYNAMIC_BLACKLIST_LABEL'); ?></h4>
                        <div class="controls">													
							<?php echo booleanlist('dynamic_blacklist', array(), $this->dynamic_blacklist) ?>
                        </div>
                        <blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_DYNAMIC_BLACKLIST_DESCRIPTION') ?></small></p></blockquote>
                        <h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_DYNAMIC_BLACKLIST_TIME_LABEL'); ?></h4>
                        <div class="controls">
							<input type="number" size="5" maxlength="5" id="dynamic_blacklist_time" name="dynamic_blacklist_time" value="<?php echo $this->dynamic_blacklist_time ?>" title="" />        
                        </div>
                        <blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_DYNAMIC_BLACKLIST_TIME_DESCRIPTION') ?></small></p></blockquote>
						<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_DYNAMIC_BLACKLIST_COUNTER_LABEL'); ?></h4>
                        <div class="controls">
							<input type="number" size="3" maxlength="3" id="dynamic_blacklist_counter" name="dynamic_blacklist_counter" value="<?php echo $this->dynamic_blacklist_counter ?>" title="" />        
                        </div>
                        <blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_DYNAMIC_BLACKLIST_COUNTER_DESCRIPTION') ?></small></p></blockquote>
					</div> 
				<!-- End col -->
                </div>
                <div class="col-xl-3 mb-3">
                    <div class="card-header text-white bg-primary">
						<?php echo JText::_('PLG_SECURITYCHECKPRO_BLACKLIST_LABEL') ?>
                    </div>
                    <div class="card-body">
                        <h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_BLACKLIST_EMAIL_LABEL'); ?></h4>
                        <div class="controls">
							<?php echo booleanlist('blacklist_email', array(), $this->blacklist_email) ?>
                        </div>
                        <blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_BLACKLIST_EMAIL_LABEL') ?></small></p></blockquote>
					</div>
				<!-- End col -->
                </div>                                            
                <div class="col-xl-3 mb-3">
					<div class="card-header text-white bg-primary">
						<?php echo JText::_('COM_SECURITYCHECKPRO_GLOBAL_PARAMETERS') ?>
                    </div>
                    <div class="card-body">
                        <h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_PRIORITY_LABEL'); ?></h4>
                        <label for="priority" class="control-label" title="<?php echo JText::_('First'); ?>"><?php echo JText::_('First'); ?></label>
                        <div class="controls">
							<?php echo prioritylist('priority1', array(), $this->priority1) ?>
                        </div>
                        <label for="priority" class="control-label" title="<?php echo JText::_('Second'); ?>"><?php echo JText::_('Second'); ?></label>
                        <div class="controls">
							<?php echo prioritylist('priority2', array(), $this->priority2) ?>
                        </div>
                        <label for="priority" class="control-label" title="<?php echo JText::_('Third'); ?>"><?php echo JText::_('Third'); ?></label>
                        <div class="controls">
							<?php echo prioritylist('priority3', array(), $this->priority3) ?>
                        </div>                                                        
                        <blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_PRIORITY_LABEL') ?></small></p></blockquote>                                                    
                    </div>
				<!-- End col -->
                </div>
			<!-- End row -->
            </div>
			<?php include JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'firewall_config_list_aux_tab.php'; ?>
		<!-- End card-body -->
        </div> 		
	<!-- End card -->
    </div>       