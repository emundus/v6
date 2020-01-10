<?php
defined('_JEXEC') or die;
$version = new JVersion();
jimport('joomla.version');
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
$params = $this->item->params;
$canEdit	= $this->item->params->get('access-edit');
$user= JFactory::getUser();
$app	= JFactory::getApplication();
$images  = json_decode($this->item->images);
$urls    = json_decode($this->item->urls);
$info    = $params->get('info_block_position', 0);
$templateparams = $app->getTemplate(true)->params;
$assocParam = (JLanguageAssociations::isEnabled() && $params->get('show_associations'));
JHtml::_('behavior.caption');
?>
<meta itemprop="inLanguage" content="<?php echo ($this->item->language === '*') ? JFactory::getConfig()->get('language') : $this->item->language; ?>"/>
<?php if ($this->params->get('show_page_heading', 1)) : ?>
<h1>
<?php echo $this->escape($this->params->get('page_heading')); ?>
</h1>
<?php endif; ?>
<?php if (!empty($this->item->pagination) && $this->item->pagination && !$this->item->paginationposition && $this->item->paginationrelative)
	{
	echo $this->item->pagination;
}
?>
<?php $useDefList = ($params->get('show_modify_date') || $params->get('show_publish_date') || $params->get('show_create_date') || $params->get('show_hits') || $params->get('show_category') || $params->get('show_parent_category') || $params->get('show_author') || $assocParam || $canEdit); ?>
<article class="ttr_post<?php echo $this->pageclass_sfx; ?> list">
<div class="ttr_post_content_inner">
<?php if ($params->get('show_title')) : ?>
<div class="ttr_post_inner_box">
<h2 class="ttr_post_title">
<?php if ($params->get('link_titles') && !empty($this->item->readmore_link)) : ?>
<a href="<?php echo $this->item->readmore_link; ?>">
<?php echo $this->escape($this->item->title); ?></a>
<?php else : ?>
<?php echo $this->escape($this->item->title); ?>
<?php endif; ?>
</h2>
</div>
<?php endif; ?>
<?php if ($this->item->state == 0) : ?>
<span class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span>
<?php endif; ?>
<?php if (strtotime($this->item->publish_up) > strtotime(JFactory::getDate())) : ?>
<span class="label label-warning"><?php echo JText::_('JNOTPUBLISHEDYET'); ?></span>
<?php endif; ?>
<?php if ((strtotime($this->item->publish_down) < strtotime(JFactory::getDate())) && $this->item->publish_down != JFactory::getDbo()->getNullDate()) : ?>
<span class="label label-warning"><?php echo JText::_('JEXPIRED'); ?></span>
<?php endif; ?>
<div class="ttr_article">
<?php if ($info == 0 || $info == 2) : ?>
	<div class="postedon">
		<?php if ($useDefList): ?>
			<?php echo JLayoutHelper::render('joomla.content.info_block.block', array('item' => $this->item, 'params' => $params, 'position' => 'above')); ?>
		<?php endif; ?>
		<?php if ($info == 0) : ?>
			<?php if ($canEdit || $params->get('show_print_icon') || $params->get('show_email_icon')) : ?>
				<?php if ($params->get('show_print_icon')) : ?>
					<?php if(!$this->print): ?>
						<?php echo JHtml::_('icon.print_popup', $this->item, $params); ?>
					<?php else: ?>
						<?php echo JHtml::_('icon.print_screen', $this->item, $params); ?>
					<?php endif; ?> 
				<?php endif; ?> 
				<?php if ($params->get('show_email_icon')) :
		 			echo JHtml::_('icon.email', $this->item, $params, array(), true);
				endif; ?>
				<?php if ($canEdit) :
					echo JHtml::_('icon.edit', $this->item, $params, array(), true);
				endif; ?> 
			<?php endif; ?>
		<?php endif; ?>
	</div>
<?php endif; ?>
<?php echo $this->item->event->afterDisplayTitle; ?>
<?php if ($info == 0 && $params->get('show_tags', 1) && !empty($this->item->tags->itemTags)) : ?>
<?php $this->item->tagLayout = new JLayoutFile('joomla.content.tags'); ?>
<?php echo $this->item->tagLayout->render($this->item->tags->itemTags); ?>
<?php endif; ?>
<?php echo $this->item->event->beforeDisplayContent; ?>
<?php if (isset($urls) && ((!empty($urls->urls_position) && ($urls->urls_position == '0')) || ($params->get('urls_position') == '0' && empty($urls->urls_position))) || (empty($urls->urls_position) && (!$params->get('urls_position')))) : ?>
<?php echo $this->loadTemplate('links'); ?>
<?php endif; ?>
<div class="postcontent">
<?php if ($params->get('access-view')) : ?>
<?php echo JLayoutHelper::render('joomla.content.full_image', $this->item); ?>
<?php
	if (!empty($this->item->pagination) && $this->item->pagination && !$this->item->paginationposition && !$this->item->paginationrelative) :
