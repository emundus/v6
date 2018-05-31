<?php
/**
* Securitycheck Pro ControlCenter View para el Componente Securitycheckpro
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// Protect from unauthorized access
defined('_JEXEC') or die();
JSession::checkToken( 'get' ) or die( 'Invalid Token' );

function booleanlist( $name, $attribs = null, $selected = null, $id=false )
{
	$arr = array(
		JHTML::_('select.option',  '0', JText::_( 'COM_SECURITYCHECKPRO_NO' ) ),
		JHTML::_('select.option',  '1', JText::_( 'COM_SECURITYCHECKPRO_YES' ) )
	);
	return JHTML::_('select.genericlist',  $arr, $name, 'class="chosen-select-no-single"', 'value', 'text', (int) $selected, $id );
}

// Cargamos el comportamiento modal para mostrar las ventanas para exportar
JHtml::_('behavior.modal');

// Eliminamos la carga de las librerías mootools
$document = JFactory::getDocument();
$rootPath = JURI::root(true);
$arrHead = $document->getHeadData();
unset($arrHead['scripts'][$rootPath.'/media/system/js/mootools-core.js']);
unset($arrHead['scripts'][$rootPath.'/media/system/js/mootools-more.js']);
$document->setHeadData($arrHead);

?>

  <!-- Bootstrap core JavaScript -->
<script src="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/jquery/jquery.min.js"></script>

<?php 
// Cargamos el contenido común
include JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/common.php';
?>

<?php 
if ( version_compare(JVERSION, '3.9.50', 'lt') ) {
?>
<!-- Bootstrap core CSS-->
<link href="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/bootstrap/css/bootstrap.css" rel="stylesheet">
<?php } else { ?>
<link href="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/bootstrap/css/bootstrap_j4.css" rel="stylesheet">
<?php } ?>
<!-- Custom fonts for this template-->
<link href="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/font-awesome/css/fontawesome.css" rel="stylesheet" type="text/css">
<link href="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/font-awesome/css/fa-solid.css" rel="stylesheet" type="text/css">
 <!-- Custom styles for this template-->
<link href="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/css/sb-admin.css" rel="stylesheet">
 <!-- Chosen styles -->
<link href="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/chosen/chosen.css" rel="stylesheet">

<script type="text/javascript" language="javascript">

var Password = {
 
  _pattern : /[a-zA-Z0-9]/, 
  
  _getRandomByte : function()
  {
    // http://caniuse.com/#feat=getrandomvalues
    if(window.crypto && window.crypto.getRandomValues) 
    {
      var result = new Uint8Array(1);
      window.crypto.getRandomValues(result);
      return result[0];
    }
    else if(window.msCrypto && window.msCrypto.getRandomValues) 
    {
      var result = new Uint8Array(1);
      window.msCrypto.getRandomValues(result);
      return result[0];
    }
    else
    {
      return Math.floor(Math.random() * 256);
    }
  },
  
  generate : function(length)
  {
    return Array.apply(null, {'length': length})
      .map(function()
      {
        var result;
        while(true) 
        {
          result = String.fromCharCode(this._getRandomByte());
          if(this._pattern.test(result))
          {
            return result;
          }
        }        
      }, this)
      .join('');  
  }    
    
};
</script>


<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro&view=controlcenter&'. JSession::getFormToken() .'=1');?>" method="post" name="adminForm" id="adminForm">

		<?php 
		// Cargamos la navegación
		include JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/navigation.php';
		?>
						
			<!-- Breadcrumb-->
			<ol class="breadcrumb">
				<li class="breadcrumb-item">
					<a href="<?php echo JRoute::_( 'index.php?option=com_securitycheckpro' );?>"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_DASHBOARD'); ?></a>
				</li>
				<li class="breadcrumb-item active"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_CONTROLCENTER_TEXT'); ?></li>
			</ol>
			
			<?php if ( function_exists('openssl_encrypt') ) { ?>
			
			<div class="card mb-6">
				<div class="card-body">
					<div class="row">
						<div class="alert alert-info">
							<?php echo JText::_('COM_SECURITYCHECKPRO_CONTROLCENTER_EXPLAIN'); ?>	
						</div>
		
						<div class="col-xl-6 mb-6">
							<div class="card-header text-white bg-primary">
								<?php echo JText::_('COM_SECURITYCHECKPRO_GLOBAL_PARAMETERS') ?>
							</div>
							<div class="card-body">
								<h4 class="card-title"><?php echo JText::_('COM_SECURITYCHECKPRO_CONTROLCENTER_ENABLED_TEXT'); ?></h4>										
								<div class="controls controls-row">
									<?php echo booleanlist('control_center_enabled', array(), $this->control_center_enabled) ?>				
								</div>
								<blockquote><p class="text-info"><small><?php echo JText::_('COM_SECURITYCHECKPRO_CONTROLCENTER_ENABLED_EXPLAIN') ?></small></p></blockquote>										
								<h4 class="card-title"><?php echo JText::_('COM_SECURITYCHECKPRO_SECRET_KEY_TEXT'); ?></h4>
								<?php 
									if ( version_compare(JVERSION, '3.9.50', 'lt') ) {										
								?>
									<div class="input-prepend">
										<input class="input-xlarge" type="text" name="secret_key" id="secret_key" value="<?php echo $this->secret_key ?>" readonly>
									</div>
													
									<div class="input-append">
										<input type='button' class="btn btn-primary" value='<?php echo JText::_('COM_SECURITYCHECKPRO_HIDE_BACKEND_GENERATE_KEY_TEXT') ?>' onclick='document.getElementById("secret_key").value = Password.generate(32)' />
									</div>
								<?php } else {	?>
									<div class="input-group">
									  <input type="text" class="form-control input-xlarge" name="secret_key" id="secret_key" value="<?php echo $this->secret_key ?>" readonly>
									  <span class="input-group-btn">
										<button class="btn btn-primary" type="button" onclick='document.getElementById("secret_key").value = Password.generate(32)'><?php echo JText::_('COM_SECURITYCHECKPRO_HIDE_BACKEND_GENERATE_KEY_TEXT') ?></button>
									  </span>
									</div>																								
								<?php } ?>								
								<blockquote><p class="text-info"><small><?php echo JText::_('COM_SECURITYCHECKPRO_SECRET_KEY_EXPLAIN') ?></small></p></blockquote>
							</div>
						</div>
					</div>					
				</div>
			</div>
			<?php } else { ?>
				<div class="alert alert-error">
					<?php echo JText::_('COM_SECURITYCHECKPRO_CONTROLCENTER_ENCRYPT_LIBRARY_NOT_PRESENT'); ?>	
				</div>

			<?php } ?>
		</div>
</div>

<!-- Bootstrap core JavaScript -->
<script src="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/popper/popper.min.js"></script>
<script src="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/bootstrap/js/bootstrap.min.js"></script>
<!-- Custom scripts for all pages -->
<script src="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/js/sb-admin.js"></script> 
<!-- Chosen scripts -->
<script src="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/chosen/chosen.jquery.js"></script>
<script src="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/chosen/init.js"></script>

<input type="hidden" name="option" value="com_securitycheckpro" />
<input type="hidden" name="view" value="controlcenter" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="task" id="task" value="save" />
<input type="hidden" name="controller" value="controlcenter" />	
</form>