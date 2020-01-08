<?php

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers');

$category  = $this->get('category');
$extension = $category->extension;
$canEdit   = $this->params->get('access-edit');
$className = substr($extension, 4);

$dispatcher = JEventDispatcher::getInstance();
$this->category->text = $this->category->description;
$dispatcher->trigger('onContentPrepare', array($this->extension . '.categories', &$this->category, &$this->params, 0));
$this->category->description = $this->category->text;

$results = $dispatcher->trigger('onContentAfterTitle', array($this->extension . '.categories', &$this->category, &$this->params, 0));
$afterDisplayTitle = trim(implode("\n", $results));

$results = $dispatcher->trigger('onContentBeforeDisplay', array($this->extension . '.categories', &$this->category, &$this->params, 0));
$beforeDisplayContent = trim(implode("\n", $results));

$results = $dispatcher->trigger('onContentAfterDisplay', array($this->extension . '.categories', &$this->category, &$this->params, 0));
$afterDisplayContent = trim(implode("\n", $results));

if (substr($className, -1) === 's')
{
	$className = rtrim($className, 's');
}
$tagsData = $category->tags->itemTags; 
?>

<div class="category-list<?php echo $this->pageclass_sfx;?>">

	<?php if ($this->params->get('show_page_heading')) : ?>
			<h1>
				<?php echo $this->escape($this->params->get('page_heading')); ?>
			</h1>
		<?php endif; ?>

		<?php if ($this->params->get('show_category_title', 1)) : ?>
			<h2>
			<?php echo JHtml::_('content.prepare', $this->category->title, '', $this->extension . '.category.title'); ?>	
			<?php if ($this->params->get('page_subheading')) : ?>
				<span class="subheading-category">
				<?php echo $this->escape($this->params->get('page_subheading')); ?>		
				</span>
			<?php endif; ?>
			</h2>

	<?php endif; ?>
		
		<?php echo $afterDisplayTitle; ?>
	
	<?php if ($this->params->get('show_cat_tags', 1)) : ?>
			<?php echo JLayoutHelper::render('joomla.content.tags', $tagsData); ?>
		<?php endif; ?>

		<?php if ($beforeDisplayContent || $afterDisplayContent || $this->params->get('show_description', 1) || $this->params->def('show_description_image', 1)) : ?>
			<div class="category-desc">
				<?php if ($this->params->get('show_description_image') && $this->category->getParams()->get('image')) : ?>
					<img src="<?php echo $this->category->getParams()->get('image'); ?>" alt="<?php echo htmlspecialchars($category->getParams()->get('image_alt'), ENT_COMPAT, 'UTF-8'); ?>"/>
				<?php endif; ?>
				<?php echo $beforeDisplayContent; ?>
				<?php if ($this->params->get('show_description') && $category->description) : ?>
					<?php echo JHtml::_('content.prepare', $category->description, '', $this->extension . '.category.description'); ?>
				<?php endif; ?>
				<?php echo $afterDisplayContent; ?>
				<div class="clr"></div>
			</div>
		<?php endif; ?>
		<div class="cat-items">
		<?php echo $this->loadTemplate('articles'); ?>
	</div>

		<?php if ($this->maxLevel != 0 && $this->get('children')) : ?>
			<div class="cat-children">
				<?php if ($this->params->get('show_category_heading_title_text', 1) == 1) : ?>
					<h3>
						<?php echo JText::_('JGLOBAL_SUBCATEGORIES'); ?>
					</h3>
				<?php endif; ?>
				<?php echo $this->loadTemplate('children'); ?>
			</div>
		<?php endif; ?>
</div>