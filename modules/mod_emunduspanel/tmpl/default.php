<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 
JHTML::stylesheet( 'emundus.css', JURI::Base().'modules/mod_emunduspanel/style/' );

if (!empty($tab)) {
?>
<fieldset>
	<?php
    if(isset($user->profile) && $user->profile>0) {
      /*  $name = $user->profile;
        $query='SELECT label,id FROM #__emundus_setup_profiles WHERE id ='.$name;
        $db->setQuery($query);
        $label = $db->loadResult();*/
        $title = strlen($user->campaign_name)>3?$user->campaign_name:$user->profile_label;
        if($show_profile_link == 1)
        	echo '<a href="index.php?option=com_users&view=profile&layout=edit"><h2>'.$title. ' <span class="icon-cog"></span></h2></a>';
        else
        	echo '<h2>'.$title.'</h2>';
    }
    
    ?>
    <div class="emundus_home_page" ><div class="rt-grid-12">
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
            if ($k >= 4) {
                echo '</div><div class="rt-grid-12">';
                $k=0;
            }
        }
		$j++;
    } 
     echo '</div>';
    echo '</div>';
	/* // Apply again
	$query='SELECT count(id) as cpt FROM #__emundus_setup_campaigns 
			WHERE id NOT IN (
				select campaign_id FROM #__emundus_campaign_candidature WHERE applicant_id='.$user->id.'
			)';
	$db->setQuery($query);
	$cpt = $db->loadResult();

	if (@$user->applicant == 1 && @$user->candidature_posted == 1 && @$user->candidature_incomplete == 0 && $cpt > 0 && $applicant_can_renew) {
		$str = '<a href="index.php?option=com_emundus&view=renew_application"><img src="'.JURI::Base().'media/com_emundus/images/icones/renew.png" /></a>';
		$str .= '<br/><a class="text" href="'.JURI::Base().'index.php?option=com_emundus&view=renew_application">'.JText::_('RENEW_APPLICATION').'</a>';
		echo '<td align="center">'.$str.'</td>';
	}
	*/
    ?>
</fieldset>
<?php } ?>
