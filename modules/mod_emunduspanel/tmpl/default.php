<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 

if (!empty($tab)) {
?>
<div class="emundus_home_page" id="em-panel">
<fieldset class="ui existing segment">
    <?php
    if(isset($user->profile) && $user->profile>0) {

        $title = $user->profile_label;

        if($show_profile_link == 1)
            $title .= ' '.$btn_profile;
        if($show_start_link == 1)
            $title .= ' '.$btn_start;
        
        echo '<legend>'.$title.'</legend>';
    }
    
    ?>
    <div class="ui grid">
    <?php 
    //$i=1; $j=1;$k=0;
    //$l = (@$user->candidature_posted == 1 && @$user->candidature_incomplete == 0 ) ? 2 : '999';

    foreach ($tab as $t){ 
        //if ($j>$l) {
          //  break;
        //} else {
            echo '<div class="five wide column element_home_emundus">' . $t . '</div>';
        //}
        //$j++;
    } 
    ?>
    </div>
    
</fieldset>
<?php } ?>
</div>