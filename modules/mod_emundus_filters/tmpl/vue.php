<?php
defined('_JEXEC') or die('Restricted Access');

JText::script('MOD_EMUNDUS_FILTERS');
JText::script('MOD_EMUNDUS_FILTERS_SELECT_FILTER');
JText::script('MOD_EMUNDUS_FILTERS_SELECT_FILTER_LABEL');
JText::script('MOD_EMUNDUS_FILTERS_ADD_FILTER');
JText::script('MOD_EMUNDUS_FILTERS_SELECT_VALUE');
JText::script('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_IS');
JText::script('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_IS_NOT');
JText::script('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_CONTAINS');
JText::script('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_DOES_NOT_CONTAIN');
JText::script('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_IS_ONE_OF');
JText::script('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_IS_NOT_ONE_OF');
JText::script('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_AND');
JText::script('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_OR');
JText::script('MOD_EMUNDUS_FILTERS_PLEASE_SELECT');
JText::script('MOD_EMUNDUS_FILTERS_FILTER_SEARCH');
JText::script('MOD_EMUNDUS_FILTERS_APPLY_FILTERS');
JText::script('MOD_EMUNDUS_FILTERS_CLEAR_FILTERS');
JText::script('MOD_EMUNDUS_FILTERS_SAVE_FILTERS');
JText::script('MOD_EMUNDUS_FILTERS_SAVE_FILTER_NAME');
JText::script('MOD_EMUNDUS_FILTERS_SAVED_FILTERS');
JText::script('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_SUPERIOR_TO');
JText::script('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_SUPERIOR_OR_EQUAL_TO');
JText::script('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_INFERIOR_TO');
JText::script('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_INFERIOR_OR_EQUAL_TO');
JText::script('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_BETWEEN');
JText::script('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_NOT_BETWEEN');
JText::script('MOD_EMUNDUS_FILTERS_SCOPE_IN');
JText::script('MOD_EMUNDUS_FILTERS_SCOPE_ALL');
JText::script('MOD_EMUNDUS_FILTERS_SCOPE_FIRSTNAME');
JText::script('MOD_EMUNDUS_FILTERS_SCOPE_LASTNAME');
JText::script('MOD_EMUNDUS_FILTERS_SCOPE_USERNAME');
JText::script('MOD_EMUNDUS_FILTERS_SCOPE_EMAIL');
JText::script('MOD_EMUNDUS_FILTERS_SCOPE_FNUM');
JText::script('MOD_EMUNDUS_FILTERS_SCOPE_ID');
JText::script('MOD_EMUNDUS_FILTERS_GLOBAL_SEARCH_PLACEHOLDER');
JText::script('MOD_EMUNDUS_FILTERS_MORE_VALUES');

?>
<div
  id="em-filters-vue"
  data-module-id="<?= $module->id ?>"
  data-applied-filters='<?= base64_encode(json_encode($applied_filters)); ?>'
  data-filters='<?= base64_encode(json_encode($filters)); ?>'
  data-quick-search-filters='<?= base64_encode(json_encode($quick_search_filters)); ?>'
  data-count-filter-values='<?= $params->get('count_filter_values'); ?>'
  data-allow-add-filter="<?= $params->get('allow_add_filter', 1); ?>"
></div>

<script src="media/mod_emundus_filters/app.js"></script>
