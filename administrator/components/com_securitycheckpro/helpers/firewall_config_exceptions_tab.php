	<div class="card mb-12">
        <div class="card-body">
            <div class="row">
                <div class="col-xl-12 mb-12">
                    <div class="card-header text-white bg-primary">
						<?php echo JText::_('PLG_SECURITYCHECKPRO_EXCEPTIONS_LABEL') ?>
                    </div>
                    <div class="card-body">
                        <h4 class="card-title"><?php echo JText::_('COM_SECURITYCHECKPRO_EXCLUDE_EXCEPTIONS_IF_VULNERABLE_LABEL'); ?></h4>
                        <div class="controls">
							<?php echo booleanlist('exclude_exceptions_if_vulnerable', array(), $this->exclude_exceptions_if_vulnerable) ?>
                        </div>
                        <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('COM_SECURITYCHECKPRO_EXCLUDE_EXCEPTIONS_IF_VULNERABLE_DESCRIPTION') ?></small></footer></blockquote>
						
						<?php echo JHtml::_('bootstrap.startTabSet', 'ExceptionsTabs'); ?>
							<?php echo JHtml::_('bootstrap.addTab', 'ExceptionsTabs', 'li_header_referer_tab', JText::_('PLG_SECURITYCHECKPRO_CHECK_HEADER_REFERER_LABEL')); ?>
								<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_CHECK_HEADER_REFERER_LABEL'); ?></h4>
                                <div class="controls">
									<?php echo booleanlist('check_header_referer', array(), $this->check_header_referer) ?>
                                </div>
                                <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_CHECK_HEADER_REFERER_DESCRIPTION') ?></small></footer></blockquote>   
							<?php echo JHtml::_('bootstrap.endTab'); ?>
							
							<?php echo JHtml::_('bootstrap.addTab', 'ExceptionsTabs', 'li_base64_tab', JText::_('PLG_SECURITYCHECKPRO_CHECK_BASE64_LABEL')); ?>
								<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_CHECK_BASE64_LABEL'); ?></h4>
                                <div class="controls">
									<?php echo booleanlist('check_base_64', array(), $this->check_base_64) ?>
                                </div>
                                <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_CHECK_BASE64_DESCRIPTION') ?></small></footer></blockquote>
                                                                                                                
								<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_BASE64_EXCEPTIONS_LABEL'); ?></h4>
                                <div class="controls">
                                    <textarea cols="35" rows="3" name="base64_exceptions" class="firewall-config-style"><?php echo $this->base64_exceptions ?></textarea>                                
                                </div>
                                <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_BASE64_EXCEPTIONS_DESCRIPTION') ?></small></footer></blockquote> 
							<?php echo JHtml::_('bootstrap.endTab'); ?>
							
							<?php echo JHtml::_('bootstrap.addTab', 'ExceptionsTabs', 'li_xss_tab', JText::_('XSS')); ?>
								<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_STRIP_ALL_TAGS_LABEL'); ?></h4>
                                <div class="controls" id="strip_all_tags">
									<?php echo booleanlist_js('strip_all_tags', array(), $this->strip_all_tags) ?>
                                </div>
                                <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_STRIP_ALL_TAGS_DESCRIPTION') ?></small></footer></blockquote>
                                                                                                                
                                <div id="tags_to_filter_div">
                                    <h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_TAGS_TO_FILTER_LABEL'); ?></h4>
                                    <div class="controls">
                                        <textarea cols="35" rows="3" name="tags_to_filter" class="firewall-config-style"><?php echo $this->tags_to_filter ?></textarea>                                
                                    </div>
                                    <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_TAGS_TO_FILTER_DESCRIPTION') ?></small></footer></blockquote>
                                </div>
                                                        
                                <h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_STRIP_TAGS_EXCEPTIONS_LABEL'); ?></h4>
                                <div class="controls">
                                    <textarea cols="35" rows="3" name="strip_tags_exceptions" class="firewall-config-style"><?php echo $this->strip_tags_exceptions ?></textarea>
								</div>
                                <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_STRIP_TAGS_EXCEPTIONS_DESCRIPTION') ?></small></footer></blockquote>
							<?php echo JHtml::_('bootstrap.endTab'); ?>
							
							<?php echo JHtml::_('bootstrap.addTab', 'ExceptionsTabs', 'li_sql_tab', JText::_('SQL Injection')); ?>
								<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_DUPLICATE_BACKSLASHES_EXCEPTIONS_LABEL'); ?></h4>
								<div class="controls">
                                    <textarea cols="35" rows="3" name="duplicate_backslashes_exceptions" class="firewall-config-style"><?php echo $this->duplicate_backslashes_exceptions ?></textarea>
								</div>
                                <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_DUPLICATE_BACKSLASHES_EXCEPTIONS_DESCRIPTION') ?></small></footer></blockquote>
                                                                                                         
                                <h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_LINE_COMMENTS_EXCEPTIONS_LABEL'); ?></h4>
                                <div class="controls">
                                    <textarea cols="35" rows="3" name="line_comments_exceptions" class="firewall-config-style"><?php echo $this->line_comments_exceptions ?></textarea>
								</div>
                                <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_LINE_COMMENTS_EXCEPTIONS_DESCRIPTION') ?></small></footer></blockquote>
                                                                                                                
                                <h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_SQL_PATTERN_EXCEPTIONS_LABEL'); ?></h4>
                                <div class="controls">
                                    <textarea cols="35" rows="3" name="sql_pattern_exceptions" class="firewall-config-style"><?php echo $this->sql_pattern_exceptions ?></textarea>
								</div>
                                <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_SQL_PATTERN_EXCEPTIONS_DESCRIPTION') ?></small></footer></blockquote>
                                                                                                                
                                <h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_IF_STATEMENT_EXCEPTIONS_LABEL'); ?></h4>
                                <div class="controls">
                                    <textarea cols="35" rows="3" name="if_statement_exceptions" class="firewall-config-style"><?php echo $this->if_statement_exceptions ?></textarea>
								</div>
                                <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_IF_STATEMENT_EXCEPTIONS_DESCRIPTION') ?></small></footer></blockquote>
                                                                                                                
								<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_USING_INTEGERS_EXCEPTIONS_LABEL'); ?></h4>
                                <div class="controls">
                                    <textarea cols="35" rows="3" name="using_integers_exceptions" class="firewall-config-style"><?php echo $this->using_integers_exceptions ?></textarea>
								</div>
                                <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_USING_INTEGERS_EXCEPTIONS_DESCRIPTION') ?></small></footer></blockquote>
                                                                                                               
                                <h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_ESCAPE_STRINGS_EXCEPTIONS_LABEL'); ?></h4>
                                <div class="controls">
									<textarea cols="35" rows="3" name="escape_strings_exceptions" class="firewall-config-style"><?php echo $this->escape_strings_exceptions ?></textarea>
								</div>
                                <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_ESCAPE_STRINGS_EXCEPTIONS_DESCRIPTION') ?></small></footer></blockquote>
							<?php echo JHtml::_('bootstrap.endTab'); ?>
							
							<?php echo JHtml::_('bootstrap.addTab', 'ExceptionsTabs', 'li_lfi_tab', JText::_('PLG_SECURITYCHECKPRO_LFI_EXCEPTIONS_LABEL')); ?>
								<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_LFI_EXCEPTIONS_LABEL'); ?></h4>
                                <div class="controls">
                                    <textarea cols="35" rows="3" name="lfi_exceptions" class="firewall-config-style"><?php echo $this->lfi_exceptions ?></textarea>                                
								</div>
                                <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_LFI_EXCEPTIONS_DESCRIPTION') ?></small></footer></blockquote>
							<?php echo JHtml::_('bootstrap.endTab'); ?>
							
							<?php echo JHtml::_('bootstrap.addTab', 'ExceptionsTabs', 'li_secondlevel_tab', JText::_('PLG_SECURITYCHECKPRO_SECOND_LEVEL_EXCEPTIONS_LABEL')); ?>
								<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_SECOND_LEVEL_EXCEPTIONS_LABEL'); ?></h4>
                                <div class="controls">
                                    <textarea cols="35" rows="3" name="second_level_exceptions" class="firewall-config-style"><?php echo $this->second_level_exceptions ?></textarea>                                
                                </div>
                                <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_SECOND_LEVEL_EXCEPTIONS_DESCRIPTION') ?></small></footer></blockquote>
							<?php echo JHtml::_('bootstrap.endTab'); ?>
						
						<?php echo JHtml::_('bootstrap.endTabSet'); ?>                                            
                                                                 
					</div>                                    
                </div>                                    
			</div>
        </div> 
    </div>