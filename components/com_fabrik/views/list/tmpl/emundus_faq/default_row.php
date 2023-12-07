<?php
/**
 * Fabrik List Template: Admin Row
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$cellClass= $this->cellClass;

$fullHeadings = array_filter($this->headings, function ($heading) use ($cellClass) {
    return strpos($cellClass[$heading]['class'], "w-full")!== false;
}, ARRAY_FILTER_USE_KEY );

$otherHeadings = array_diff_key($this->headings, $fullHeadings);
$actionsHeadings = array_filter($otherHeadings, function ($heading) use ($cellClass) {
	return $heading === 'fabrik_actions';
}, ARRAY_FILTER_USE_KEY );

$otherHeadings = array_diff_key($otherHeadings, $actionsHeadings);
$summary = array_keys($otherHeadings)[0];

unset($otherHeadings[$summary]);

?>
<div id="<?php echo $this->_row->id;?>" class="<?php echo $this->_row->class;?> mb-4" xmlns="http://www.w3.org/1999/html">
    <details class="faq-question-container">
        <summary class="faq-question-container__question">
            <?php echo isset($this->_row->data) ? $this->_row->data->$summary : '';?>
        </summary>

        <div class="faq-question-container__content">
            <?php foreach ($fullHeadings as $heading => $label) {
                $style = empty($this->cellClass[$heading]['style']) ? '' : 'style="'.$this->cellClass[$heading]['style'].'"';
                ?>

                <?php if($heading != 'fabrik_select' && $this->_row->data->$heading != '') : ?>
                        <div class="<?php echo $this->cellClass[$heading]['class']; ?> faq-question-container__answer"><?php echo isset($this->_row->data) ? $this->_row->data->$heading : '';?></div>
                <?php endif;?>
            <?php }?>

            <div class="faq-question-container__informations-container flex">
                <?php foreach ($otherHeadings as $heading => $label) {
                    $style = empty($this->cellClass[$heading]['style']) ? '' : 'style="'.$this->cellClass[$heading]['style'].'"';
                    ?>
                    <?php if($heading != 'fabrik_select' && $this->_row->data->$heading != '') : ?>
                    <div class="faq-question-container__infos-block">
                        <div class="<?php echo $this->cellClass[$heading]['class']; ?> faq-question-container__answer-label"><?php echo isset($label) ? $label : '';?></div>
                        <div class="<?php echo $this->cellClass[$heading]['class']; ?> faq-question-container__answer-info"><?php echo isset($this->_row->data) ? $this->_row->data->$heading : '';?></div>
                    </div>
                        <?php endif;?>
                <?php }?>
            </div>

	        <?php foreach ($actionsHeadings as $heading => $label) {
		        $style = empty($this->cellClass[$heading]['style']) ? '' : 'style="'.$this->cellClass[$heading]['style'].'"';
		        ?>

		        <?php if($this->_row->data->$heading != '') : ?>
                    <div class="<?php echo $this->cellClass[$heading]['class']; ?> faq-question-container__answer"><?php echo isset($this->_row->data) ? $this->_row->data->$heading : '';?></div>
		        <?php endif;?>
	        <?php }?>
        </div>
    </details>
</div>