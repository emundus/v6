<?php
/**
 * @package    Joomla
 * @subpackage emundus
 * @link       http://www.emundus.fr
 * @copyright  eMundus
 * @license    GNU/GPL
 * @author     Hugo Moracchini
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

$current_user = JFactory::getUser();
$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
$view = JRequest::getVar('view', null, 'GET', 'none',0);
$task = JRequest::getVar('task', null, 'GET', 'none',0);
$tmpl = JRequest::getVar('tmpl', null, 'GET', 'none',0);

// Load the WYSIWYG editor used to edit the mail body.
$editor = JFactory::getEditor('tinymce');
$mail_body = $editor->display('mail_body', JText::_('DEAR').' [NAME], ', '100%', '400', '20', '20', false, 'mail_body', null, null, array('mode' => 'simple'));

$m_messages = new EmundusModelMessages();

// load all of the available messages, categories (to sort messages),attachments, letters.
$message_categories = $m_messages->getAllCategories();
$message_templates = $m_messages->getAllMessages();
$setup_attachments = $m_messages->getAttachments();
$setup_letters = $m_messages->getLetters();

$email_list = array();

$allowed_attachments = EmundusHelperAccess::getUserAllowedAttachmentIDs($current_user->id);
if ($allowed_attachments !== true) {
    foreach ($setup_attachments as $key => $att) {
        if (!in_array($att->id, $allowed_attachments)) {
            unset($setup_attachments[$key]);
        }
    }
}
?>

<!-- WYSIWYG Editor -->
<link rel="stylesheet" href="components/com_jce/editor/libraries/css/editor.min.css" type="text/css">
<script data-cfasync="false" type="text/javascript" src="media/editors/tinymce/tinymce.min.js"></script>
<script data-cfasync="false" type="text/javascript" src="media/editors/tinymce/js/tinymce.min.js"></script>
<script data-cfasync="false" type="text/javascript">tinyMCE.init({menubar:false,statusbar: false})</script>

<div id="em-email-messages"></div>

<div class="em-modal-sending-emails" id="em-modal-sending-emails">
    <div id="em-sending-email-caption" class="em-sending-email-caption"><?= JText::_('SENDING_EMAILS'); ?></div>
    <img class="em-sending-email-img" id="em-sending-email-img" src="media/com_emundus/images/sending-email.gif">
</div>

<form id="emailForm" class="em-form-message" name="emailForm" style="padding:0px 15px;">
    <div class="em_email_block" id="em_email_block">

        <div class="form-inline row">

            <!-- Dropdown to select the email categories used. -->
            <div class="form-group col-md-6 col-sm-6 em-form-selectCategory">
                <label for="select_category" ><?= JText::_('SELECT_CATEGORY'); ?></label>
                <select name="select_category" class="form-control" onChange="setCategory(this);">
                    <?php if (!$message_categories) :?>
                        <option value="%"> <?= JText::_('NO_CATEGORIES_FOUND'); ?> </option>
                    <?php else: ?>
                        <option value="%"> <?= JText::_('SELECT_CATEGORY'); ?> </option>
                        <?php foreach ($message_categories as $message_category) :?>
                            <?php if (!empty($message_category)) :?>
                                <option value="<?= $message_category; ?>"> <?= $message_category; ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Dropdown to select the email template used. -->
            <div class="form-group col-md-6 col-sm-6 em-form-selectTypeEmail">
                <label for="select_template" ><?= JText::_('SELECT_TEMPLATE'); ?></label>
                <select name="select_template" id="message_template" class="form-control" onChange="getTemplate(this);">
                    <?php if (!$message_templates) :?>
                        <option value="%"> <?= JText::_('NO_TEMPLATES_FOUND'); ?> </option>
                    <?php else: ?>
                        <option value="%"> <?= JText::_('SELECT_TEMPLATE'); ?> </option>
                        <?php foreach ($message_templates as $message_template) :?>
                            <option value="<?= $message_template->id; ?>"> <?= $message_template->subject; ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <br/>

        <div class="form-inline row">

            <!-- Dropdown to select the email categories used. -->
            <div class="form-group col-md-6 col-sm-6 em-form-selectCategory">
                <label for="select_sending_mode" ><?= JText::_('SELECT_SENDING_MODE'); ?></label>
                <select name="select_sending_mode" class="form-control" id="sending-mode">
                    <option value="0" selected> <?= JText::_('SELECT_SENDING_MODE_DEFAULT'); ?> </option>
                    <option value="1"> <?= JText::_('SELECT_SENDING_MODE_YES'); ?> </option>
                    <option value="2"> <?= JText::_('SELECT_SENDING_MODE_NO'); ?> </option>
                </select>
            </div>

            <!-- Dropdown to select the email template used. -->
            <div class="form-group col-md-6 col-sm-6 em-form-selectTypeEmail" id="action-tag-box">
                <label for="select_action_tags" ><?= JText::_('SELECT_ACTION_TAGS'); ?></label>
                <input type="text" id="action-tags" class="action-tags" style="height: 43px !important">
            </div>
        </div>

        <input name="mail_from_id" type="hidden" class="inputbox" id="mail_from_id" value="<?= $current_user->id; ?>" /><br>
        <input name="fnums" type="hidden" class="inputbox" id="fnums" value="<?= implode(',',$this->fnums); ?>" />
        <input name="tags" type="hidden" class="inputbox" id="tags" value="" />

        <!-- Add current user to Cc -->
        <div id="cc-box" class="input-group form-inline col-md-12" style="margin-bottom: 10px !important;">
            <input type="text" id="cc-mails" class="cc-bcc-mails">
        </div><!-- /input-group -->

        <!-- Add current user to Bcc -->
        <div id="bcc-box" class="input-group form-inline col-md-12">
            <input type="text" id="bcc-mails" class="cc-bcc-mails">
        </div><!-- /input-group -->

    </br>
        <div class="form-group em-form-recipients">
            <!-- List of users / their emails, gotten from the fnums selected. -->
            <div class="well well-sm" id="em-recipitents">
                <span class='label label-grey'><?= JText::_('TO'); ?>:</span>
                <?php foreach ($this->users as $user) : ?>

                    <?php if (!empty($user['email']) && !in_array($user['email'], $email_list)) : ?>
                        <?php $email_list[] = $user['email']; ?>
                        <span class="label label-grey em-email-label">
							<?= $user['name'].' <em>&lt;'.$user['email'].'&gt;</em>'; ?>
						</span>

                        <input type="hidden" name="ud[]" id="ud" value="<?= $user['id']; ?>"/>
                    <?php endif; ?>

                <?php endforeach; ?>
            </div>
        </div>
        <div class="form-group em-form-sender">
            <div class="inputbox input-xlarge form-control form-inline">
                <span class='label label-grey' for="mail_from" ><?= JText::_('FROM'); ?>:</span>
                <div class="form-group" style="display:inline-block !important;" id="mail_from_name" contenteditable="true"><?= $current_user->name; ?> </div>
                <div class="form-group" style="display:inline-block !important;" id="mail_from" contenteditable="true"><strong> <?= $current_user->email; ?></strong></div>
            </div>
        </div>
        <div class="form-group em-form-subject">
            <div class="inputbox input-xlarge form-control form-inline">
                <span class='label label-grey' for="mail_from" ><?= JText::_('SUBJECT'); ?>:</span>
                <div class="form-group" style="display:inline-block !important;" id="mail_subject" contenteditable="true"><?= JFactory::getConfig()->get('sitename'); ?></div>
            </div>

            <!-- Email WYSIWYG -->
            <?= $mail_body; ?>
        </div>

        <div class="form-group">
            <br>
            <hr>
        </div>

        <div class="form-inline row em-form-attachments">
            <div class="form-group col-sm-12 col-md-5">
                <label for="em-select_attachment_type" ><?= JText::_('SELECT_ATTACHMENT_TYPE'); ?></label>
                <select name="em-select_attachment_type" id="em-select_attachment_type" class="form-control download" onChange="toggleAttachmentType(this);">
                    <option value=""> <?= JText::_('PLEASE_SELECT'); ?> </option>
                    <option value="upload"> <?= JText::_('UPLOAD'); ?> </option>
                    <?php if (EmundusHelperAccess::asAccessAction(4, 'r')) : ?>
                        <option value="candidate_file"> <?= JText::_('CANDIDATE_FILE'); ?> </option>
                    <?php endif; ?>
                    <?php if (EmundusHelperAccess::asAccessAction(4, 'c') && EmundusHelperAccess::asAccessAction(27, 'c')) : ?>
                        <option value="setup_letters"> <?= JText::_('SETUP_LETTERS_ATTACH'); ?> </option>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group col-sm-12 col-md-7">
                <!-- Upload a file from computer -->
                <div class="hidden upload-file em-form-attachments-uploadFile" id="upload_file">

                    <div class="file-browse">
                        <span id="em-filename"><?= JText::_('FILE_NAME'); ?></span>

                        <label for="em-file_to_upload" type="button"><?= JText::_('SELECT_FILE_TO_UPLOAD') ?>
                            <input type="file" id="em-file_to_upload" onChange="addFile();">
                        </label>
                    </div>
                    <div id="em-progress-wrp" class="loading-bar">
                        <div class="progress-bar"></div>
                        <div class="status">0%</div>
                    </div>
                </div>

                <!-- Get a file from setup_attachments -->
                <?php if (EmundusHelperAccess::asAccessAction(4, 'r')) : ?>
                    <div class="hidden em-form-attachments-candidateFile" id="candidate_file">
                        <label for="em-select_candidate_file" ><?= JText::_('UPLOAD'); ?></label>
                        <select id="em-select_candidate_file" name="candidate_file" class="form-control download">
                            <?php if (!$setup_attachments) :?>
                                <option value="%"> <?= JText::_('NO_FILES_FOUND'); ?> </option>
                            <?php else: ?>
                                <option value="%"> <?= JText::_('PLEASE_SELECT'); ?> </option>
                                <?php foreach ($setup_attachments as $attachment): ?>
                                    <option value="<?= $attachment->id; ?>"> <?= $attachment->value; ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <span class="input-group-btn">
                              <a class="btn btn-grey hidden" type="button" id="uploadButton" style="top:23px; float: right;" onClick="addFile();"><?= JText::_('ADD_ATTACHMENT'); ?></a>
                        </span>
                    </div>
                <?php endif; ?>

                <!-- Get a file from setup_letters -->
                <?php if (EmundusHelperAccess::asAccessAction(4, 'c') && EmundusHelperAccess::asAccessAction(27, 'c')) : ?>
                    <div class="hidden em-form-attachments-setupLetters" id="setup_letters">
                        <label for="em-select_setup_letters" ><?= JText::_('UPLOAD'); ?></label>
                        <select id="em-select_setup_letters" name="setup_letters" class="form-control">
                            <?php if (!$setup_letters) :?>
                                <option value="%"> <?= JText::_('NO_FILES_FOUND'); ?> </option>
                            <?php else: ?>
                                <option value="%"> <?= JText::_('PLEASE_SELECT'); ?> </option>
                                <?php foreach ($setup_letters as $letter): ?>
                                    <option value="<?= $letter->id; ?>"> <?= $letter->title; ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <span class="input-group-btn">
                            <a class="btn btn-grey" type="button" id="uploadButton" style="top:23px; float: right;" onClick="addFile();"><?= JText::_('ADD_ATTACHMENT'); ?></a>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <br>
    <hr>
    <div class="form-group attachment em-form-attachments-location">
        <ul class="list-group" id="em-attachment-list">
            <!-- Files to be attached will be added here. -->
        </ul>
    </div>

    <a href="index.php?option=com_emundus&view=export_select_columns&format=html&layout=all_programs&Itemid=1173" target="_blank"><?= JText::_('COM_EMUNDUS_SEE_TAGS'); ?></a>

    <input type="hidden" name="task" value=""/>
</form>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>

<script type="text/javascript">
    // get all action tags by ajax

    $("#action-tags").selectize({create: false, preload: false});         // init selectize object

    var $selectize_cc = $("#cc-mails").selectize({
        plugins: ["remove_button"],
        create: true,
        preload:true,
        render: {
            item: function(data, escape) {
                var val = data.value;
                return '<div>' +
                    '<span class="title">' +
                    '<span class="name">' + escape(val.substring(val.indexOf(":") + 1))+'</span>' +
                    '</span>' +
                    '</div>';
            }
        },
    });

    var $selectize_bcc = $("#bcc-mails").selectize({
        plugins: ["remove_button"],
        create: true,
        preload:true,
        render: {
            item: function(data, escape) {
                var val = data.value;
                return '<div>' +
                    '<span class="title">' +
                    '<span class="name">' + escape(val.substring(val.indexOf(":") + 1))+'</span>' +
                    '</span>' +
                    '</div>';
            }
        },
    });

    // var cci = null;
    getAllTagsWithoutRefresh();

    // Editor loads disabled by default, we apply must toggle it active on page load.
    $(document).ready(function() {
        var $select_tag = $(document.getElementById('action-tags'));
        var selectize_tag = $select_tag[0].selectize;
        selectize_tag.clear();

        $('#action-tag-box .selectize-input').css('pointer-events', 'none');
        $('#action-tag-box .selectize-input').css('background-image', 'linear-gradient(17deg, #fafafa 25%, #dfebf2 25%, #dfebf2 50%, #fafafa 50%, #fafafa 75%, #dfebf2 75%, #dfebf2 100%)');
        $('#action-tag-box .selectize-input').css('background-size', '20.52px 6.27px');

        $('#action-tag-box .selectize-input').append('<label id="default_message" style="color:red; font-size: 0.8rem; text-transform: uppercase">' + Joomla.JText._('COM_EMUNDUS_DEFAULT_SENDING_MESSAGE') + '</label>');

        tinyMCE.execCommand('mceToggleEditor', true, 'mail_body');
        $('#cc-box .selectize-input').append('<label for="cc-emails" style="font-size: 15px !important; color: #cecece; font-weight: normal !important">' + Joomla.JText._('COM_EMUNDUS_EMAILS_CC_LABEL') + '</label>');
        $('#bcc-box .selectize-input').append('<label for="bcc-emails" style="font-size: 15px !important; color: #cecece; font-weight: normal !important">' + Joomla.JText._('COM_EMUNDUS_EMAILS_BCC_LABEL') + '</label>');
    });

    // Change file upload string to selected file and reset the progress bar.
    $('#em-file_to_upload').change(function() {
        $('#em-filename').html(this.value.match(/([^\/\\]+)$/)[1]);
        $("#em-progress-wrp .progress-bar").css("width", + 0 + "%");
        $("#em-progress-wrp .status").text(0 + "%");
    });


    $('.action-tags .selectize-input').keyup(function(e){
        if(e.keyCode == '8') {
            getAllTagsWithRefresh();
        }
    })

    // get all tags without refresh
    function getAllTagsWithoutRefresh() {
        $.ajax({
            type: 'POST',
            url: 'index.php?option=com_emundus_onboard&controller=settings&task=gettags',
            dataType: 'JSON',
            success: function(data) {
                var $select_tag = $(document.getElementById('action-tags'));
                var selectize_tag = $select_tag[0].selectize;

                let tags = data.data;

                tags.forEach(tag => {
                    selectize_tag.addOption({ value:tag.id, text: tag.label });
                    selectize_tag.addItem(tag.label);
                })

                selectize_tag.refreshOptions(false);
            }, error: function(jqXHR) {
                console.log(jqXHR.responseText);
            }
        })
    }

    // get all tags with refresh
    function getAllTagsWithRefresh() {
        $.ajax({
            type: 'POST',
            url: 'index.php?option=com_emundus_onboard&controller=settings&task=gettags',
            dataType: 'JSON',
            success: function(data) {
                var $select_tag = $(document.getElementById('action-tags'));
                var selectize_tag = $select_tag[0].selectize;

                let tags = data.data;

                tags.forEach(tag => {
                    selectize_tag.addOption({ value:tag.id, text: tag.label });
                    selectize_tag.addItem(tag.label);
                })

                selectize_tag.refreshOptions(true);
            }, error: function(jqXHR) {
                console.log(jqXHR.responseText);
            }
        })
    }

    /// when user select "classic mode" of sending_mode --> deactivate action-tag box and then change cursor mode ==> not-allowed
    $(document).on('change', '#sending-mode', function() {
        let sending_mode = $('#sending-mode').val();
        if(sending_mode == 0) {
            var $select_tag = $(document.getElementById('action-tags'));
            var selectize_tag = $select_tag[0].selectize;
            selectize_tag.clear();

            $('#action-tag-box .selectize-input').append('<label id="default_message" style="color:red; font-size: 0.8rem; text-transform: uppercase">' + Joomla.JText._('COM_EMUNDUS_DEFAULT_SENDING_MESSAGE') + '</label>');
            $('#action-tag-box .selectize-input').css('pointer-events', 'none');
            $('#action-tag-box .selectize-input').css('background-image', 'linear-gradient(17deg, #fafafa 25%, #dfebf2 25%, #dfebf2 50%, #fafafa 50%, #fafafa 75%, #dfebf2 75%, #dfebf2 100%)');
            $('#action-tag-box .selectize-input').css('background-size', '20.52px 6.27px');

            $('#action-tag-box').find('.selectize-dropdown-content').empty();
        } else {
            $('#default_message').remove();
            getAllTagsWithoutRefresh();
            $('#action-tag-box .selectize-input').css('pointer-events', '');
            $('#action-tag-box .selectize-input').css('background-image', '');
            $('#action-tag-box .selectize-input').css('background-size', '');

            // load action tags by email template
            $.ajax({
                type: 'POST',
                url: 'index.php?option=com_emundus_onboard&controller=email&task=getemailbyid',
                dataType: 'JSON',
                data: { id : $('#message_template').val() },
                success: function(data) {
                    if(data.status) {
                        let tags = data.data.tags;
                        if (tags != null && tags != undefined) {
                            $('#sending-mode option:eq(1)').prop('selected', true);
                            tags.forEach(tag => {
                                $("div.option[data-value='" + tag.id + "']").click();
                                $("div.item[data-value='" + tag.id + "']").css('background',tag.class);
                                $("div.item[data-value='" + tag.id + "']").css('color','#fff');
                                $("div.item[data-value='" + tag.id + "']").css('font-family','Maven Pro');
                                $("div.item[data-value='" + tag.id + "']").css('font-weight','bold');
                                $("div.item[data-value='" + tag.id + "']").css('padding','.2em .6em .3em');
                                $("div.item[data-value='" + tag.id + "']").css('font-size','100% !important');
                                $("div.item[data-value='" + tag.id + "']").css('margin-top','7px !important');
                            })
                        }
                    }
                }, error: function(jqXHR) {
                    console.log(jqXHR.responseText);
                }
            })
        }
    })

    // Loads the template and updates the WYSIWYG editor
    function getTemplate(select) {
        /// clear tags
        $('#tags').val("");

        /// clear cc
        var $select_cc = $(document.getElementById('cc-mails'));
        var selectize_cc = $select_cc[0].selectize;
        selectize_cc.clear();

        /// clear bcc
        var $select_bcc = $(document.getElementById('bcc-mails'));
        var selectize_bcc = $select_bcc[0].selectize;
        selectize_bcc.clear();

        var $select_tag = $(document.getElementById('action-tags'));
        var selectize_tag = $select_tag[0].selectize;
        selectize_tag.clear();

        $("label[for='cc-emails']").empty();
        $("label[for='bcc-emails']").empty();

        $('#cc-box .selectize-input').append('<label for="cc-emails" style="font-size: 15px !important; color: #cecece; font-weight: normal !important">' + Joomla.JText._('COM_EMUNDUS_EMAILS_CC_LABEL') + '</label>');
        $('#bcc-box .selectize-input').append('<label for="bcc-emails" style="font-size: 15px !important; color: #cecece; font-weight: normal !important">' + Joomla.JText._('COM_EMUNDUS_EMAILS_BCC_LABEL') + '</label>');

        $('#em-attachment-list').empty();
        // ajax to get all receivers when changing model email
        $.ajax({
            type: 'POST',
            url: 'index.php?option=com_emundus_onboard&controller=email&task=getemailbyid',
            dataType: 'JSON',
            data: { id : select.value },
            success: function(data) {

                if(data.status) {

                    let tags = data.data.tags;

                    if (tags != null && tags != undefined) {
                        $('#sending-mode option:eq(1)').prop('selected', true);
                        $('#sending-mode').trigger("change");
                        $('#sending-mode').trigger("chosen:updated");

                        tags.forEach(tag => {
                            console.log(tag.id);
                            $("div.option[data-value='" + tag.id + "']").click();
                            $("div.item[data-value='" + tag.id + "']").css('background',tag.class);
                            $("div.item[data-value='" + tag.id + "']").css('color','#fff');
                            $("div.item[data-value='" + tag.id + "']").css('font-family','Maven Pro');
                            $("div.item[data-value='" + tag.id + "']").css('font-weight','bold');
                            $("div.item[data-value='" + tag.id + "']").css('padding','.2em .6em .3em');
                            $("div.item[data-value='" + tag.id + "']").css('font-size','100% !important');
                            $("div.item[data-value='" + tag.id + "']").css('margin-top','7px !important');
                        })
                    }

                    if (data.data.receivers != null && data.data.receivers != undefined && data.data.receivers != "") {

                        let receivers = data.data.receivers;

                        let receiver_cc = [];
                        let receiver_bcc = [];
                        let fabrik_cc = [];
                        let fabrik_bcc = [];

                        for (let index = 0; index < receivers.length; index++) {
                            switch (receivers[index].type) {
                                case 'receiver_cc_email':
                                    receiver_cc.push(receivers[index].receivers);
                                    break;

                                case 'receiver_bcc_email':
                                    receiver_bcc.push(receivers[index].receivers);
                                    break;

                                case 'receiver_cc_fabrik':
                                    fabrik_cc.push(receivers[index].receivers);
                                    break;

                                case 'receiver_bcc_fabrik':
                                    fabrik_bcc.push(receivers[index].receivers);
                                    break;

                                default:
                                    break;
                            }
                        }

                        // cc
                        receiver_cc.forEach(cc => {
                            selectize_cc.addOption({value: "CC: " + cc, text: cc});
                            selectize_cc.addItem("CC: " + cc);
                        })

                        // bcc
                        receiver_bcc.forEach(bcc => {
                            selectize_bcc.addOption({value: "BCC: " + bcc, text: bcc});
                            selectize_bcc.addItem("BCC: " + bcc);
                        })

                        if(fabrik_cc.length > 0 && fabrik_cc != "" && fabrik_cc != null && fabrik_cc != undefined) {
                            var REGEX_EMAIL = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                            // call to controller --> get fabrik value
                            $.ajax({
                                type: 'post',
                                url: 'index.php?option=com_emundus&controller=files&task=getfabrikvaluebyid',
                                dataType: 'json',
                                data: { elements : fabrik_cc },
                                success: function(data) {
                                    let emails = [];

                                    for(email in data.data) {
                                        if (REGEX_EMAIL.test(data.data[email])) {
                                            emails.push(data.data[email]);
                                            selectize_cc.addOption({value: "CC: " + data.data[email], text: data.data[email]});
                                            selectize_cc.addItem("CC: " + data.data[email]);
                                        }
                                    }

                                }, error: function(jqXHR) {
                                    console.log(jqXHR.responseText);
                                }
                            })
                        }

                        // do the same thing with bcc receivers
                        if(fabrik_bcc.length > 0 && fabrik_bcc != "" && fabrik_bcc != null && fabrik_bcc != undefined) {
                            var REGEX_EMAIL = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                            // call to controller --> get fabrik value
                            $.ajax({
                                type: 'post',
                                url: 'index.php?option=com_emundus&controller=files&task=getfabrikvaluebyid',
                                dataType: 'json',
                                data: { elements : fabrik_bcc },
                                success: function(data) {
                                    let emails = [];

                                    for(email in data.data) {
                                        if (REGEX_EMAIL.test(data.data[email])) {
                                            emails.push(data.data[email]);
                                            selectize_bcc.addOption({value: "BCC: " + data.data[email], text: data.data[email]});
                                            selectize_bcc.addItem("BCC: " + data.data[email]);
                                        }
                                    }

                                    console.log(emails);

                                }, error: function(jqXHR) {
                                    console.log(jqXHR.responseText);
                                }
                            })
                        }
                    }
                }

                $.ajax({
                    type: "POST",
                    url : "index.php?option=com_emundus&controller=messages&task=gettemplate",
                    data : {
                        select : select.value
                    },
                    success: function (email) {
                        var cci = $selectize_cc[0].selectize;
                        email = JSON.parse(email);

                        if(email.tmpl.cci != null){
                            let cci_emails = email.tmpl.cci.split(',');
                            cci_emails.forEach((elt) => {
                                cci.createItem("BCC: Bcc: <"+elt+">");
                            });
                        } else {
                            //cci.clear();
                        }

                        //$("#tags").val(email.tmpl.tags);

                        var email_block = document.getElementById("em_email_block");
                        $("#mail_subject").text(email.tmpl.subject);
                        $("#mail_from").text(email.tmpl.emailfrom);
                        $("#mail_from_name").text(email.tmpl.name);
                        $("#mail_body").val(email.tmpl.message);
                        tinyMCE.execCommand("mceSetContent", false, email.tmpl.message);
                        tinyMCE.execCommand("mceRepaint");

                        //Reset attachments.
                        $('#em-attachment-list').each(function(idx, li) {
                            var attachment = $(li);

                            if (attachment.hasClass('candidate_file')) {

                                // Remove 'disabled' attr from select options.
                                $('#em-select_candidate_file option[value="'+attachment.find('.value').text()+'"]').prop('disabled', false);

                            } else if (attachment.hasClass('setup_letters')) {

                                // Remove 'disabled' attr from select options.
                                $('#em-select_setup_letters option[value="'+attachment.find('.value').text()+'"]').prop('disabled', false);

                            }
                        });

                        // Get the attached uploaded file if there is one.
                        if (typeof(email.tmpl.attachment) != 'undefined' && email.tmpl.attachment != null) {
                            $('#em-attachment-list').append('<li class="list-group-item upload" style="padding: 15px 15px"><div class="value hidden">'+email.tmpl.attachment+'</div>'+ email.tmpl.attachment.split('\\').pop().split('/').pop() +'<span class="badge btn-danger" onClick="removeAttachment(this);"><span class="glyphicon glyphicon-remove"></span></span><span class="badge"><span class="glyphicon glyphicon-saved"></span></span></li>');
                        }

                        <?php if (EmundusHelperAccess::asAccessAction(4, 'r')) : ?>
                        // Get the attached candidate files if there are any.
                        if (typeof(email.tmpl.candidate_attachments) != 'undefined' && email.tmpl.candidate_attachments != null) {

                            // We need another AJAX to get the info about the attachments, we only have the IDs and we need the names.
                            $.ajax({
                                type: 'POST',
                                url: 'index.php?option=com_emundus&controller=messages&task=getcandidatefilenames',
                                data : {
                                    attachments : email.tmpl.candidate_attachments
                                },
                                success: function (attachments) {
                                    attachments = JSON.parse(attachments);
                                    if (attachments.status) {

                                        // Add the attachments to the list and deselect the corresponding selects from the option.
                                        attachments.attachments.forEach(function(attachment) {
                                            $('#em-attachment-list').append('<li class="list-group-item candidate_file" style="padding: 15px 15px"><div class="value hidden">'+attachment.id+'</div>'+attachment.value+'<span class="badge btn-danger" onClick="removeAttachment(this);"><span class="glyphicon glyphicon-remove"></span></span><span class="badge"><span class="glyphicon glyphicon-paperclip"></span></span></li>');
                                            $('#em-select_candidate_file option[value="'+attachment.id+'"]').prop('disabled', true);
                                        });
                                    }
                                }
                            })
                        }
                        <?php endif; ?>

                        // TODO: Rights?
                        // Get the attached candidate files if there are any.
                        if (typeof(email.tmpl.letter_attachments) != 'undefined' && email.tmpl.letter_attachments != null) {
                            console.log(data);
                            let letters = data.data.attachments;

                            letters.forEach(letter => {
                                $('#em-attachment-list').append('' +
                                    '<li class="list-group-item setup_letters" style="padding: 15px 15px">' +
                                        '<div class="value hidden">' + letter.id + '</div>' + letter.value +
                                        '<span class="badge btn-danger" onClick="removeAttachment(this);">' +
                                        '<span class="glyphicon glyphicon-remove"></span>' +
                                        '</span>' +
                                        '<span class="badge">' +
                                        '<span class="glyphicon glyphicon-envelope">' + '</span>' +
                                        '</span>' +
                                    '</li>');
                            })
                        }
                    },
                    error: function () {
                        // handle error
                        $("#message_template").append('<span class="alert"> <?= JText::_('ERROR'); ?> </span>')
                    }
                });
            }, error: function(jqXHR) {
                console.log(jqXHR.responseText);
            }
        })
    }

    function removeElement(element) {
        $('.remove').on('click', function(e) {
            $(this).parent().remove();
        });
    }

    // Used for toggling the options dipslayed in the message templates dropdown.
    function setCategory(element) {

        if (element.value == "%")
            category = 'all';
        else
            category = element.value;

        $.ajax({
            type: "GET",
            url: "index.php?option=com_emundus&controller=messages&task=setcategory&category="+category,
            success: function (data) {

                data = JSON.parse(data);

                if (data.status) {

                    var $el = $("#message_template");
                    $('#message_template option:gt(0)').remove();

                    $.each(data.templates, function(key,value) {
                        $el.append($("<option></option>")
                            .attr("value", value.id).text(value.subject));
                    });
                } else {
                    $("#message_template").append('<span class="alert"> <?= JText::_('ERROR'); ?> </span>')
                }
            },
            error: function (error) {
                // handle error
                $("#message_template").append('<span class="alert"> <?= JText::_('ERROR'); ?> </span>')
            },
        });
    }

    function SubmitForm() {
        // Form submission has been moved to em_files.js under the modal submission listener.
    }


    // Used for reseting a File upload input.
    function resetFileInput(e) {
        e.wrap('<form>').closest('form').get(0).reset();
        e.unwrap();
    }


    // Change the attachment type being uploaded.
    function toggleAttachmentType(toggle) {

        switch (toggle.value) {

            case 'upload' :
                $('#upload_file').removeClass('hidden');
                $('#candidate_file').addClass('hidden');
                $('#setup_letters').addClass('hidden');
                $('#uploadButton').removeClass('hidden');
                break;

            case 'candidate_file' :
                resetFileInput($('#upload_file'));
                $('#upload_file').addClass('hidden');
                $('#candidate_file').removeClass('hidden');
                $('#setup_letters').addClass('hidden');
                $('#uploadButton').removeClass('hidden');
                break;

            case 'setup_letters' :
                resetFileInput($('#upload_file'));
                $('#upload_file').addClass('hidden');
                $('#candidate_file').addClass('hidden');
                $('#setup_letters').removeClass('hidden');
                $('#uploadButton').removeClass('hidden');
                break;

            default :
                resetFileInput($('#upload_file'));
                $('#upload_file').addClass('hidden');
                $('#candidate_file').addClass('hidden');
                $('#setup_letters').addClass('hidden');
                $('#uploadButton').addClass('hidden');
                break;

        }

    }


    // Add file to the list being attached.
    function addFile() {

        switch ($('#em-select_attachment_type :selected').val()) {

            case 'upload' :

                // We need to get the file uploaded by the user.
                var file = $("#em-file_to_upload")[0].files[0];
                var upload = new Upload(file);
                // Verification of style size and type can be done here.
                upload.doUpload();

                break;


            case 'candidate_file' :

                // we just need to note the reference to the setup_attachment file.
                var file = $('#em-select_candidate_file :selected');

                var alreadyPicked = $('#em-attachment-list li.candidate_file').find('.value:contains("'+file.val()+'")');

                if (alreadyPicked.text() != '') {

                    // Flash the line a certain color to show it's already picked.
                    alreadyPicked.parent().attr("style", "background-color: #C5EFF7");
                    setTimeout(function(){
                        alreadyPicked.parent().attr("style", "");
                    }, 500);

                } else {

                    // Disable the file from the dropdown.
                    file.prop('disabled', true);
                    // Add the file to the list.
                    $('#em-attachment-list').append('<li class="list-group-item candidate_file" style="padding: 15px 15px"><div class="value hidden">'+file.val()+'</div>'+file.text()+'<span class="badge btn-danger" onClick="removeAttachment(this);"><span class="glyphicon glyphicon-remove"></span></span><span class="badge"><span class="glyphicon glyphicon-paperclip"></span></span></li>');

                }

                break;

            case 'setup_letters' :

                // We need to note the reference to the setup_letters file.
                var file = $('#em-select_setup_letters :selected');

                var alreadyPicked = $('#em-attachment-list li.setup_letters').find('.value:contains("'+file.val()+'")');

                if (alreadyPicked.text() != '') {

                    // Flash the line a certain color to show it's already picked.
                    alreadyPicked.parent().attr("style", "background-color: #C5EFF7");
                    setTimeout(function(){
                        alreadyPicked.parent().attr("style", "");
                    }, 500);

                } else {

                    // Disable the file from the dropdown.
                    file.prop('disabled', true);
                    // Add the file to the list.
                    $('#em-attachment-list').append('<li class="list-group-item setup_letters" style="padding: 15px 15px"><div class="value hidden">'+file.val()+'</div>'+file.text()+'<span class="badge btn-danger" onClick="removeAttachment(this);"><span class="glyphicon glyphicon-remove"></span></span><span class="badge"><span class="glyphicon glyphicon-envelope"></span></span></li>');

                }

                break;

            default :

                // Nothing selected, this case should not happen.
                $("#em-attachment-list").append('<span class="alert alert-danger"> <?= JText::_('ERROR'); ?> </span>')

                break;

        }
    }


    function removeAttachment(element) {

        element = $(element);

        if (element.parent().hasClass('candidate_file')) {

            // Remove 'disabled' attr from select options.
            $('#em-select_candidate_file option[value="'+element.parent().find('.value').text()+'"]').prop('disabled', false);

        } else if (element.parent().hasClass('setup_letters')) {

            // Remove 'disabled' attr from select options.
            $('#em-select_setup_letters option[value="'+element.parent().find('.value').text()+'"]').prop('disabled', false);

        }

        $(element).parent().remove();
    }


    // Helper function for uploading a file via AJAX.
    var Upload = function (file) {
        this.file = file;
    };

    Upload.prototype.getType = function() {
        return this.file.type;
    };
    Upload.prototype.getSize = function() {
        return this.file.size;
    };
    Upload.prototype.getName = function() {
        return this.file.name;
    };
    Upload.prototype.doUpload = function () {
        var that = this;
        var formData = new FormData();

        // add assoc key values, this will be posts values
        formData.append("file", this.file, this.getName().replace(/\s/g, '-').normalize("NFD").replace(/[\u0300-\u036f]/g, ""));
        formData.append("upload_file", true);

        $.ajax({
            type: "POST",
            url: "index.php?option=com_emundus&controller=messages&task=uploadfiletosend",
            xhr: function () {
                var myXhr = $.ajaxSettings.xhr();
                if (myXhr.upload) {
                    myXhr.upload.addEventListener('progress', that.progressHandling, false);
                }
                return myXhr;
            },
            success: function (data) {
                data = JSON.parse(data);

                if (data.status) {
                    $('#em-attachment-list').append('<li class="list-group-item upload" style="padding: 15px 15px"><div class="value hidden">'+data.file_path+'</div>'+data.file_name+'<span class="badge btn-danger" onClick="removeAttachment(this);"><span class="glyphicon glyphicon-remove"></span></span><span class="badge"><span class="glyphicon glyphicon-saved"></span></span></li>');
                } else {
                    $("#em-file_to_upload").append('<span class="alert"> <?= JText::_('UPLOAD_FAILED'); ?> </span>')
                }
            },
            error: function (error) {
                // handle error
                $("#em-file_to_upload").append('<span class="alert"> <?= JText::_('UPLOAD_FAILED'); ?> </span>')
            },
            async: true,
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            timeout: 60000
        });
    };

    Upload.prototype.progressHandling = function (event) {
        var percent = 0;
        var position = event.loaded || event.position;
        var total = event.total;
        var progress_bar_id = "";
        if (event.lengthComputable) {
            percent = Math.ceil(position / total * 100);
        }
        // update progressbars classes so it fits your code
        $("#em-progress-wrp .progress-bar").css("width", +percent + "%");
        $("#em-progress-wrp .status").text(percent + "%");
    };
</script>