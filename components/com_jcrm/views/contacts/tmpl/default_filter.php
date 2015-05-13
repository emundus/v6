<?php

defined('JPATH_BASE') or die;
$filters = false;
if (isset($data['view']->filterForm)) {
    $filters = $this->filterForm->getGroup('filter');
}
?>

<fieldset id="filter-bar">
    <div class="filter-search fltlft">
        <input type="text" name="filter[search]" id="filter_search"
               value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
               title="<?php echo JText::_('COM_USERS_SEARCH_USERS'); ?>"/>
        <button type="submit"><?php echo JText::_('COM_JCRM_SEARCH_FILTER_SUBMIT'); ?></button>
        <button type="button"
                onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('COM_JCRM_SEARCH_FILTER_CLEAR'); ?></button>
        <?php if ($filters) : ?>
        <button type="button"
                onclick="jQuery('.fltrt').slideToggle('fast');jQuery(this).toggleClass('open');"><?php echo JText::_('COM_JCRM_SEARCH_TOOLS'); ?></button>
        <?php endif; ?>
    </div>
    <div class="filter-select fltrt <?php echo (!$this->activeFilters) ? 'hide' : 'show'; ?>">
        <?php // Load the form filters ?>
        <?php if ($filters) : ?>
            <?php foreach ($filters as $fieldName => $field) : ?>
                <?php if ($fieldName != 'filter_search') : ?>
                    <div class="field-filter">
                        <?php echo $field->input; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</fieldset>