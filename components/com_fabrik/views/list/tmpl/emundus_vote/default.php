<?php
/**
 * Bootstrap List Template - Default
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.1
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$pageClass = $this->params->get('pageclass_sfx', '');

$listid = $this->table->id;

JText::script('COM_FABRIK_VOTE_MODAL_TEXT');
JText::script('COM_FABRIK_ERROR_PLEASE_COMPLETE_EMAIL');
JText::script('COM_FABRIK_VOTE_MODAL_YES');
JText::script('COM_FABRIK_VOTE_MODAL_NO');
JText::script('COM_FABRIK_VOTE_MODAL_SUCCESS_TITLE');
JText::script('COM_FABRIK_VOTE_MODAL_SUCCESS_TEXT');
JText::script('COM_FABRIK_VOTE_MODAL_ERROR_TITLE');
JText::script('COM_FABRIK_VOTE_MODAL_ERROR_TEXT');

if ($pageClass !== '') :
	echo '<div class="' . $pageClass . '">';
endif;

if ($this->tablePicker != '') : ?>
    <div style="text-align:right"><?php echo FText::_('COM_FABRIK_LIST') ?>: <?php echo $this->tablePicker; ?></div>
<?php
endif;

if ($this->params->get('show_page_heading')) :
	echo '<h1>' . $this->params->get('page_heading') . '</h1>';
endif;

if ($this->showTitle == 1) : ?>
    <div class="page-header em-flex-row em-flex-space-between emundus-list-page-header">
        <h1 class="after-em-border after:bg-red-800"><?php echo $this->table->label; ?></h1>
    </div>
<?php
endif;

// Intro outside of form to allow for other lists/forms to be injected.
?>
<div class="page-intro mt-4">
	<?php echo $this->table->intro; ?>
</div>

<form class="fabrikForm form-search" action="<?php echo $this->table->action; ?>" method="post"
      id="<?php echo $this->formid; ?>" name="fabrikList">

	<?php
	if ($this->hasButtons):
		echo $this->loadTemplate('buttons');
	endif; ?>
    <div class="<?php if ($this->showFilters) : ?>filter-data-align<?php endif; ?>">
		<?php
		if ($this->showFilters && $this->bootShowFilters) :
			echo $this->layoutFilters();
		endif;
		//for some really ODD reason loading the headings template inside the group
		//template causes an error as $this->_path['template'] doesn't contain the correct
		// path to this template - go figure!
		$headingsHtml = $this->loadTemplate('headings');
		$showTitle    = $this->params->get('show-title');
		echo $this->loadTemplate('tabs');
		?>

        <div class="fabrikDataContainer w-full">

			<?php foreach ($this->pluginBeforeList as $c) :
				echo $c;
			endforeach;
			?>

            <div class="mt-2">
				<?php echo $this->nav; ?>
            </div>

            <div class="em-grid-3-2-1 <?php echo $this->list->class; ?>"
                 id="list_<?php echo $this->table->renderid; ?>">
				<?php if (empty($this->rows) || empty($this->rows[0])) : ?>
                    <div class="emptyDataMessage" style="<?php echo $this->emptyStyle ?>">
						<?php echo $this->emptyDataMessage; ?>
                    </div>
				<?php endif; ?>
				<?php
				$gCounter = 0;
				foreach ($this->rows as $groupedBy => $group) : ?>
					<?php
					foreach ($group as $this->_row) :
						echo $this->loadTemplate('row');
					endforeach;
					$gCounter++;
				endforeach;

				if ($this->hasCalculations) : ?>
                    <div>
                        <div class="fabrik_calculations">

							<?php
							foreach ($this->headings as $key => $heading) :
								$h = $this->headingClass[$key];
								$style = empty($h['style']) ? '' : 'style="' . $h['style'] . '"'; ?>
                                <div class="<?php echo $h['class'] ?>" <?php echo $style ?>>
									<?php
									$cal = $this->calculations[$key];
									echo array_key_exists($groupedBy, $cal->grouped) ? $cal->grouped[$groupedBy] : $cal->calc;
									?>
                                </div>
							<?php
							endforeach;
							?>

                        </div>
                    </div>
				<?php endif ?>
            </div>
			<?php print_r($this->hiddenFields); ?>
        </div>
    </div>
</form>
<?php
echo $this->table->outro;
if ($pageClass !== '') :
	echo '</div>';
endif;
?>
