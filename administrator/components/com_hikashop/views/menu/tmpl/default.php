<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><nav class="hk-navbar hk-navbar-default">
	<div class="hk-container-fluid">
		<ul class="hk-nav hk-navbar-nav">
<?php
$config = hikashop_config();
foreach($this->menus as $menu) {
	$task = !empty($menu['task']) ? $menu['task'] : 'view';
	$icon = !empty($menu['icon']) ? '<i class="'.$menu['icon'].'"></i> ' : '';

	$dropdown = false;
	if(!empty($menu['children'])) {
		foreach($menu['children'] as &$child) {
			$childTask = !empty($child['task']) ? $child['task'] : 'view';
			if(!empty($child['acl']) && !hikashop_isAllowed($config->get('acl_'.$child['acl'].'_'.$childTask, 'all'))) {
				$child = false;
				continue;
			}
			if(!empty($child['url']))
				$dropdown = true;
			if(isset($child['active']) && $child['active']) {
				$menu['active'] = true;
			}
		}
		unset($child);
	}

	if(!empty($menu['acl']) && !hikashop_isAllowed($config->get('acl_'.$menu['acl'].'_'.$task, 'all')) && !$dropdown)
		continue;

	$classes = !empty($menu['active']) ? ' active' : '';
	if(!isset($menu['options'])) $menu['options'] = '';

	if(!$dropdown) {
?>
			<li class="<?php echo trim($classes); ?>"><a href="<?php echo $menu['url']; ?>" <?php echo $menu['options']; ?>><?php echo $icon . $menu['name']; ?></a></li>
<?php
		continue;
	}

?>
			<li class="hkdropdown<?php echo $classes; ?>">
				<a href="#" class="hkdropdown-toggle" data-toggle="hkdropdown" role="button" aria-haspopup="true" aria-expanded="false"><?php echo $icon . $menu['name']; ?> <span class="caret"></span></a>
				<ul class="hkdropdown-menu">
<?php
	foreach($menu['children'] as $k => $child) {
		if(empty($child))
			continue;
		$childTask = !empty($child['task']) ? $child['task'] : 'view';
		$childIcon = !empty($child['icon']) ? '<i class="'.$child['icon'].'"></i> ' : '';
		if(!isset($child['options'])) $child['options'] = '';
		$classes = !empty($child['active']) ? ' active' : '';

		if(!empty($child['url'])) {
			echo '<li><a class="'.trim($classes).'" href="'.$child['url'].'" '.$child['options'].'>' . $childIcon . $child['name'] . '</a></li>';
		} elseif(!empty($menu['children'][$k-1]) && !empty($menu['children'][$k-1]['url'])) {
			echo '<li role="separator" class="divider" '.$child['options'].'></li>';
		}
	}
?>
				</ul>
			</li>
<?php
}
?>
		</ul>
	</div>
</nav>