echo $this->item->pagination;
endif; ?>
<?php
if (!empty($this->item->pagination) && $this->item->pagination && !$this->item->paginationposition && !$this->item->paginationrelative):
echo $this->item->pagination;
endif;
?>
<?php if (isset ($this->item->toc)) :
echo $this->item->toc;
endif; ?>
<?php echo $this->item->text; ?>
<div style="clear:both;"></div>
</div>
<?php if ($info == 1 || $info == 2) :?>
	<div class="postedon">
	<?php if ($useDefList) : ?>
		<?php echo JLayoutHelper::render('joomla.content.info_block.block', array('item' => $this->item, 'params' => $params, 'position' => 'below')); ?>
	<?php endif; ?>
	<?php if ($canEdit || $params->get('show_print_icon') || $params->get('show_email_icon')) : ?>
		<?php if ($params->get('show_print_icon')) : ?>
			<?php if(!$this->print): ?>
				<?php echo JHtml::_('icon.print_popup', $this->item, $params); ?>
			<?php else: ?>
				<?php echo JHtml::_('icon.print_screen', $this->item, $params); ?>
			<?php endif; ?> 
	<?php endif; ?>
	<?php if ($params->get('show_email_icon')) :
		 echo JHtml::_('icon.email', $this->item, $params, array(), true); 
	endif; ?>
	<?php if ($canEdit) :
		 echo JHtml::_('icon.edit', $this->item, $params, array(), true);
	endif; ?>
<?php endif; ?>
</div>
	<?php if ($params->get('show_tags', 1) && !empty($this->item->tags->itemTags)) : ?>
		<?php $this->item->tagLayout = new JLayoutFile('joomla.content.tags'); ?>
		<?php echo $this->item->tagLayout->render($this->item->tags->itemTags); ?>
	<?php endif; ?>
<?php endif; ?>
<?php
if (!empty($this->item->pagination) && $this->item->pagination && $this->item->paginationposition && !$this->item->paginationrelative):
echo '<div>';
echo $this->item->pagination;
echo '</div>';
endif; ?>
<?php if (isset($urls) && ((!empty($urls->urls_position) && ($urls->urls_position == '1')) || ($params->get('urls_position') == '1'))) : ?>
<?php echo $this->loadTemplate('links'); ?>
<?php endif; ?>
<?php elseif ($params->get('show_noauth') == true && $user->get('guest')) : ?>
<?php echo JLayoutHelper::render('joomla.content.intro_image', $this->item); ?>
<?php echo JHtml::_('content.prepare', $this->item->introtext); ?>
<?php if ($params->get('show_readmore') && $this->item->fulltext != null) : ?>
<?php $menu = JFactory::getApplication()->getMenu(); ?>
<?php $active = $menu->getActive(); ?>
<?php $itemId = $active->id; ?>
<?php $link = new JUri(JRoute::_('index.php?option=com_users&view=login&Itemid=' . $itemId, false)); ?>
<?php $link->setVar('return', base64_encode(ContentHelperRoute::getArticleRoute($this->item->slug, $this->item->catid, $this->item->language))); ?>
<p class="readmore">
<a href="<?php echo $link; ?>" class="register">
<?php $attribs = json_decode($this->item->attribs); ?>
<?php
if ($attribs->alternative_readmore == null) :
	echo JText::_('COM_CONTENT_REGISTER_TO_READ_MORE');
elseif ($readmore = $attribs->alternative_readmore) :
	echo $readmore;
	if ($params->get('show_readmore_title', 0) != 0) :
		echo JHtml::_('string.truncate', $this->item->title, $params->get('readmore_limit'));
	endif;
elseif ($params->get('show_readmore_title', 0) == 0) :
	echo JText::sprintf('COM_CONTENT_READ_MORE_TITLE');
else :
	echo JText::_('COM_CONTENT_READ_MORE');
	echo JHtml::_('string.truncate', $this->item->title, $params->get('readmore_limit'));
endif; ?>
</a>
</p>
<?php endif; ?>
<?php endif; ?>
<?php
if (!empty($this->item->pagination) && $this->item->pagination && $this->item->paginationposition && $this->item->paginationrelative) :
echo '<div>';
echo $this->item->pagination;
echo '</div>';
?>
<?php endif; ?>
<?php echo $this->item->event->afterDisplayContent; ?>
</div>
</div>
</article>
