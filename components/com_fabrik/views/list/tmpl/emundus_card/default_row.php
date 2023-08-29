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

use Joomla\CMS\Language\Text;

$notes = $this->params->get('note', '');
if(!empty($notes)){
    $notes = explode(',',$notes);
}
?>
<?php if(!in_array('details',$notes)) : ?>
    <a id="<?php echo $this->_row->id;?>" class="<?php echo $this->_row->class;?> em-repeat-card-no-padding em-pb-24 em-pointer" href="<?php echo $this->_row->data->fabrik_view_url ?>">
        <?php foreach ($this->headings as $heading => $label) {
            $style = empty($this->cellClass[$heading]['style']) ? '' : 'style="'.$this->cellClass[$heading]['style'].'"';
            ?>
            <?php if (isset($this->_row->data)) : ?>
                <?php if(strpos($this->_row->data->$heading,'<img') !== false || strpos($this->_row->data->$heading,'fabrik-filetype-webp') !== false) : ?>
                    <?php
                        $xpath = new DOMXPath(@DOMDocument::loadHTML($this->_row->data->$heading));
                        $src = $xpath->evaluate("string(//img/@src)");
                        if(empty($src)){
                            $src = $xpath->evaluate("string(//a/@href)");
                        }
                    ?>
                    <p class="<?php echo $this->cellClass[$heading]['class']?>" <?php echo $style?>>
                        <div class="fabrikImageBackground" style="background-image: url('<?php echo $src ?>')"></div>
                    </p>
                <?php else : ?>
                    <?php if (strpos($this->headingClass[$heading]['class'],'displayed')) : ?>
                        <div class="em-mt-12 em-p-8-12">
                            <label class="em-font-weight-600 em-mb-0-important"><?php echo Text::_($label) ?></label>
                            <p class="em-mt-8 <?php echo $this->cellClass[$heading]['class']?>" <?php echo $style?>>
                                <?php echo isset($this->_row->data) ? $this->_row->data->$heading : '';?>
                            </p>
                        </div>
                    <?php else : ?>
                        <p class="em-mt-12 em-p-8-12 <?php echo $this->cellClass[$heading]['class']?>" <?php echo $style?>>
                            <?php echo isset($this->_row->data) ? $this->_row->data->$heading : '';?>
                        </p>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        <?php }?>
    </a>
<?php else : ?>
    <details id="<?php echo $this->_row->id;?>" class="<?php echo $this->_row->class;?> em-repeat-card-no-padding em-pointer" href="<?php echo $this->_row->data->fabrik_view_url ?>">
        <summary>
            <?php foreach ($this->headings as $heading => $label) {
                $style = empty($this->cellClass[$heading]['style']) ? '' : 'style="'.$this->cellClass[$heading]['style'].'"';
                ?>

                <?php if (isset($this->_row->data) && strpos($this->cellClass[$heading]['class'],'summary')) : ?>
                    <p class="em-p-8-12 <?php echo $this->cellClass[$heading]['class']?>" <?php echo $style?>>
                        <?php echo isset($this->_row->data) ? $this->_row->data->$heading : '';?>
                    </p>
                <?php endif; ?>
            <?php }?>
        </summary>
        <?php foreach ($this->headings as $heading => $label) {
            $style = empty($this->cellClass[$heading]['style']) ? '' : 'style="'.$this->cellClass[$heading]['style'].'"';
            ?>
            <?php if (isset($this->_row->data) && strpos($this->cellClass[$heading]['class'],'summary') === false) : ?>
                <?php if (strpos($this->headingClass[$heading]['class'],'displayed')) : ?>
                    <div class="em-p-8-12">
                        <label class="em-font-weight-600 em-mb-0-important"><?php echo Text::_($label) ?></label>
                        <p class="em-mt-8 <?php echo $this->cellClass[$heading]['class']?>" <?php echo $style?>>
                            <?php echo isset($this->_row->data) ? $this->_row->data->$heading : '';?>
                        </p>
                    </div>
                <?php else : ?>
                    <p class="em-p-8-12 <?php echo $this->cellClass[$heading]['class']?>" <?php echo $style?>>
                        <?php echo isset($this->_row->data) ? $this->_row->data->$heading : '';?>
                    </p>
                <?php endif; ?>

            <?php endif; ?>
        <?php }?>
    </details>
<?php endif; ?>
