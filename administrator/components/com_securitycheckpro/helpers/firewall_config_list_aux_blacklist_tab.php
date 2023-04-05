<!-- Blacklist Import file modal -->
    <div class="modal fade" id="select_blacklist_file_to_upload" tabindex="-1" role="dialog" aria-labelledby="blacklistfileuploadLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header alert alert-info">
					<h2 class="modal-title" id="blacklistfileuploadLabel"><?php echo JText::_('COM_SECURITYCHECKPRO_IMPORT_SETTINGS'); ?></h2>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
				<div class="modal-body">    
					<div id="div_messages">
						<label class="red"><?php echo JText::_('COM_SECURITYCHECKPRO_OVERWRITE_WARNING'); ?></label>
						<h5><?php echo JText::_('COM_SECURITYCHECKPRO_SELECT_EXPORTED_FILE'); ?></h5>                        
						<div class="controls">
							<input class="input_box" id="file_to_import_blacklist" name="file_to_import_blacklist" type="file" size="57" />
						</div>
					</div>                                                                                
				</div>
				<div class="modal-footer" id="div_boton_subida_blacklist">
					<input class="btn btn-primary" id="upload_import_button" type="button" value="<?php echo JText::_('COM_SECURITYCHECKPRO_UPLOAD_AND_IMPORT'); ?>" />								
					<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo JText::_('COM_SECURITYCHECKPRO_CLOSE'); ?></button>
				</div>              
			</div>
        </div>
    </div>
    <div class="box-content">
		<div class="alert alert-info">
            <p><?php echo JText::_('COM_SECURITYCHECKPRO_BLACKLIST_DESCRIPTION'); ?></p>
        </div>    
        <div class="alert alert-info">
            <a class="close" href="#" data-dismiss="alert">×</a>
            <p><?php echo JText::_('COM_SECURITYCHECKPRO_ADD_IP_HEADER'); ?></p>
            <ol>
				<b><?php echo JText::_('COM_SECURITYCHECKPRO_IPV4'); ?></b>                            
                <li>
					<b><?php echo JText::_('COM_SECURITYCHECKPRO_ADD_IP_SINGLE'); ?></b>, i.e.<var><?php echo $current_ip; ?></var>
                </li>
                <li>
                    <b><?php echo JText::_('COM_SECURITYCHECKPRO_ADD_IP_RANGE'); ?></b>, i.e.<var><?php echo $range_example; ?></var>
                </li>
                <li>
                    <b><?php echo JText::_('COM_SECURITYCHECKPRO_CIDR'); ?></b>, i.e.<var><?php echo $cidr_v4_example; ?></var>                                
                </li>                            
            </ol>
            <ol>
                <b><?php echo JText::_('COM_SECURITYCHECKPRO_IPV6'); ?></b>                            
                <li>
                    <b><?php echo JText::_('COM_SECURITYCHECKPRO_ADD_IP_SINGLE'); ?></b><?php echo ", i.e. 2001:13d0::1"; ?>
                </li>
                <li>
                    <b><?php echo JText::_('COM_SECURITYCHECKPRO_CIDR'); ?></b>    <?php echo ", i.e. 2001:13d0::/29"; ?>
                </li>                            
            </ol>
            <p>
				<?php echo JText::_('COM_SECURITYCHECKPRO_ADD_IP_CURRENT'); ?>
				<code><?php echo $current_ip; ?></code>    
				<button type="button" id="add_ip_whitelist_button2" class="btn btn-sm btn-success" href="#">
					<?php echo JText::_('COM_SECURITYCHECKPRO_ADD_TO_WHITELIST'); ?>
				</button>
            </p>
        </div>
        <div id="blacklist_buttons">
            <div class="btn-group pull-left">
				<input type="text" name="blacklist_add_ip" placeholder="<?php echo JText::_('COM_SECURITYCHECKPRO_NEW_IP'); ?>" id="blacklist_add_ip" value="" title="<?php echo JText::_('COM_SECURITYCHECKPRO_NEW_IP_LABEL'); ?>" />
            </div>
            <div class="btn-group pull-left" class="margin-left-10 margin-bottom-20">
                <button class="btn btn-success" id="add_ip_blacklist_button" href="#">
                    <i class="fapro fa-plus-octagon"> </i>
                    <?php echo JText::_('COM_SECURITYCHECKPRO_ADD'); ?>
                </button>
            </div>
            <div class="btn-group pull-left" class="margin-left-10">
                <a href="#select_blacklist_file_to_upload" id="select_blacklist_file_to_upload" role="button" class="btn btn-secondary" data-toggle="modal"><i class="icon-upload"></i><?php echo JText::_('COM_SECURITYCHECKPRO_IMPORT_IPS'); ?></a>                                
            </div>
            <div class="btn-group pull-left" class="margin-left-10">
                <button class="btn btn-info" id="export_blacklist_button" href="#">
                    <i class="icon-new icon-white"> </i>
                    <?php echo JText::_('COM_SECURITYCHECKPRO_EXPORT_IPS'); ?>
                </button>
            </div>
            <?php
            if (count($this->blacklist_elements)>0 ) {                                                                            
            ?>
            <div class="btn-group pull-right">
                <button class="btn btn-danger" id="delete_ip_blacklist_button" href="#">
					<i class="icon-trash icon-white"> </i>
					<?php echo JText::_('COM_SECURITYCHECKPRO_DELETE'); ?>
                </button>
            </div>
            <?php } ?>
        </div>
        <table class="table table-striped table-bordered bootstrap-datatable datatable">
            <thead>
                <tr>
                    <th class="center"><?php echo JText::_("Ip"); ?></th>                                                                    
                    <th class="center">
                        <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
                    </th>
                </tr>
            </thead>   
            <tbody>
            <?php
			if (count($this->blacklist_elements)>0 ) {
                $k = 0;
                foreach ($this->blacklist_elements as &$row) { 
            ?>
                <tr>
                    <td class="center"><?php echo $row; ?></td>                                                                            
                    <td class="center">
						<?php echo JHtml::_('grid.id', $k, $row); ?>
                    </td>
				</tr>
            <?php 
                $k++;
                } 
            }    ?>
            </tbody>
        </table>
    </div>