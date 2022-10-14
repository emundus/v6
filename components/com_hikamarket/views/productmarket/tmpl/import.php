<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

if(empty($this->import)) {
?>
<div id="hikamarket_import_zone">
<?php
	$options = array(
		'upload' => true,
		'upload_base_url' => 'index.php?option=com_hikamarket&ctrl=upload',
		'toolbar' => null,
		'text' => JText::_('HIKAM_IMPORT_UPLOAD_ZONE'),
		'uploader' => array('product', 'import_file'),
		'vars' => array(

		),
		'ajax' => true
	);

	echo $this->uploaderType->displayFileSingle('hikamarket_import_file', '', $options);
?>
</div>
<?php
}
