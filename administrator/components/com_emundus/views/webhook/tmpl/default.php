<?php
/**
* @package Joomla
* @subpackage eMundus
* @copyright Copyright (C) 2023 emundus.fr. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

defined('_JEXEC') or die('RESTRICTED');
?>
<html><body bgcolor="#FFFFFF">
<table width="100%" border="0">
  <tr>
        <td><h3><?php echo JText::_('COM_EMUNDUS_ADMIN_WEBHOOK_GENERATE_TITLE') ?></h3></td>
  </tr>
        <tr>
            <td>
                <p>
                    <strong style="color: #9f2929"><?php echo JText::_('COM_EMUNDUS_ADMIN_WEBHOOK_GENERATE_TIP') ?></strong>
                </p>
            </td>
        </tr>
        <tr>
            <td><button class="em-primary-button em-w-auto" type="button" onclick="generate()">Générer</button></td>
        </tr>
</table>
</body></html>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>
<script>
    function generate(){
        fetch(window.location.origin + '/administrator/index.php?option=com_emundus&controller=webhook&task=webhook.generate', {
            method: 'get'
        }).then((response) => {
            if (response.ok) {
                return response.json();
            }
        }).then((res) => {
            if(res.status === true){
                Swal.fire({
                    title: 'Clé générée',
                    html: '<p>Copier la clé ci-dessous en lieu sûr : </p><p><strong>' + res.token + '</strong></p>',
                    type: "success",
                    showCancelButton: false,
                    reverseButtons: true,
                    showConfirmButton: true,
                    customClass: {
                        title: 'em-swal-title',
                        cancelButton: 'em-swal-cancel-button',
                        confirmButton: 'em-swal-confirm-button',
                    },
                });
            }
        });
    }
</script>
