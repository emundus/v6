	<div class="box-content">
		<div class="alert alert-info">
            <p><?php echo JText::_('COM_SECURITYCHECKPRO_DYNAMIC_BLACKLIST_DESCRIPTION'); ?></p>
        </div>
        <?php
            if (count($this->dynamic_blacklist_elements)>0 ) {                                                                            
        ?>
        <div id="dynamic_blacklist_buttons">
			<div class="btn-group pull-right" class="margin-bottom-5">
                <button class="btn btn-danger" id="deleteip_dynamic_blacklist_button" href="#">
                    <i class="icon-trash icon-white"> </i>
                    <?php echo JText::_('COM_SECURITYCHECKPRO_DELETE'); ?>
                </button>
            </div>                        
        </div>
        <?php } ?>
        <table id="dynamic_blacklist_table" class="table table-striped table-bordered bootstrap-datatable datatable">
            <thead>
                <tr>
                    <th class="center"><?php echo JText::_("Ip"); ?></th>                                                                
					<th class="center">
                        <input type="checkbox" id="toggle_dynamic_blacklist" name="toggle_dynamic_blacklist" value="" />
                    </th>
                </tr>
            </thead>   
            <tbody>
            <?php
                if (count($this->dynamic_blacklist_elements)>0 ) {
					$k = 0;
                    foreach ($this->dynamic_blacklist_elements as &$row_dynamic) {                 
            ?>
                <tr>
                    <td class="center"><?php echo $row_dynamic; ?></td>                                                                     
                    <td class="center">
                        <?php echo JHtml::_('grid.id', $k, $row_dynamic, '', 'dynamic_blacklist_table'); ?>
                    </td>
                </tr>
            <?php 
				$k++;
				} 
            }    ?>
			</tbody>
        </table>
    </div>       