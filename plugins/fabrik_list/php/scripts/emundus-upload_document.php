<?php

$app = JFactory::getApplication();
$ids = $app->input->get('ids');

foreach ($ids as $id) { // loop cause ids are bad, get only 1 value
    $data = $model->getRow($id);

    if (!empty($data)) {
        foreach ($data as $key => $value) {
            if (str_contains($key, '___fnum')) {
                $fnum = $value;
            }
        }
    }
}

if (!empty($fnum)) {
    require_once(JPATH_SITE . '/components/com_emundus/models/files.php');
    $m_files = new EmundusModelFiles();
    $infos = $m_files->getFnumInfos($fnum);

    if (!empty($infos)) {
        $user_id = $infos->user_id;
        $applicant_id = $infos->user_id;
        $campaign_id = $infos->campaign_id;

        echo '<script>
           Swal.fire({
            width: "80%",
            showCancelButton: false,
            showConfirmButton: false,
            showCloseButton: true,
            html: "<iframe src=' . JURI::Base() . '/component/fabrik/form/67/?jos_emundus_uploads___user_id[value]=' . $user_id . '&jos_emundus_uploads___fnum[value]=' . $fnum . '&student_id=' . $applicant_id . '&jos_emundus_uploads___campaign_id[value]=' . $campaign_id . '&tmpl=component&iframe=1&action_id=4 style="width: 100%; height: 40em;"></iframe>"
            });
           
        </script>';
    }
}


/*
 * echo '<script>
        Swal.fire({
            width: "80%",
            showCancelButton: false,
            showConfirmButton: false,
            showCloseButton: true,
            html: "<iframe src="+ JURI::Base() +
                "/component/fabrik/form/67/?jos_emundus_uploads___user_id[value]=" + $user_id + "
                "&jos_emundus_uploads___fnum[value]="'$fnum'
                &student_id="'$applicant_id'"
                &jos_emundus_uploads___campaign_id[value]="'$campaign_id'"&tmpl=component&iframe=1&action_id=4"
                style=\"width: 100%; height: 40em;\" ></iframe>"
        });
        </script>';
 */
