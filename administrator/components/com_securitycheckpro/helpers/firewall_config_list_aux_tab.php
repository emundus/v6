<!-- Aux Lists tab -->
    <div class="card mb-3">    
        <div class="card-header">
			<i class="fapro fa-bars"></i>
            <?php echo JText::_('COM_SECURITYCHECKPRO_LISTS_MANAGEMENT'); ?>
        </div>
        <div class="card-body">
            <div id="filter-bar" class="btn-toolbar" class="margin-left-10">
                <div class="filter-search btn-group pull-left">
                    <input type="text" name="filter_lists_search" placeholder="<?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.lists_search')); ?>" title="<?php echo JText::_('JSEARCH_FILTER'); ?>" />
                </div>
                <div class="btn-group pull-left" class="margin-left-10">
                    <button class="btn tip" type="submit" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
                    <button id="search_button" class="btn tip" type="button" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
                </div>
				<div id="pagination" class="margin-bottom-30">
                <?php	            
                if (isset($this->pagination) ) {                                    
                    ?>
                    <div class="btn-group pull-right">
                        <label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
						<?php echo $this->pagination->getLimitBox(); ?>
                    </div>            
                    <?php echo $this->pagination->getListFooter(); ?>            
                    <?php
					}
					?>
                </div>
            </div>
            <br/>
			
			<?php echo JHtml::_('bootstrap.startTabSet', 'ListsTabs'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'ListsTabs', 'li_blacklist_tab', JText::_('COM_SECURITYCHECKPRO_BLACKLIST')); ?>
					<?php include JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'firewall_config_list_aux_blacklist_tab.php'; ?>
				<?php echo JHtml::_('bootstrap.endTab'); ?>
				
				<?php echo JHtml::_('bootstrap.addTab', 'ListsTabs', 'li_dynamic_blacklist_tab', JText::_('COM_SECURITYCHECKPRO_DYNAMIC_BLACKLIST')); ?>
					<?php include JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'firewall_config_list_aux_dynamic_tab.php'; ?>
				<?php echo JHtml::_('bootstrap.endTab'); ?>
				
				<?php echo JHtml::_('bootstrap.addTab', 'ListsTabs', 'li_whitelist_tab', JText::_('COM_SECURITYCHECKPRO_WHITELIST')); ?>
					<?php include JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'firewall_config_list_aux_whitelist_tab.php'; ?>
				<?php echo JHtml::_('bootstrap.endTab'); ?>			
			
			<?php echo JHtml::_('bootstrap.endTabSet'); ?>
			
        </div>
    </div>    