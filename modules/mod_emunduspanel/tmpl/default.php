<?php // no direct access
defined('_JEXEC') or die('Restricted access');

if (!empty($tab)) :?>


    <div class="emundus_home_page" id="em-panel">
        <?php if (isset($user->profile) && $user->profile > 0) {
            if (!empty($module_title)&& !$lean_mode) {
                $title = "<h2 class='title'>".@$module_title."</h2>";
            } else {
                $title = "";
            }

            if ($show_programme_title == 1) {
                $title .= ' '.$user->profile_label;
            }
            if ($show_profile_link == 1) {
                $title .= ' '.$btn_profile;
            }
            if ($show_start_link == 1) {
                $title .= ' '.$btn_start;
            }

            echo '<legend>'.$title.'</legend>';

            $ids_array = array();
            if (isset($user->fnums) && $user->fnums) {
                foreach ($user->fnums as $fnum) {
                    $ids_array[$fnum->profile_id] = $fnum->fnum;
                }
            }

            /*if (!empty($user->emProfiles) && sizeof($user->emProfiles) > 1 && (($lean_mode && !$only_applicant) || !$lean_mode)) {
                echo '<p>'.JText::_('MOD_EMUNDUSPANEL_SELECT_PROGRAMME').'</p>';
                echo '<br/><div class="select">';
                echo '<legend><select class="form-control form-control-sm" id="profile" name="profiles" onchange="postCProfile()"> ';
                foreach ($user->emProfiles as $profile) {
                    if (array_key_exists($profile->id, $ids_array)) {
                        echo '<option  value="'.$profile->id.".".$ids_array[$profile->id].'"' .(($user->profile == $profile->id)?'selected="selected"':"").'>'.trim($profile->label).'</option>';
                    } else {
                        echo '<option  value="'.$profile->id.".".'"' .(($user->profile == $profile->id)?'selected="selected"':"").'>'.trim($profile->label).'</option>';
                    }
                }
                echo '</select><div class="select_arrow">
                </div></legend></div><br/><br/>';
            }*/
        }
        if ($show_menu == 'true' && (($lean_mode && !in_array($user->profile, $applicant_profiles)) || !$lean_mode)) :?>
            <div class="ui grid">
            <?php
            if (!in_array($user->profile, $applicant_profiles) || $user->fnum) {
                foreach ($tab as $t) {
                    echo '<div class="five wide column element_home_emundus">' . $t . '</div>';
                }
            }
            ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>


<?php if (!empty($user->emProfiles) && sizeof($user->emProfiles) > 1) :?>
<script type="text/javascript">
function postCProfile() {

    var current_fnum = document.getElementById("profile").value;

    jQuery.ajax({
        type: 'POST',
        url: 'index.php?option=com_emundus&task=switchprofile',
        data: ({
            profnum: current_fnum
        }),
        success: function (result) {
            location.reload(true);
        },
        error : function (jqXHR, status, err) {
            alert("Error switching porfiles.");
        }
    });
}
</script>
<?php endif; ?>
