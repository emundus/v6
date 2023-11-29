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

?>
<tr id="<?php echo $this->_row->id;?>" class="<?php echo $this->_row->class;?>" xmlns="http://www.w3.org/1999/html">
    <details class="faq-question-container">
        <?php foreach ($this->headings as $heading => $label) {
            $index = array_search($heading,array_keys($this->headings));
            $style = empty($this->cellClass[$heading]['style']) ? '' : 'style="'.$this->cellClass[$heading]['style'].'"';
            ?>

            <?php if($index == 0) : ?>
                <summary class="faq-question-container__question">
                    <?php echo isset($this->_row->data) ? $this->_row->data->$heading : '';?>
                </summary>
            <?php elseif($heading != 'fabrik_select' && $this->_row->data->$heading != '') : ?>
                <div class="<?php echo $this->cellClass[$heading]['class']; ?> faq-question-container__answer"><?php echo isset($this->_row->data) ? $this->_row->data->$heading : '';?></div>
            <?php endif;?>
            <?php }?>
    </details>
</tr>