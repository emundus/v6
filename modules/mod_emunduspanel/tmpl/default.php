<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 
JHTML::stylesheet( 'emundus.css', JURI::Base().'modules/mod_emunduspanel/style/' );

if (!empty($tab)) {
?>
<fieldset class="ui existing segment">
	<?php
    if(isset($user->profile) && $user->profile>0) {

        $title = $user->profile_label;
        //$title = strlen($user->campaign_name)>3?$user->campaign_name:$user->profile_label;
        if($show_profile_link == 1)
        	echo '<a href="index.php?option=com_users&view=profile&layout=edit"><legend>'.$title. ' <span class="icon-cog"></span></legend></a>';
        else
        	echo '<legend>'.$title.'</legend>';
    }
    
    ?>
    <div class="emundus_home_page" ><div class="rt-grid-9">
    <?php 
    $i=1; $j=1;$k=0;
	$l = (@$user->candidature_posted == 1 && @$user->candidature_incomplete == 0 ) ? 2 : '999';
	//die(print_r($user));

    foreach ($tab as $t){ 
		if ($j>$l) {
            break;
        } else {
            echo '<div class="rt-grid-3 element_home_emundus">' . $t . '</div>';
            $k++;
            if ($k > 2) {
                echo '</div><div class="rt-grid-9">';
                $k=0;
            }
        }
		$j++;
    } 
   	?>
    </div>
    </div>
    
</fieldset>
<?php } ?>
