<?php
/**
 * Created by PhpStorm.
 * User: yoan
 * Date: 27/05/14
 * Time: 14:34
 */ 
 defined( '_JEXEC' ) or die( 'Restricted access' );

if ($this->use_module_for_filters === null) {
 $menu = JFactory::getApplication()->getMenu();
 $current_menu = $menu->getActive();
 $menu_params = $menu->getParams(@$current_menu->id);
 $this->use_module_for_filters = boolval($menu_params->get('em_use_module_for_filters', 0));
}

defined( '_JEXEC' ) or die( 'Restricted access' );
?>

<div id="em_filters">
 <?php
 if (!$this->use_module_for_filters) {
 echo $this->filters;

 ?>
</div>
 <script>
  var data = {};

  $('select.testSelAll').on('sumo:opened', function(event) {
   data[event.target.name] = [];

   [...event.target.options].forEach((option) => {
    if (option.selected) {
     data[event.target.name].push(option.value);
    };
   });
  });

  $('select.testSelAll').on('sumo:closed', function(event) {
   let newValues = [];
   [...event.target.options].forEach((option) => {
    if (option.selected) {
     newValues.push(option.value);
    };
   });

   let differences = newValues
       .filter(newValue => !data[event.target.name].includes(newValue))
       .concat(data[event.target.name].filter(oldVal => !newValues.includes(oldVal)));

   if (differences.length > 0) {
    setFiltersSumo(event);
   }

   data = {};
  });
 </script>
<?php
} else {
 echo JHtml::_('content.prepare', '{loadposition emundus_filters}');
}
?>

