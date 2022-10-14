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

$toolbar_classname = $this->config->get('front_toolbar_btn_classname', 'hikabtn');
if(empty($toolbar_classname))
	$toolbar_classname = 'hikabtn';

$toolbar_mode = $this->config->get('front_toolbar_icon_mode', 'fa');
if(!in_array($toolbar_mode, array('css', 'fa')))
	$toolbar_mode = 'css';

$data = $this->left;
if(!empty($this->right)) {
	$data[] = '#RIGHT#';
	$data = array_merge($data, $this->right);
}

?>
<div class="hikam_toolbar">
	<div class="hikam_toolbar_btn hikam_btn_32">
<?php

foreach($data as $key => $tool) {
	if($tool === '#RIGHT#') {
		echo '<div class="hikam_toolbar_right">';
		continue;
	}

	if(empty($tool['url']) && !empty($tool['sep'])) {
		echo '<div class="sep"></div>';
		continue;
	}

	$content = '';

	if(!empty($tool['fa']) && $toolbar_mode == 'fa') {
		$content .= '<i class="fa '.$tool['fa'].'"></i>';
		$tool['icon'] = null;
	}
	if(!empty($tool['icon'])) {
		$content .= '<span class="btnIcon iconM-32-'.$tool['icon'].'"></span>';
	}

	if(!empty($tool['name'])) { $content .= '<span class="btnName">' . $tool['name'] . '</span>'; }

	if(!empty($tool['url'])) {
		if(empty($tool['popup'])) {
			if(empty($tool['linkattribs']))
				echo '<a class="'.$toolbar_classname.'" href="'.$tool['url'].'">';
			else
				echo '<a class="'.$toolbar_classname.'" href="'.$tool['url'].'" '.$tool['linkattribs'].'>';
			echo $content . '</a>';
		} else {
			$attr = $this->popup->getAttr(@$tool['linkattribs'], 'hikabtn');
			echo $this->popup->display(
				$content,
				@$tool['name'],
				$tool['url'],
				$tool['popup']['id'],
				$tool['popup']['width'],
				$tool['popup']['height'],
				$attr, '', 'link'
			);
		}
	} else {
		echo '<div class="'.$toolbar_classname.'">'.$content.'</div>';
	}
	unset($content);
}
if(!empty($this->right))
	echo '</div>';

?>
		<div style="clear:both"></div>
	</div>
</div>
