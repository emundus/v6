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
?>
<div
        id="em-filters-vue"
        data-module-id="<?= $module->id ?>"
        data-applied-filters='<?= base64_encode(json_encode($applied_filters)) ?>'
        data-filters='<?= base64_encode(json_encode($filters)) ?>'
></div>

<script src="media/mod_emundus_filters/app.js"></script>
