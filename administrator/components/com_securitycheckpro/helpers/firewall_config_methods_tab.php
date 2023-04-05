<!-- Methods -->
    <div class="card mb-6">
        <div class="card-body">
            <div class="row">
                <div class="col-xl-6 mb-6">
                    <div class="card-header text-white bg-primary">
						<?php echo JText::_('PLG_SECURITYCHECKPRO_METHODS_INSPECTED_LABEL') ?>
					</div>
					<div class="card-body">
						<h4 class="card-title"><?php echo JText::_('PLG_SECURITYCHECKPRO_METHODS_LABEL'); ?></h4>
						<div class="controls">
							<?php echo methodslist('methods', array(), $this->methods); ?>
						</div>
						<blockquote class="blockquote" id="block"><footer class="blockquote-footer"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_METHODS_INSPECTED_DESCRIPTION') ?></small></footer></blockquote>                                                
					</div>
				</div>                                        
			</div>
        </div> 
    </div>