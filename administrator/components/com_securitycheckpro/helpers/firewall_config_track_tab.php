<?php if ($this->plugin_trackactions_installed) { ?>
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-xl-3 mb-3">
                                                <div class="card-header text-white bg-primary">
                                                    <?php echo JText::_('PLG_TRACKACTIONS_LABEL') ?>
                                                </div>
                                                <div class="card-body">
                                                                                                    
                                                    <h4 class="card-title"><?php echo JText::_('PLG_SYSTEM_TRACKACTIONS_LOG_DELETE_PERIOD'); ?></h4>
                                                    <div class="controls">
                                                        <input type="number" size="3" maxlength="3" id="delete_period" name="delete_period" value="<?php echo $this->delete_period ?>" title="" />    
                                                    </div>
                                                    <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SYSTEM_TRACKACTIONS_LOG_DELETE_PERIOD_DESC') ?></small></footer></blockquote>
                                                                                                        
                                                    <h4 class="card-title"><?php echo JText::_('PLG_SYSTEM_TRACKACTIONS_IP_LOGGING'); ?></h4>
                                                    <div class="controls">
                                                        <?php echo booleanlist('ip_logging', array(), $this->ip_logging) ?>
                                                    </div>
                                                    <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SYSTEM_TRACKACTIONS_IP_LOGGING_DESC') ?></small></footer></blockquote>
                                                </div>
                                            </div>
                                            
                                            <div class="col-xl-3 mb-3">
                                                <div class="card-header text-white bg-primary">
                                                    <?php echo JText::_('PLG_TRACKACTIONS_LABEL') ?>
                                                </div>
                                                <div class="card-body">
                                                    <h4 class="card-title"><?php echo JText::_('PLG_SYSTEM_TRACKACTIONS_LOG_EXTENSIONS'); ?></h4>
                                                    <div class="controls">
                                                        <?php
                                                        // Listamos todas las extensiones 
                                                        $db = JFactory::getDBO();
                                                        $query = "SELECT extension from #__securitycheckpro_trackactions_extensions" ;            
                                                        $db->setQuery($query);
                                                        $groups = $db->loadRowList();    
                                                        foreach ($groups as $key=>$value) {                                
                                                            $options_trackactions[] = JHTML::_('select.option', $value[0], $value[0]);                            
                                                        }
                                                        echo JHTML::_('select.genericlist', $options_trackactions, 'loggable_extensions[]', 'class="chosen-select-no-single" multiple="multiple"', 'value', 'text',  $this->loggable_extensions);                                                 
                                                        ?>                    
                                                    </div>    
                                                    <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SYSTEM_TRACKACTIONS_LOG_EXTENSIONS_DESC') ?></small></footer></blockquote>                                                
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
        <?php } else { ?>
                                    <div class="alert alert-warning centrado">
            <?php echo JText::_('COM_SECURITYCHECKPRO_TRACKACTIONS_NOT_INSTALLED'); ?>    
                                    </div>    
        <?php }  ?>