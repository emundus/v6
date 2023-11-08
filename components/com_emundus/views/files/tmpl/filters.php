<?php
/**
 * @version        $Id: filter.php 14401 2014-10-06 14:10:00Z brivalland $
 * @package        Joomla
 * @subpackage     Emundus
 * @copyright      Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license        GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

if ($this->use_module_for_filters === null) {
	$menu                         = JFactory::getApplication()->getMenu();
	$current_menu                 = $menu->getActive();
	$menu_params                  = $menu->getParams(@$current_menu->id);
	$this->use_module_for_filters = boolval($menu_params->get('em_use_module_for_filters', 0));
}


defined('_JEXEC') or die('Restricted access');
?>

<div id="em_filters">
	<?php
	if (!$this->use_module_for_filters) {
	echo @$this->filters;

	?>
</div>
    <script>
        var data = {};

        $('select.testSelAll').on('sumo:opened', function (event) {
            data[event.target.name] = [];

            [...event.target.options].forEach((option) => {
                if (option.selected) {
                    data[event.target.name].push(option.value);
                }
                ;
            });
        });

        $('select.testSelAll').on('sumo:closed', function (event) {
            let newValues = [];
            [...event.target.options].forEach((option) => {
                if (option.selected) {
                    newValues.push(option.value);
                }
                ;
            });

            let differences = newValues
                .filter(newValue => !data[event.target.name].includes(newValue))
                .concat(data[event.target.name].filter(oldVal => !newValues.includes(oldVal)));
            ;

            if (differences.length > 0) {
                setFiltersSumo(event);
            }

            data = {};
        });
    </script>
<?php
}
else {
	echo JHtml::_('content.prepare', '{loadposition emundus_filters}');
}
?>
