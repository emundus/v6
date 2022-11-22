<?php
/**
* @package Joomla
* @subpackage eMundus
* @copyright Copyright (C) 2019 emundus.fr. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

defined('_JEXEC') or die('RESTRICTED');
?>
<html><body bgcolor="#FFFFFF">
<table width="100%" border="0">
  <tr>
      <td><h1>Installer des modules</h1></td>
      <?php foreach ($this->modules as $m_key => $module): ?>
        <tr>
            <td>
                <h3><?php echo $module['title'] ?></h3>
                <?php if (!empty($module['desc'])): ?> <p><?= $module['desc']; ?></p> <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td><button class="em-primary-button em-w-auto" type="button" onclick="install('<?= $m_key ?>')">Installer le module</button></td>
        </tr>
      <?php endforeach; ?>
  </tr>
</table>
</body></html>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>
<script>
    function install(module){
        jQuery.ajax({
            type: 'POST',
            url: 'index.php?option=com_emundus&controller=modules&task=install',
            data: ({
                module: module
            }),
            success: function (result) {
                result = JSON.parse(result);
                if(result.status === true){
                    Swal.fire({
                        title: 'Installation effectuée',
                        text: 'Installation du module effectuée avec succès',
                        type: "success",
                        showCancelButton: false,
                        reverseButtons: true,
                        customClass: {
                            title: 'em-swal-title',
                            cancelButton: 'em-swal-cancel-button',
                            confirmButton: 'em-swal-confirm-button',
                        },
                        timer: 2000,
                    });
                }
            },
            error : function (jqXHR, status, err) {
                alert("Error switching porfiles.");
            }
        });
    }
</script>
