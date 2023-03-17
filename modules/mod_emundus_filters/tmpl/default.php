<?php
defined('_JEXEC') or die;
?>

<section id="mod_emundus_filters">
    <?php
    if (!empty($default_filters)) {
    ?>
    <div id="default-filters" class="">
        <?php
        foreach($default_filters as $filter) {
            ?>
            <div>
                <label><?= $filter['label'] ?></label>
                <?php
                switch ($filter['type']) {
                    case 'field':
                        echo '<input id="' . $filter['id'] . '" type="text" maxlength="255" />';
                        break;
                    case 'select':
                        ?>
                        <select id="<?= $filter['id'] ?>">
                            <option value="0"><?= JText::_('PLEASE_SELECT'); ?></option>
                            <?php foreach($filter['values'] as $value){ ?>
                                <option value="<?=$value['value']?>"><?= $value['label'] ?></option>
                            <?php } ?>
                        </select>
                        <?php
                        break;
                    case 'date':
                        echo '<input id="' . $filter['id'] . '" type="date"/>';
                        break;
                }
                ?>
            </div>
            <?php
        }
        ?>
    </div>

    <div id="applied-filters">
        <?php
        foreach($applied_filters as $filter) {

        }
	    ?>
    </div>
    <div class="actions">
        <button class="em-primary-button"><?= JText::_('SEARCH'); ?></button>
    </div>
    <?php
    } else {
    ?>
    <div class="no-default-filters">
        <p><?= JText::_('COM_EMUNDUS_EMPTY_FILTERS'); ?></p>
    </div>
    <?php
    }
    ?>
</section>
