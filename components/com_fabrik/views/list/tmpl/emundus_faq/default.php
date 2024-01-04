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
	<div class="page-header em-flex-column em-flex-space-between emundus-list-page-header">
		<h1><?php echo $this->table->label;?></h1>
		<?php if ($this->showAdd) :?>

            <div><a class="addbutton addRecord em-primary-button em-w-max-content" href="<?php echo $this->addRecordLink;?>">
					<?php echo FText::_($this->addLabel);?>
                </a></div>
		<?php
		endif; ?>
	</div>
<?php
endif;

// Intro outside of form to allow for other lists/forms to be injected.
?>
<div class="page-intro <?php if ($this->showTitle != 1) : ?>em-mt-32<?php endif; ?>">
    <?php echo $this->table->intro; ?>
</div>

<form class="fabrikForm form-search" action="<?php echo $this->table->action;?>" method="post" id="<?php echo $this->formid;?>" name="fabrikList">

<?php
if ($this->hasButtons):
	echo $this->loadTemplate('buttons');
endif;

if ($this->showFilters && $this->bootShowFilters) :
	echo $this->layoutFilters();
endif;
//for some really ODD reason loading the headings template inside the group
//template causes an error as $this->_path['template'] doesn't contain the correct
// path to this template - go figure!
$headingsHtml = $this->loadTemplate('headings');
echo $this->loadTemplate('tabs');
?>

<div class="fabrikDataContainer em-mt-24">

<?php foreach ($this->pluginBeforeList as $c) :
	echo $c;
endforeach;
?>
	<div class="<?php echo $this->list->class;?>" id="list_<?php echo $this->table->renderid;?>" >
		<?php
		if ($this->isGrouped && empty($this->rows)) :
			?>
			<div style="<?php echo $this->emptyStyle?>">
				<div class="groupDataMsg">
					<div class="emptyDataMessage" style="<?php echo $this->emptyStyle?>" colspan="<?php echo count($this->headings)?>">
						<div class="emptyDataMessage" style="<?php echo $this->emptyStyle?>">
							<?php echo $this->emptyDataMessage; ?>
						</div>
					</div>
				</div>
			</div>
			<?php
		endif;
		$gCounter = 0;
		foreach ($this->rows as $groupedBy => $group) :
			if ($this->isGrouped) : ?>
			<div class="border-t border-neutral-300 pt-6">
				<div class="fabrik_groupheading info mb-4 px-2">
					<div colspan="<?php echo $this->colCount;?>">
						<?php echo $this->layoutGroupHeading($groupedBy, $group); ?>
					</div>
				</div>
			</div>
			<?php endif ?>
			<div class="fabrik_groupdata">
				<div class="groupDataMsg" style="<?php echo $this->emptyStyle?>">
					<div class="emptyDataMessage" style="<?php echo $this->emptyStyle?>" colspan="<?php echo count($this->headings)?>">
						<div class="emptyDataMessage" style="<?php echo $this->emptyStyle?>">
							<?php echo $this->emptyDataMessage; ?>
						</div>
					</div>
				</div>
			<?php
			foreach ($group as $this->_row) :
				echo $this->loadTemplate('row');
		 	endforeach
		 	?>
		 	</div>
			<?php if ($this->hasCalculations) : ?>
			<div>
				<div class="fabrik_calculations">

				<?php
				foreach ($this->headings as $key => $heading) :
					$h = $this->headingClass[$key];
					$style = empty($h['style']) ? '' : 'style="' . $h['style'] . '"';?>
					<td class="<?php echo $h['class']?>" <?php echo $style?>>
						<?php
						$cal = $this->calculations[$key];
						echo array_key_exists($groupedBy, $cal->grouped) ? $cal->grouped[$groupedBy] : $cal->calc;
						?>
					</td>
				<?php
				endforeach;
				?>

				</div>
			</div>
			<?php endif ?>
		<?php
		$gCounter++;
		endforeach?>
        <table style="border: unset">
            <tr class="fabrik___heading bg-transparent">
                <td colspan="<?php echo count($this->headings);?>">
					<?php echo $this->nav;?>
                </td>
            </tr>
        </table>
	</div>
	<?php print_r($this->hiddenFields);?>
</div>
</form>
<?php
echo $this->table->outro;
if ($pageClass !== '') :
	echo '</div>';
endif;
?>

<script>

    class Accordion {
        constructor(el) {
            this.el = el;
            this.summary = el.querySelector('summary');
            this.contents = el.querySelectorAll('.faq-question-container__content>.faq-question-container__answer, .faq-question-container__content>.faq-question-container__informations-container');
            this.animation = null;
            this.isClosing = false;
            this.isExpanding = false;
            this.summary.addEventListener('click', (e) => this.onClick(e));
        }

        onClick(e) {
            e.preventDefault();
            this.el.style.overflow = 'hidden';
            if (this.isClosing || !this.el.open) {
                this.open();
            } else if (this.isExpanding || this.el.open) {
                this.shrink();
            }
        }

        shrink() {
            this.isClosing = true;
            const startHeight = `${this.el.offsetHeight}px`;
            const endHeight = `${this.summary.offsetHeight}px`;
            if (this.animation) {
                // Cancel the current animation
                this.animation.cancel();
            }
            this.animation = this.el.animate({
                height: [startHeight, endHeight]
            }, {
                duration: 400,
                easing: 'ease-out'
            });

            this.animation.onfinish = () => this.onAnimationFinish(false);
            this.animation.oncancel = () => this.isClosing = false;
        }

        open() {
            this.el.style.height = `${this.el.offsetHeight}px`;
            this.el.open = true;
            window.requestAnimationFrame(() => this.expand());
        }

        expand() {
            this.isExpanding = true;
            const startHeight = `${this.el.offsetHeight}px`;

            let totalContentsHeight = 0;
            this.contents.forEach(content => {
                totalContentsHeight += content.offsetHeight;
            })

            const endHeight = `${this.summary.offsetHeight + totalContentsHeight}px`;
            if (this.animation) {
                this.animation.cancel();
            }
            this.animation = this.el.animate({
                height: [startHeight, endHeight]
            }, {
                duration: 400,
                easing: 'ease-out'
            });
            this.animation.onfinish = () => this.onAnimationFinish(true);
            this.animation.oncancel = () => this.isExpanding = false;
        }

        onAnimationFinish(open) {
            this.el.open = open;
            this.animation = null;
            this.isClosing = false;
            this.isExpanding = false;
            this.el.style.height = this.el.style.overflow = '';
        }
    }

    document.querySelectorAll('details').forEach((el) => {
        new Accordion(el);
    });

</script>
