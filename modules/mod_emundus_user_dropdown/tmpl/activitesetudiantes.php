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
        const loader = document.getElementsByClassName('em-page-loader');

        if (loader) {
            loader[0].style.display = 'block';
        }
        const current_fnum_el = document.getElementById('current_fnum');
        const current_fnum = current_fnum_el ? current_fnum_el.value : false;

        if (current_fnum) {
            let formData = new FormData();
            formData.append('fnums', current_fnum);
            formData.append('ids_tmpl', [31]);
            formData.append('cansee', 1);
            formData.append('showMode', 2);
            formData.append('mergeMode', 0);

            fetch('index.php?option=com_emundus&controller=files&task=generateletter', {
                method: 'POST',
                body: formData
            }).then(response => {
                return response.json();
            }).then(data => {
                if (data.status &&  data.data.files.length > 0) {
                    let file = data.data.files[0].url + data.data.files[0].filename;
                    download(file,'carte_acces.pdf');
                }

                if (loader) {
                    loader[0].style.display = 'none';
                }
            }).catch(err => {
                console.error(err);

                if (loader) {
                    loader[0].style.display = 'none';
                }
            });
        }
    }
</script>