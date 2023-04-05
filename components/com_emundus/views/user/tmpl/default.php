<?php
defined('_JEXEC') or die('Restricted access');

$email = $this->user;
$current_user = JFactory::getUser();
?>
<div class="em-activation-header">
    <p><a class="em-back-button em-pointer em-w-auto em-float-left" style="text-decoration: unset" href="index.php?option=com_users&task=user.logout&<?php echo JSession::getFormToken() ?>=1"><span class="material-icons em-mr-4">navigate_before</span><?= JText::_('COM_EMUNDUS_MAIL_GB_BUTTON'); ?></a></p>
</div>

    <section class="em-activation">
        <section class="info">
            <div class="infoContainer">
                <div class="em-flex-column">
                    <div class="em-circle-main-100 em-flex-column">
                        <div class="em-circle-main-200 em-flex-column">
                            <span class="material-icons-outlined em-font-size-48 em-main-400-color">mail</span>
                        </div>
                    </div>
                </div>
                <h3 class="em-h3 em-mb-32 em-mt-24"><?= JText::_('COM_EMUNDUS_MAIL_SEND') ?></h3>
                <p class="instructions"><?= JText::sprintf( 'COM_EMUNDUS_ACCESS_PLATFORM', $this->user_email ); ?></p>
                <div class="resend em-mt-48">
                    <p><?= JText::_('COM_EMUNDUS_MAIL_NOT_RECEIVE_DESC'); ?>
                        <!--<span onclick="activation(<?= $this->user->id; ?>)" class="em-pointer"><?= JText::_('COM_EMUNDUS_MAIL_NOT_RECEIVE_DESC_2'); ?></span>-->
                    </p>
                    <div class="containerButtons">
                        <input id="email" type="text" name="email" value="<?= $this->user_email ?>" class="mail">
                        <input type="button" onclick="activation()" class="btn btn-primary btn-resend" value="<?= JText::_('COM_EMUNDUS_MAIL_SEND_NEW'); ?>">
                    </div>
                </div>
            </div>
        </section>
    </section>

<div class="em-page-loader"></div>

<script>
    window.addEventListener('DOMContentLoaded', () => {
        document.getElementsByClassName('em-page-loader')[0].style.display = 'none';
        document.getElementById('g-page-surround').style.background = 'white';
        document.getElementById('g-footer').style.display = 'none';
        document.getElementById('header-c').style.display = 'none';
    });

    function activation() {
       document.getElementsByClassName('em-page-loader')[0].style.display = 'block';
       return new Promise(function(resolve, reject) {
           let formData = new FormData();
           formData.append('email', document.getElementById('email').value);
           fetch(window.location.origin + '/index.php?option=com_emundus&controller=users&task=activation', {
               body: formData,
               method: 'post'
           }).then((response) => {
               if (response.ok) {
                   return response.json();
               } else{
                   Swal.fire({
                       title: Joomla.JText._('COM_EMUNDUS_ONBOARD_ERROR_MESSAGE'),
                       type: 'error',
                       showConfirmButton: false,
                       showCancelButton: false,
                       timer: 1500,
                       customClass: {
                           title: 'em-swal-title',
                       },
                   });

                   reject(response);
               }
           }).then((res) => {
               document.getElementsByClassName('em-page-loader')[0].style.display = 'none';

               if (res.status) {
                   Swal.fire({
                       title: Joomla.JText._('COM_EMUNDUS_MAIL_SENDED'),
                       text: res.msg,
                       type: "success",
                       showConfirmButton: false,
                       showCancelButton: false,
                       timer: 1500,
                       customClass: {
                           title: 'em-swal-title',
                       },
                   });
               } else {
                   Swal.fire({
                       title: Joomla.JText._('COM_EMUNDUS_ONBOARD_ERROR_MESSAGE'),
                       text: res.msg,
                       type: "error",
                       showConfirmButton: false,
                       showCancelButton: false,
                       timer: 1500,
                       customClass: {
                           title: 'em-swal-title',
                       },
                   });
                   reject(res.msg);
               }
           });
       });
   }
</script>

