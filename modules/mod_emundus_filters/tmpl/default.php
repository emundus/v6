<?php
defined('_JEXEC') or die;
$filterjs_url = JURI::base().'modules/mod_emundus_filters/assets/js/filters.js';

JText::script('MOD_EMUNDUS_FILTERS_SELECT_FILTER');
JText::script('MOD_EMUNDUS_FILTERS_SELECT_VALUE');
JText::script('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_IS');
JText::script('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_IS_NOT');
JText::script('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_CONTAINS');
JText::script('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_DOES_NOT_CONTAIN');
JText::script('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_IS_ONE_OF');
JText::script('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_IS_NOT_ONE_OF');
JText::script('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_AND');
JText::script('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_OR');

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
	                ?>
                    <div class="filter-container em-w-100 em-mb-16" data-filterUid="<?= $filter['uid'] ?>">
                        <div class="filter-header em-w-100 em-flex-row em-flex-space-between em-mb-8">
                            <label for="filter<?= $filter['id'] ?>" class="em-w-100"><?= $filter['label'] ?></label>
                            <?php if (!$filter['default']) : ?>
                                <span class="material-icons-outlined em-pointer" data-filterUid="<?= $filter['uid'] ?>">delete</span>
                            <?php endif; ?>
                        </div>
                        <?php
                            switch ($filter['type']) {
                                case 'select':
                                ?>
                                    <select id="filter+<?= $filter['id'] ?>" name="filter<?= $filter['id'] ?>" class="em-w-100">
                                        <option value="all"><?= JText::_('ALL') ?></option>
                                        <?php foreach($filter['values'] as $value): ?>
                                            <option value="<?= $value['value'] ?>" <?= $value['value'] == $filter['value'] ? 'selected' : '' ?>><?= $value['label'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php
                                    break;
                                case 'text':
                                ?>
                                    <input type="text" id="filter+<?= $filter['id'] ?>" name="filter<?= $filter['id'] ?>" value="<?= $filter['value'] ?>" class="em-w-100"/>
                                <?php
                                    break;
                                case 'date':
                                ?>
                                    <input type="date" id="filter+<?= $filter['id'] ?>" name="filter<?= $filter['id'] ?>" value="<?= $filter['value'] ?>" class="em-w-100"/>
                                <?php
                                    break;
                            }
                        ?>
                    </div>
                    <?php
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

<style>
    .filter-options {
        max-height: 200px;
        overflow-y: auto;
    }
</style>