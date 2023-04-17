<?php
defined('_JEXEC') or die;
$filterjs_url = JURI::base().'modules/mod_emundus_filters/assets/js/filters.js';

JText::script('MOD_EMUNDUS_FILTERS_SELECT_FILTER');
JText::script('MOD_EMUNDUS_FILTERS_SELECT_VALUE');
?>

<section id="mod_emundus_filters">
    <input type="text" id="search" placeholder="<?= JText::_('SEARCH') ?>"/>

    <?php
    if (!empty($filters)) {
    ?>
        <div id="applied-filters" class="em-mt-16 em-mb-16">
            <?php
            if (!empty($applied_filters)) {
                foreach($applied_filters as $filter) {

                }
            }
            ?>
        </div>
        <div id="filters-selection-wrapper" class="hidden em-w-100 em-mt-16 em-mb-16">
            <label for="filters-selection"><?= JText::_('MOD_EMUNDUS_FILTERS_SELECT_FILTER_LABEL'); ?></label>
            <select id="filters-selection" name="filters-selection" class="em-w-100">
                <option value="0"><?= JText::_('MOD_EMUNDUS_FILTERS_SELECT_FILTER') ?></option>
		        <?php foreach($filters as $filter): ?>
                    <option value="<?= $filter['id'] ?>" data-values="<?= base64_encode(json_encode($filter['values'])); ?>" data-type="<?= $filter['type'] ?>"><?= $filter['label'] ?></option>
		        <?php endforeach; ?>
            </select>
        </div>
        <div class="actions em-mt-16">
            <button id="apply-filters" class="em-primary-button"><?= JText::_('MOD_EMUNDUS_FILTERS_APPLY_FILTERS'); ?></button>
            <button id="add-filter" class="em-secondary-button em-mt-16"><?= JText::_('MOD_EMUNDUS_FILTERS_ADD_FILTER'); ?></button>
        </div>
    <?php
    } else {
    ?>
        <div class="no-default-filters">
            <p><?= JText::_('MOD_EMUNDUS_FILTERS_EMPTY_FILTER'); ?></p>
        </div>
    <?php
    }
    ?>
</section>
<script src="<?= $filterjs_url ?>"></script>