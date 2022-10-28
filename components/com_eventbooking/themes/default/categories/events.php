<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

$description = $this->category ? $this->category->description: $this->introText;
?>
<div id="eb-categories-page" class="eb-container">
	<?php
		if ($this->params->get('show_page_heading'))
		{
		?>
			<h1 class="eb-page-heading"><?php echo $this->escape($this->params->get('page_heading'));?></h1>
		<?php
		}

		if ($description)
		{
		?>
			<div class="eb-description"><?php echo $description;?></div>
		<?php
		}

		if (count($this->items))
		{
			echo EventbookingHelperHtml::loadCommonLayout('common/categories_events.php', array('categories' => $this->items, 'categoryId' => $this->categoryId, 'config' => $this->config, 'Itemid' => $this->Itemid));
		}


		if ($this->pagination->total > $this->pagination->limit)
		{
		?>
			<div class="pagination">
				<?php echo $this->pagination->getPagesLinks(); ?>
			</div>
		<?php
		}
	?>
</div>