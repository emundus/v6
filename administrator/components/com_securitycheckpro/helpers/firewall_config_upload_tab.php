<div class="card mb-3">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-xl-3 mb-3">
                                            <div class="card-header text-white bg-primary">
                                                <?php echo JText::_('COM_SECURITYCHECKPRO_GLOBAL_PARAMETERS') ?>
                                            </div>
                                            <div class="card-body">
                                                <h4 class="card-title"><?php echo JText::_('COM_SECURITYCHECKPRO_UPLOADSCANNER_LABEL'); ?></h4>
                                                <div class="controls">
                <?php echo booleanlist('upload_scanner_enabled', array(), $this->upload_scanner_enabled) ?>
                                                </div>
                                                <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('COM_SECURITYCHECKPRO_UPLOADSCANNER_DESCRIPTION') ?></small></footer></blockquote>
                                                                                                
                                                <h4 class="card-title"><?php echo JText::_('COM_SECURITYCHECKPRO_UPLOADSCANNER_CHECK_MULTIPLE_EXTENSIONS_LABEL'); ?></h4>
                                                <div class="controls">
                <?php echo booleanlist('check_multiple_extensions', array(), $this->check_multiple_extensions) ?>
                                                </div>
                                                <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('COM_SECURITYCHECKPRO_UPLOADSCANNER_CHECK_MULTIPLE_EXTENSIONS_DESCRIPTION') ?></small></footer></blockquote>
                                                
                                            </div>
                                        </div>
                                        
                                        <div class="col-xl-3 mb-3">
                                            <div class="card-header text-white bg-primary">
                                                <?php echo JText::_('COM_SECURITYCHECKPRO_GLOBAL_PARAMETERS') ?>
                                            </div>
                                            <div class="card-body">											
												<h4 class="card-title"><?php echo JText::_('COM_SECURITYCHECKPRO_UPLOADSCANNER_MIMETYPES_BLACKLIST_LABEL'); ?></h4>
                                                <div class="controls">
                                                    <textarea cols="35" rows="3" name="mimetypes_blacklist" class="mimetypes-blacklist"><?php echo $this->mimetypes_blacklist ?></textarea>                                
                                                </div>
                                                <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('COM_SECURITYCHECKPRO_UPLOADSCANNER_MIMETYPES_BLACKLIST_DESCRIPTION') ?></small></footer></blockquote>
												
												
                                                <h4 class="card-title"><?php echo JText::_('COM_SECURITYCHECKPRO_UPLOADSCANNER_EXTENSIONS_BLACKLIST_LABEL'); ?></h4>
                                                <div class="controls">
                                                    <textarea cols="35" rows="3" name="extensions_blacklist" class="extensions-blacklist"><?php echo $this->extensions_blacklist ?></textarea>                                
                                                </div>
                                                <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('COM_SECURITYCHECKPRO_UPLOADSCANNER_EXTENSIONS_BLACKLIST_DESCRIPTION') ?></small></footer></blockquote>
                                                                                                
                                                <h4 class="card-title"><?php echo JText::_('COM_SECURITYCHECKPRO_UPLOADSCANNER_DELETE_FILES_LABEL'); ?></h4>
                                                <div class="controls">
                <?php echo booleanlist('delete_files', array(), $this->delete_files) ?>
                                                </div>
                                                <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('COM_SECURITYCHECKPRO_UPLOADSCANNER_DELETE_FILES_DESCRIPTION') ?></small></footer></blockquote>
                                                
                                            </div>
                                        </div>
                                        
                                        <div class="col-xl-3 mb-3">
                                            <div class="card-header text-white bg-primary">
                                                <?php echo JText::_('COM_SECURITYCHECKPRO_GLOBAL_PARAMETERS') ?>
                                            </div>
                                            <div class="card-body">
                                                <h4 class="card-title"><?php echo JText::_('COM_SECURITYCHECKPRO_UPLOADSCANNER_ACTIONS_LABEL'); ?></h4>
                                                <div class="controls">
                <?php echo actions('actions_upload_scanner', array(), $this->actions_upload_scanner) ?>
                                                </div>
                                                <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('COM_SECURITYCHECKPRO_UPLOADSCANNER_ACTIONS_DESCRIPTION') ?></small></footer></blockquote>
                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>