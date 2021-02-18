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


            if(!in_array($user->profile, $applicant_profiles)){
                echo "<div class='section-sub-menu' style='margin-bottom: 10px'>
                    <div class='container-2 w-container' style='max-width: unset'>
                        <div class='d-flex'>
                            <img src='/images/emundus/menus/dashboard.png' class='tchooz-icon-title' alt='dashboard'>
                            <h1 class='tchooz-section-titles'>Tableau de bord</h1>
                        </div>
                        <div class='actions-add-block'>
                            <p class='tchooz-section-description'>Retrouvez vos statistiques</p>
                        </div>
                    </div>
                    </div>";
            }

            $ids_array = array();
            if (isset($user->fnums) && $user->fnums) {
                foreach ($user->fnums as $fnum) {
                    $ids_array[$fnum->profile_id] = $fnum->fnum;
                }
            }

            /*if (!empty($user->emProfiles) && sizeof($user->emProfiles) > 1 && (($lean_mode && !$only_applicant) || !$lean_mode)) {
                echo '<p>'.JText::_('SELECT_PROGRAMME').'</p>';
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
        ?>
    </div>
<?php endif; ?>
