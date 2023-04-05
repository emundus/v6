	<div class="card mb-6">
        <div class="card-body">
            <div class="row">
                <div class="col-xl-6 mb-6">
                    <div class="card-header text-white bg-primary">
                        <?php echo JText::_('PLG_SECURITYCHECKPRO_REDIRECTION_LABEL') ?>
                    </div>
                    <div class="card-body">
                        <h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_REDIRECT_AFTER_ATTACK_LABEL'); ?></h4>
                        <div class="controls">
							<?php echo booleanlist('redirect_after_attack', array(), $this->redirect_after_attack) ?>
                        </div>
                        <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_REDIRECT_AFTER_ATTACK_DESCRIPTION') ?></small></footer></blockquote>
                                                                                               
                        <h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_REDIRECT_LABEL'); ?></h4>
                        <div class="controls" id="redirect_options">
							<?php echo redirectionlist('redirect_options', array(), $this->redirect_options) ?>
                        </div>
						<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_REDIRECT_DESCRIPTION') ?></small></footer></blockquote>
                                                                                                                                                
                        <h4 class="card-title"><?php echo JText::_('COM_SECURITYCHECKPRO_REDIRECTION_URL_TEXT'); ?></h4>
                        <?php 
                        if (version_compare(JVERSION, '3.20', 'lt') ) {                                        
                        ?>
                        <div class="controls controls-row">
                            <div class="input-prepend">
                                <span class="add-on" class="background-8EBBFF"><?php echo $site_url ?></span>
                                <input class="input-large" type="text" id="redirect_url" name="redirect_url" value="<?php echo $this->redirect_url?>" placeholder="<?php echo $this->redirect_url ?>">
                            </div>                        
                        </div>
                        <?php } else {    ?>
                        <div class="input-group">
                            <span class="input-group-text" class="background-8EBBFF"><?php echo $site_url ?></span>
                            <input type="text" class="form-control" id="redirect_url" name="redirect_url" value="<?php echo $this->redirect_url?>" placeholder="<?php echo $this->redirect_url ?>">
                        </div>                                            
                        <?php } ?>
                        <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('COM_SECURITYCHECKPRO_REDIRECTION_URL_EXPLAIN') ?></small></footer></blockquote>
                                                                                               
                        <div class="control-group">
                            <h4 class="card-title"><?php echo JText::_('COM_SECURITYCHECKPRO_EDITOR_TEXT'); ?></h4>
                             <blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('COM_SECURITYCHECKPRO_EDITOR_EXPLAIN') ?></small></footer></blockquote>                                                    
								<?php 
								// IMPORT EDITOR CLASS
								jimport('joomla.html.editor');

								// GET EDITOR SELECTED IN GLOBAL SETTINGS
								$config = JFactory::getConfig();
								$global_editor = $config->get('editor');

								// GET USER'S DEFAULT EDITOR
								$user_editor = JFactory::getUser()->getParam("editor");

								if($user_editor && $user_editor !== 'JEditor') {
									$selected_editor = $user_editor;
								} else {
									$selected_editor = $global_editor;
								}

								// INSTANTIATE THE EDITOR
								$editor = JEditor::getInstance($selected_editor);
																	
								// SET EDITOR PARAMS
								$params = array( 'smilies'=> '0' ,
								'style'  => '1' ,
								'layer'  => '0' ,
								'table'  => '0' ,
								'clear_entities'=>'0'
								);

								// DISPLAY THE EDITOR (name, html, width, height, columns, rows, bottom buttons, id, asset, author, params)
								echo $editor->display('custom_code', $this->custom_code, '600', '200', '10', '10', true, null, null, null, $params);
								?>                                                    
                        </div>
                    </div>
				</div>                                        
            </div>
        </div> 
    </div>