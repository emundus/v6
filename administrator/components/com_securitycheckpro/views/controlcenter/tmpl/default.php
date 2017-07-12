<?php
/**
* Securitycheck Pro ControlCenter View para el Componente Securitycheckpro
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// Protect from unauthorized access
defined('_JEXEC') or die();
JRequest::checkToken( 'get' ) or die( 'Invalid Token' );

function booleanlist( $name, $attribs = null, $selected = null, $id=false )
{
	$arr = array(
		JHTML::_('select.option',  '0', JText::_( 'COM_SECURITYCHECKPRO_NO' ) ),
		JHTML::_('select.option',  '1', JText::_( 'COM_SECURITYCHECKPRO_YES' ) )
	);
	return JHTML::_('select.genericlist',  $arr, $name, $attribs, 'value', 'text', (int) $selected, $id );
}

JHTML::_( 'behavior.framework' );

// Add style declaration
$media_url = "media/com_securitycheckpro/stylesheets/cpanelui.css";
JHTML::stylesheet($media_url);

$bootstrap_css = "media/com_securitycheckpro/stylesheets/bootstrap.min.css";
JHTML::stylesheet($bootstrap_css);

JHtml::_('formbehavior.chosen', 'select');

$site_url = JURI::base();
?>

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


<div class="securitycheck-bootstrap">

<?php if ( function_exists('openssl_encrypt') ) { ?>

<form action="index.php" name="adminForm" id="adminForm" method="post" class="form form-horizontal">
	<input type="hidden" name="option" value="com_securitycheckpro" />
	<input type="hidden" name="view" value="controlcenter" />
	<input type="hidden" name="boxchecked" value="1" />
	<input type="hidden" name="task" id="task" value="save" />
	<input type="hidden" name="controller" value="controlcenter" />
	<?php echo JHTML::_( 'form.token' ); ?>
	
	<fieldset>
		<legend><?php echo JText::_('COM_SECURITYCHECKPRO_CONTROLCENTER_TEXT') ?></legend>
		
		<div class="alert alert-info">
			<?php echo JText::_('COM_SECURITYCHECKPRO_CONTROLCENTER_EXPLAIN'); ?>	
		</div>
		
		<div class="control-group">
			<label for="control_center_enabled" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_CONTROLCENTER_ENABLED_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_CONTROLCENTER_ENABLED_TEXT'); ?></label>
			<div class="controls controls-row">
				<?php echo booleanlist('control_center_enabled', array(), $this->control_center_enabled) ?>				
			</div>
			<blockquote><p class="text-info"><small><?php echo JText::_('COM_SECURITYCHECKPRO_CONTROLCENTER_ENABLED_EXPLAIN') ?></small></p></blockquote>
		</div>
		
		<div class="control-group">
			<label for="secret_key" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_SECRET_KEY_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_SECRET_KEY_TEXT'); ?></label>
				
				<div class="input-prepend">
					<input class="input-xlarge" type="text" name="secret_key" id="secret_key" value="<?php echo $this->secret_key ?>" readonly>
				</div>
								
				<div class="input-append">
					<input type='button' class="btn btn-primary" value='<?php echo JText::_('COM_SECURITYCHECKPRO_HIDE_BACKEND_GENERATE_KEY_TEXT') ?>' onclick='document.getElementById("secret_key").value = Password.generate(32)' />
				</div>
				<div class="controls controls-row">
				</div>
			<blockquote><p class="text-info"><small><?php echo JText::_('COM_SECURITYCHECKPRO_SECRET_KEY_EXPLAIN') ?></small></p></blockquote>
		</div>
	</fieldset>
</form>

<?php } else { ?>
	<div class="alert alert-error">
		<?php echo JText::_('COM_SECURITYCHECKPRO_CONTROLCENTER_ENCRYPT_LIBRARY_NOT_PRESENT'); ?>	
	</div>

<?php } ?>

</div>