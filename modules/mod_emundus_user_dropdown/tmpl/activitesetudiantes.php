<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_menu
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

require JModuleHelper::getLayoutPath('mod_emundus_user_dropdown', 'tchooz');
?>

<input type="hidden" value="<?= $user->fnum; ?>" id="current_fnum">
<script>
    function download(fileUrl, fileName) {
        var a = document.createElement("a");
        a.href = fileUrl;
        a.setAttribute("download", fileName);
        a.click();
    }

    function generateLetter() {
        document.getElementsByClassName('em-page-loader')[0].style.display = 'block';
        let current_fnum = document.getElementById("current_fnum").value;

        fetch('index.php?option=com_emundus&controller=files&task=generateletter', {
            method: 'POST',
            body: JSON.stringify({
                fnums: current_fnum,
                ids_tmpl: [31],
                cansee: 1,
                showMode: 2,
                mergeMode: 0,
            }),
            headers: {
                'Content-Type': 'application/json'
            }
        }).then(response => {
            return response.json();
        }).then(data => {
            if (data.status &&  data.data.files.length > 0) {
                let file = data.data.files[0].url + data.data.files[0].filename;
                download(file,'carte_acces.pdf');
            }
            document.getElementsByClassName('em-page-loader')[0].style.display = 'none';
        }).catch(err => {
            console.error(err);
            document.getElementsByClassName('em-page-loader')[0].style.display = 'none';
        });
    }
</script>