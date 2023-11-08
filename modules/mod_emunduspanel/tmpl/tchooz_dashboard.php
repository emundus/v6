<?php // no direct access
defined('_JEXEC') or die('Restricted access');

if (!empty($tab)) :?>


    <div class="emundus_home_page" id="em-panel">
		<?php if (isset($user->profile) && $user->profile > 0) {
			echo '<legend></legend>';


			if (!in_array($user->profile, $applicant_profiles)) {
				echo "<div class='section-sub-menu' style='margin-bottom: 10px'>
                    <div class='container-2 w-container' style='max-width: unset'>
                        <div class='d-flex'>
                            <img src='" . JURI::base() . "images/emundus/menus/dashboard.png' class='tchooz-icon-title' alt='dashboard'>
                            <h1 class='tchooz-section-titles'>" . $title . "</h1>
                        </div>
                        <div class='actions-add-block'>
                            <p class='tchooz-section-description'>" . $desc_text . "</p>
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
		}
		?>
    </div>
<?php endif; ?>
