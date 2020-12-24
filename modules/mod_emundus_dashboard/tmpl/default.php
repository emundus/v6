<?php
    foreach ($widgets as $widget):
        //echo $widget;
        require JModuleHelper::getLayoutPath('mod_emundus_dashboard', $widget);
    endforeach;
?>
