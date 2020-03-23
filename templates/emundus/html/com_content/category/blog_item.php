<?php
defined('_JEXEC') or die;
$params = &$this->item->params;
$canEdit	= $this->item->params->get('access-edit');
$app	= JFactory::getApplication();
$images  = json_decode($this->item->images);
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
$templateparams = $app->getTemplate(true)->params;
?>
<?php $assocParam = (JLanguageAssociations::isEnabled() && $params->get('show_associations'));
$info = $params->get('info_block_position', 0);?>
<?php if ($this->item->state == 0 || strtotime($this->item->publish_up) > strtotime(JFactory::getDate()) || ((strtotime($this->item->publish_down) < strtotime(JFactory::getDate())) && $this->item->publish_down != JFactory::getDbo()->getNullDate())) : ?>
<div class="system-unpublished">
<?php endif; ?>
<?php if ($params->get('show_title')) : ?>
<div class="ttr_post_inner_box">
<h2 class="ttr_post_title">
<?php if ($params->get('link_titles') && $params->get('access-view')) : ?>
<a href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($this->item->slug, $this->item->catid)); ?>">
<?php echo $this->escape($this->item->title); ?></a>
<?php else : ?>
<?php echo $this->escape($this->item->title); ?>
<?php endif; ?>
</h2>
</div>
<?php endif; ?>
<div class="ttr_article">
<?php $useDefList = ($params->get('show_modify_date') || $params->get('show_publish_date') || $params->get('show_create_date') || $params->get('show_hits') || $params->get('show_category') || $params->get('show_parent_category') || $params->get('show_author') || $params->get('show_print_icon') || $params->get('show_email_icon') || $assocParam || $canEdit); ?>
<?php if ($info == 0 || $info == 2) : ?>
<div class="postedon">
<?php if($useDefList): ?>
	<?php echo JLayoutHelper::render('joomla.content.info_block.block', array('item' => $this->item, 'params' => $params, 'position' => 'above')); ?>
	<?php if ($info == 0 && $params->get('show_tags', 1) && !empty($this->item->tags->itemTags)) : ?>
		<?php echo JLayoutHelper::render('joomla.content.tags', $this->item->tags->itemTags); ?>
	<?php endif; ?>
<?php endif; ?>
	<?php if (($canEdit || $params->get('show_print_icon') || $params->get('show_email_icon')) && $info == 0): ?>
	<?php if ($params->get('show_print_icon')) : ?>
		<?php echo JHtml::_('icon.print_popup', $this->item, $params); ?>
	<?php endif; ?>
	<?php if ($params->get('show_email_icon')) :
		 echo JHtml::_('icon.email', $this->item, $params, array(), true); 
	endif; ?>
	<?php if ($canEdit) :
		 echo JHtml::_('icon.edit', $this->item, $params, array(), true);
	endif; ?>
<?php endif; ?>
</div>
<?php endif; ?>
<div class="postcontent">
<?php echo JLayoutHelper::render('joomla.content.intro_image', $this->item); ?>
<?php if (!$params->get('show_intro')) : ?>
<?php echo $this->item->event->afterDisplayTitle; ?>
<?php endif; ?>
<?php echo $this->item->event->beforeDisplayContent; ?>
<?php echo $this->item->introtext; ?>
<div style="clear:both;"></div>
</div>
	<?php if ($params->get('show_readmore') && $this->item->readmore) :
	if ($params->get('access-view')) :
	$link = JRoute::_(ContentHelperRoute::getArticleRoute($this->item->slug, $this->item->catid, $this->item->language));
else :
	$menu = JFactory::getApplication()->getMenu();
	$active = $menu->getActive();
	$itemId = $active->id;
	$link = new JUri(JRoute::_('index.php?option=com_users&view=login&Itemid=' . $itemId, false));
	$link->setVar('return', base64_encode(ContentHelperRoute::getArticleRoute($this->item->slug, $this->item->catid, $this->item->language)));
endif; ?>
<?php echo JLayoutHelper::render('joomla.content.readmore', array('item' => $this->item, 'params' => $params, 'link' => $link)); ?>
<?php endif; ?>
</div>
<?php if ($this->item->state == 0 || strtotime($this->item->publish_up) > strtotime(JFactory::getDate()) || ((strtotime($this->item->publish_down) < strtotime(JFactory::getDate())) && $this->item->publish_down != JFactory::getDbo()->getNullDate())) : ?>
</div>
<?php endif; ?>
<?php echo $this->item->event->afterDisplayContent; ?>
<?php if ($info == 1 || $info == 2): ?>
<div class="postedon">
<?php if($useDefList): ?>
	<?php echo JLayoutHelper::render('joomla.content.info_block.block', array('item' => $this->item, 'params' => $params, 'position' => 'below')); ?>
	<?php if ($info == 0 && $params->get('show_tags', 1) && !empty($this->item->tags->itemTags)) : ?>
		<?php echo JLayoutHelper::render('joomla.content.tags', $this->item->tags->itemTags); ?>
	<?php endif; ?>
<?php endif; ?>
	<?php if ($canEdit || $params->get('show_print_icon') || $params->get('show_email_icon')): ?>
	<?php if ($params->get('show_print_icon')) : ?>
		<?php echo JHtml::_('icon.print_popup', $this->item, $params); ?>
	<?php endif; ?>
	<?php if ($params->get('show_email_icon')) :
		 echo JHtml::_('icon.email', $this->item, $params, array(), true); 
	endif; ?>
	<?php if ($canEdit) :
		 echo JHtml::_('icon.edit', $this->item, $params, array(), true);
	endif; ?>
<?php endif; ?>
</div>
<?php endif; ?>
