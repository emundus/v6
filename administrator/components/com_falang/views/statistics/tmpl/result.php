<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

?>
<div id="resultContent"><?php echo $this->get('htmlResult');?></div>
<script language="javascript" type="text/javascript">
resultDiv = document.getElementById("resultContent");
window.parent.updateResultDiv( resultDiv, 'div' );
<?php
if( $this->get('reload') != '' ) {
	echo "document.location.href='" .$this->get('reload'). "'";
}
?>
</script>
