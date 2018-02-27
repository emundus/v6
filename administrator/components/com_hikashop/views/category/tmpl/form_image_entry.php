<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2018 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php $image = $this->imageHelper->getThumbnail(@$this->params->file_path, array(100, 100), array('default' => true)); ?>
<div>
	<a href="#delete" class="deleteImg" onclick="return window.hkUploaderList['hikashop_category_image'].delImage(this);"><img src="<?php echo HIKASHOP_IMAGES; ?>cancel.png" border="0"></a>
	<div class="hikashop_image">
		<img src="<?php echo $image->url; ?>" alt="<?php echo $image->filename; ?>"/>
	</div><input type="hidden" name="data[category][category_image]" value="<?php echo @$this->params->file_id;?>"/>
</div>
