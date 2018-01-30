<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 

if (!empty($tab)) {
?>
<div class="emundus_home_page" id="em-panel">
<?php
if(isset($user->profile) && $user->profile>0) {

   /* $title = "<h2 class='title'>".@$module_title."</h2>";

    if($show_programme_title == 1)
        $title .= ' '.$user->profile_label;
    if($show_profile_link == 1)
        $title .= ' '.$btn_profile;
    if($show_start_link == 1)
        $title .= ' '.$btn_start;
    echo '<legend>'.$title.'</legend>';
    */
    /*$session        = JFactory::getSession();
    foreach ($session->get('user') as $key => $value) {
       //$emundusSession->{$key} = $value;
       echo(json_encode($key));
       echo(json_encode($value));
    }*/
   
    $ids_array = array();
    
    if($user->fnums){
        foreach($user->fnums as $fnum){
            $ids_array[$fnum->profile_id] = $fnum->fnum;
        }
    }
    echo('<br/><div class="styled-select slate"');
    echo '<legend><select class="form-control form-control-sm" id="profile" name="profiles" onchange="postCProfile()"> ';
    
    foreach($user->emProfiles as $profile){
        if(array_key_exists($profile->id,$ids_array)){
            echo '<option  value="'.$profile->id.".".$ids_array[$profile->id].'"' .(($user->profile == $profile->id)?'selected="selected"':"").'>'.trim($profile->label).'</option>';                
        }else{      
            echo '<option  value="'.$profile->id.".".'"' .(($user->profile == $profile->id)?'selected="selected"':"").'>'.trim($profile->label).'</option>';      
        }
    
    }
}
    echo '</select></legend></div><br/>';
   // echo(json_encode($user));
  

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

<script type="text/javascript">

        function postCProfile() {

            var current_fnum    = $$("#profile").get('value');
            //var current_fnum    = $$("#profile").get('fnum');
         // alert(current_profile)
            //var current_profile    = $$("#profile").get('value');

            var ajax = new Request({
                url:'index.php?option=com_emundus&task=switchprofile',
                method: 'post',
                data: {
                    profnum: current_fnum
                },
                onSuccess: function(result){
                    //result = JSON.parse(result);
                    //alert(result)
                    location.reload(true);
                },
                onFailure: function (jqXHR, status, err) {
                    alert("ajax sending error");
                  }
            });
            //alert(current_fnum)

            ajax.send();
            
        }

    </script>