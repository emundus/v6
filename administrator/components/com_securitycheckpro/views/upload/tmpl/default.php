<?php 

/*
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted access');
JRequest::checkToken( 'get' ) or die( 'Invalid Token' );

// Add style declaration
$media_url = "media/com_securitycheckpro/stylesheets/cpanelui.css";
JHTML::stylesheet($media_url);

$bootstrap_css = "media/com_securitycheckpro/stylesheets/bootstrap.min.css";
JHTML::stylesheet($bootstrap_css);

JHTML::_( 'behavior.framework', true );
?>



<form enctype="multipart/form-data" method="post" name="adminForm" id="adminForm" class="form-horizontal">

<div class="securitycheck-bootstrap">

	<div class="alert alert-warn">
		<?php echo JText::_('COM_SECURITYCHECKPRO_IMPORT_SETTINGS_ALERT'); ?>
	</div>
	
	<fieldset class="uploadform">
		<legend><?php echo JText::_('COM_SECURITYCHECKPRO_IMPORT_SETTINGS'); ?></legend>
		<div class="control-group">
			<label for="install_package" class="control-label"><?php echo JText::_('COM_SECURITYCHECKPRO_SELECT_EXPORTED_FILE'); ?></label>
			<div class="controls">
				<input class="input_box" id="file_to_import" name="file_to_import" type="file" size="57" />
			</div>
			</div>
			<div class="form-actions">
				<input class="btn btn-primary" type="button" value="<?php echo JText::_('COM_SECURITYCHECKPRO_UPLOAD_AND_IMPORT'); ?>" onclick="Joomla.submitbutton('read_file')" />
		</div>
	</fieldset>
	
</div>

<input type="hidden" name="option" value="com_securitycheckpro" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="controller" value="upload" />

</form>