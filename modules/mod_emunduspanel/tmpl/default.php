<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 

if (!empty($tab)) {
?>
<div class="emundus_home_page" id="em-panel">

<?php
if(isset($user->profile) && $user->profile>0) {

    $title = $module_title;

    if($show_programme_title == 1)
        $title .= ' '.$user->profile_label;
    if($show_profile_link == 1)
        $title .= ' '.$btn_profile;
    if($show_start_link == 1)
        $title .= ' '.$btn_start;
    
    echo '<legend>'.$title.'</legend>';
}

?>
<div class="ui grid">
<?php 
foreach ($tab as $t){ 
    echo '<div class="five wide column element_home_emundus">' . $t . '</div>';
} 
?>
</div>

<?php } ?>
</div>