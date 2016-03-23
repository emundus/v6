<?php
/**
 * @version   $Id: layout.php 27813 2015-03-17 14:12:00Z james $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Rockettheme Gantry Template uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
// no direct access
defined('_JEXEC') or die('Restricted access');
?>
<?php
if (!defined('modRokNavMenuShowItems')) {
	function showItem(&$item, &$menu)
	{
		?>
	<li id="idrops-<?php echo $item->id; ?>" parent_id="idrops-<?php echo $item->parent; ?>" <?php if ($item->hasListItemClasses()) : ?>class="<?php echo $item->getListItemClasses();?>"<?php endif;?> <?php if (isset($item->css_id)): ?>id="<?php echo $item->css_id;?>"<?php endif;?>>
		<?php if ($item->type == 'menuitem') : ?>
		<?php if (count($item->_children) > 0 && $item->parent != 1): ?>
			<small class="menucount"><?php echo count($item->_children); ?></small>
			<?php endif; ?>
		<a <?php if ($item->hasLinkClasses()): ?>class="<?php echo $item->getLinkClasses();?>"<?php endif;?> <?php if ($item->hasLink()): ?>href="<?php echo $item->getLink();?>"<?php endif;?> <?php if (isset($item->nav)): ?>target="<?php echo $item->nav;?>"<?php endif;?> <?php if (isset($item->onclick)): ?>onclick="<?php echo $item->onclick;?>"<?php endif;?><?php if ($item->hasLinkAttribs()): ?> <?php echo $item->getLinkAttribs(); ?><?php endif;?>>
			<?php if (isset($item->image)): ?><img alt="<?php echo $item->alias;?>" src="<?php echo $item->image;?>"/><?php endif; ?>
			<span <?php if ($item->hasSpanClasses()): ?>class="<?php echo $item->getSpanClasses();?>"<?php endif; ?>><?php echo $item->title;?></span>
		</a>
		<?php elseif ($item->type == 'separator') : ?>
		<?php if (count($item->_children) > 0 && $item->parent != 1): ?>
			<small class="menucount small"><?php echo count($item->_children); ?></small>
			<?php endif; ?>
		<span <?php if ($item->hasSpanClasses()): ?>class="<?php echo $item->getSpanClasses();?>"<?php endif; ?>><?php echo $item->title;?></span>
		<?php endif; ?>
		<?php if ($item->hasChildren()): ?>
		<ul>

			<?php
			// force the parent menu item to appear
			$cls      = explode(" ", $item->getListItemClasses());
			$isActive = (in_array('active', $cls));

			if ($item->parent != 1) :
				?>
				<li class="subnav">
					<a href="#" parent_id="idrops-<?php echo $item->parent; ?>" class="item backmenu"><span>Back</span></a>
					<a href="#close" class="item closemenu"><span>Close</span></a>
					<span class="clear"></span>
				</li>
				<?php endif; ?>
			<li class="root-sub<?php echo ($isActive) ? ' active' : ''; ?>"><?php if ($item->type == 'menuitem') : ?>
				<?php if (count($item->_children) > 0 && (!$item->parent && $item->parent != 1)): ?>
					<small class="menucount"><?php echo count($item->_children); ?></small>
					<?php endif; ?>
				<a <?php if ($item->hasLinkClasses()): ?>class="<?php echo $item->getLinkClasses();?>"<?php endif;?> <?php if ($item->hasLink()): ?>href="<?php echo $item->getLink();?>"<?php endif;?> <?php if (isset($item->nav)): ?>target="<?php echo $item->nav;?>"<?php endif;?> <?php if (isset($item->onclick)): ?>onclick="<?php echo $item->onclick;?>"<?php endif;?><?php if ($item->hasLinkAttribs()): ?> <?php echo $item->getLinkAttribs(); ?><?php endif;?>>
					<?php if (isset($item->image)): ?><img alt="<?php echo $item->alias;?>" src="<?php echo $item->image;?>"/><?php endif; ?>
					<span <?php if ($item->hasSpanClasses()): ?>class="<?php echo $item->getSpanClasses();?>"<?php endif; ?>><?php echo $item->title;?></span>
				</a>
				<?php elseif ($item->type == 'separator') : ?>
				<span <?php if ($item->hasSpanClasses()): ?>class="<?php echo $item->getSpanClasses();?>"<?php endif; ?>><?php echo $item->title;?></span>
				<?php endif; ?>

				<?php foreach ($item->getChildren() as $child) : ?>
					<?php showItem($child, $menu); ?>
					<?php endforeach; ?>

		</ul>
		<?php endif; ?>

	</li>
	<?php
	}

	define('modRokNavMenuShowItems', true);
}
?>
<ul class="menu" <?php if ($menu->getParameter('tag_id') != null): ?>id="<?php echo $menu->getParameter('tag_id');?>"<?php endif;?>>
	<?php foreach ($menu->getChildren() as $item) : ?>
	<?php showItem($item, $menu); ?>
	<?php endforeach; ?>
</ul>
