<?php
defined('_JEXEC') or die;
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers');
$app	= JFactory::getApplication();
$columnwidth=((100)/$this->columns).'%';
?>
<?php if ($this->params->get('show_page_heading', 1)) : ?>
<h1>
<?php echo $this->escape($this->params->get('page_heading')); ?>
</h1>
<?php endif; ?>
<?php if ($this->params->get('show_category_title', 1) OR $this->params->get('page_subheading')) : ?>
<h3>
<?php echo $this->escape($this->params->get('page_subheading')); ?>
</h3>
<?php endif; ?>
<?php if ($this->params->get('show_category_title', 1) OR $this->params->get('show_description', 1)) : ?>
<article class="ttr_post list">
<div class="ttr_post_content_inner">
<?php if ($this->params->get('show_category_title')) : ?>
<div class="ttr_post_inner_box">
<h2 class="ttr_post_title">
<span class="subheading-category"><?php echo $this->category->title;?></span>
</h2>
</div>
<?php endif; ?>
<?php if (($this->params->get('show_description') && $this->category->description) || ($this->params->def('show_description_image') && $this->category->getParams()->get('image'))) : ?>
<div class="ttr_article">
<div class="postcontent">
<?php if ($this->params->get('show_description_image') && $this->category->getParams()->get('image')) : ?>
<img src="<?php echo $this->category->getParams()->get('image'); ?>" alt="description image"/>
<?php endif; ?>
<?php if ($this->params->get('show_description') && $this->category->description) : ?>
<?php echo JHtml::_('content.prepare', $this->category->description); ?>
<?php endif; ?>
<div style="clear: both;"></div>
</div>
</div>
<?php endif; ?>
</div>
</article>
<?php endif; ?>
<div class="row">
<?php $flag=0;?>
<?php $leadingcount=0 ; ?>
<?php if (!empty($this->lead_items)) : ?>
<?php $flag=1;?>
<?php foreach ($this->lead_items as &$item) : ?>
<div class="col-lg-12">
<article class="ttr_post list">
<div class="ttr_post_content_inner">
<?php
$this->item = &$item;
echo $this->loadTemplate('item');
?>
</div>
</article>
</div>
<?php
$leadingcount++;
?>
<?php endforeach; ?>
<?php endif; ?>
<?php
$class_suffix_lg  = round((12 / $this->columns));
if(empty($class_suffix_lg)){ 
$class_suffix_lg  =4;
}
 $md =4;
$class_suffix_md  = round((12 / $md));
 $xs =1;
$class_suffix_xs  = round((12 / $xs));
$columncounter=0;
?>
<?php if (!empty($this->intro_items)):
 if($flag == 1) { ?>
</div>
<div class="row">
<?php }
foreach ($this->intro_items as $key => &$item) :
$columncounter++; ?>
<div class="col-lg-<?php echo $class_suffix_lg;?> col-md-<?php echo $class_suffix_md;?> col-sm-<?php echo $class_suffix_md;?> col-xs-<?php echo $class_suffix_xs;?> <?php echo $this->pageclass_sfx;?>">
<article class="ttr_post grid">
<div class ="ttr_post_content_inner">
<?php
$this->item = &$item;
echo $this->loadTemplate('item');
?>
</div>
</article>
</div>
<?php if(($columncounter) % $xs == 0){ echo '<div class=" visible-xs-block" style="clear:both;"></div>';}
if(($columncounter) % $md == 0){ echo '<div class=" visible-sm-block" style="clear:both;"></div>';
echo '<div class=" visible-md-block" style="clear:both;"></div>';}
if(($columncounter) % $this->columns == 0){ echo '<div class=" visible-lg-block" style="clear:both;"></div>';}?>
<?php endforeach; ?>
<?php endif; ?>
</div>
<?php if (!empty($this->link_items)) : ?>
<?php echo $this->loadTemplate('links'); ?>
<?php endif; ?>
<?php if (!empty($this->children[$this->category->id])&& $this->maxLevel != 0) : ?>
<div class="cat-children" style="clear:both">
<h3>
<?php echo JTEXT::_('JGLOBAL_SUBCATEGORIES'); ?>
</h3>
<?php echo $this->loadTemplate('children'); ?>
</div>
<?php endif; ?>
<?php if (($this->params->def('show_pagination', 1) == 1  || ($this->params->get('show_pagination') == 2)) && ($this->pagination->get('pages.total') > 1)) : ?>
<div>
<?php  if ($this->params->def('show_pagination_results', 1)) : ?>
<p class="counter">
<?php echo $this->pagination->getPagesCounter(); ?>
</p>
<?php endif; ?>
<?php echo $this->pagination->getPagesLinks(); ?>
</div>
<?php  endif; ?>
