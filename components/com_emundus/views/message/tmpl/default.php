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
$jinput = JFactory::getApplication()->input;
$itemid = $jinput->getInt('Itemid', null);
$view = $jinput->getString('view', null);
$task = $jinput->getString('task', null);
$tmpl = $jinput->getString('tmpl', null);

// load all of the available messages, categories (to sort messages),attachments, letters.
$m_messages = new EmundusModelMessages();
$message_categories = $m_messages->getAllCategories();
$message_templates = $m_messages->getAllMessages();
$setup_attachments = $m_messages->getAttachmentsByProfiles($this->fnums);
$setup_letters = $m_messages->getAllDocumentsLetters();                 // get all attachments being letter 👻

require_once(JPATH_ROOT . '/components/com_emundus/models/evaluation.php');
$_mEval = new EmundusModelEvaluation;

$_applicant_letters = $_mEval->getLettersByFnums(implode(',', $this->fnums), false);

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
<style>
    #emailForm #mceu_15 {
        display: none;
    }
    .ql-editor{
        height: 300px !important;
        overflow-y: scroll;
    }
    .form-group .email-input-block{
        height: var(--em-coordinator-form-height);
        display: flex;
        align-items: center;
        padding: 0 var(--p-12);
        border: solid 1px var(--em-coordinator-bc);
        border-radius: var(--em-coordinator-form-br);
        background: var(--neutral-0);
    }
    .cc-bcc-mails .items{
        border: 1px solid var(--em-coordinator-bc);
        border-radius: var(--em-coordinator-form-br);
    }
    .cc-bcc-mails .items div[data-value]{
        background: #EBECF0;
        border: unset;
        border-radius: var(--em-coordinator-form-br);
        box-shadow: unset !important;
        padding: var(--p-4) var(--p-8);
    }
    .cc-bcc-mails .items div[data-value] .remove{
        font-size: 16px;
        border: unset;
        padding-right: var(--p-12);
    }
    .ql-editor .mention{
        background: unset;
    }
    .email-input-block::-webkit-scrollbar {
        height: 6px;
    }
    div#mail_from_name,div#mail_subject{
        border-radius: var(--em-coordinator-br);
        border: solid 2px transparent;
    }
    div#mail_from_name:focus,div#mail_subject:focus,div#reply_to_from:focus {
        outline-color: #2E90FA;
    }
    div#mail_from_name:hover,div#mail_subject:hover{
        border-radius: var(--em-coordinator-br);
        border: solid 2px var(--em-coordinator-bc);
    }
    #cc-box-label,#bcc-box-label,#replyto-box-label{
        border-radius: var(--em-coordinator-br);
        width: fit-content;
        padding: var(--p-4) var(--p-8) 5px 0;
        margin-left: 0;
    }
    #cc-box-label:hover,#bcc-box-label:hover {
        background: var(--neutral-300);
    }

    #reply_to_from,#emailForm div#mail_subject{
        min-width: 100%;
    }

    #mail_from_block {
        width: 90%;
    }

    .em-form-recipients {
        height: 44px;
        display: flex !important;
        flex-direction: column;
        justify-content: center;
    }

</style>
<div id="em-email-messages"></div>

<div class="em-modal-sending-emails" id="em-modal-sending-emails">
    <div id="em-sending-email-caption"
         class="em-sending-email-caption"><?= JText::_('COM_EMUNDUS_EMAILS_SENDING_EMAILS'); ?></div>
    <img class="em-sending-email-img" id="em-sending-email-img" src="media/com_emundus/images/sending-email.gif">
</div>

<form id="emailForm" class="em-form-message" name="emailForm">
    <div class="em_email_block" id="em_email_block">

        <div class="form-inline row">

            <!-- Dropdown to select the email categories used. -->
            <div class="form-group col-md-6 col-sm-6 em-form-selectCategory">
                <label for="select_category"><?= JText::_('COM_EMUNDUS_EMAILS_SELECT_CATEGORY'); ?></label>
                <select name="select_category" class="em-border-radius-8 em-mb-16 email-input-block em-w-100" onChange="setCategory(this);">
                    <?php if (!$message_categories) : ?>
                        <option value="%"> <?= JText::_('COM_EMUNDUS_EMAILS_NO_CATEGORIES_FOUND'); ?> </option>
                    <?php else : ?>
                        <option value="%"> <?= JText::_('COM_EMUNDUS_EMAILS_SELECT_CATEGORY'); ?> </option>
                        <?php foreach ($message_categories as $message_category) : ?>
                            <?php if (!empty($message_category)) : ?>
                                <option value="<?= $message_category; ?>"> <?= $message_category; ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Dropdown to select the email template used. -->
            <div class="form-group col-md-6 col-sm-6 em-form-selectTypeEmail">
                <label for="select_template"><?= JText::_('COM_EMUNDUS_EMAILS_SELECT_TEMPLATE'); ?></label>
                <select name="select_template" id="message_template" class="em-border-radius-8 em-mb-16 email-input-block em-w-100" onChange="getTemplate(this);">
                    <?php if (!$message_templates) : ?>
                        <option value="%"> <?= JText::_('COM_EMUNDUS_EMAILS_NO_TEMPLATES_FOUND'); ?> </option>
                    <?php else : ?>
                        <option value="%"> <?= JText::_('COM_EMUNDUS_EMAILS_SELECT_TEMPLATE'); ?> </option>
                        <?php foreach ($message_templates as $message_template) : ?>
                            <option value="<?= $message_template->id; ?>" <?php if($this->data['template'] == $message_template->id) : ?>selected<?php endif;?>> <?= $message_template->subject; ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <a class="em-font-size-14 em-pointer" href="emails"
                   target="_blank"><?= JText::_('COM_EMUNDUS_EMAILS_ADD_TEMPLATE'); ?></a>
            </div>
        </div>

        <input name="mail_from_id" type="hidden" class="inputbox" id="mail_from_id"
               value="<?= $current_user->id; ?>"/>
        <input name="fnums" type="hidden" class="inputbox" id="fnums" value="<?= implode(',', $this->fnums); ?>"/>
        <input name="tags" type="hidden" class="inputbox" id="tags" value=""/>
        <input name="mail_body" type="hidden" class="inputbox" id="mail_body" value=""/>

        <!-- FROM -->
        <div class="form-inline row">
            <div class="form-group em-form-sender em-mt-12 col-md-6 col-sm-6">
                <div class="flex items-center">
                    <label class='em-mr-8' for="mail_from"><?= JText::_('FROM'); ?> :</label>
                    <div id="mail_from_block" class="em-border-radius-8 em-mb-4 email-input-block">
                        <div id="mail_from_name" class="em-p-4-6" contenteditable="true"><?= JFactory::getConfig()->get('fromname') ?></div>
                        <div id="mail_from" class="em-ml-4" contenteditable="false">
                            <em class="em-font-size-14">&lt;<?= JFactory::getConfig()->get('mailfrom') ?>&gt;</em>
                        </div>
                    </div>
                </div>

                <span class="em-font-size-14"><?= JText::_('COM_EMUNDUS_FROM_HELP_TEXT') ?></span>

            </div>

            <div class="form-group em-form-recipients em-mt-12 col-md-6 col-sm-6">

                <!-- List of users / their emails, gotten from the fnums selected. -->
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <label class='em-mr-8 em-cursor-text mb-0'><?= JText::_('COM_EMUNDUS_TO'); ?> :</label>

                        <div class="em-border-radius-8">
		                    <?php if(count($this->users) == 1) : ?>
			                    <?php foreach ($this->users as $user) : ?>

				                    <?php if (!empty($user['email']) && !in_array($user['email'], $email_list)) : ?>
					                    <?php $email_list[] = $user['email']; ?>
                                        <span class="label label-default em-mr-8 em-email-label">
                                    <?= $user['name'] . ' <em class="em-font-size-14">&lt;' . $user['email'] . '&gt;</em>'; ?>
                                </span>

                                        <input type="hidden" name="ud[]" id="ud" value="<?= $user['id']; ?>"/>
				                    <?php endif; ?>

			                    <?php endforeach; ?>
		                    <?php else : ?>
                                <div class="flex items-center">
                        <span class="label label-default em-mr-8 em-email-label">
                                    <?= $this->users[0]['name'] . ' <em class="em-font-size-14">&lt;' . $this->users[0]['email'] . '&gt;</em>'; ?>
                        </span>
                                    <span class="label label-default em-mr-8 em-email-label">
                                +<?= count($this->users)-1 ?>
                            </span>
                                </div>
		                    <?php endif; ?>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <div id="cc-box-label" class="em-flex-row em-pointer" onclick="openCC()">
                            <label class="em-mb-0-important"><?= JText::_('COM_EMUNDUS_EMAILS_CC_LABEL'); ?></label>
                            <span id="cc-icon" class="material-icons-outlined">chevron_right</span>
                        </div>

                        <div id="bcc-box-label" class="em-flex-row em-pointer" onclick="openBCC()">
                            <label class="em-mb-0-important"><?= JText::_('COM_EMUNDUS_EMAILS_BCC_LABEL'); ?></label>
                            <span id="bcc-icon" class="material-icons-outlined">chevron_right</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Add current user to Cc -->
        <div id="cc-box" class="input-group form-inline col-md-12 em-mt-12">
            <label for="select_action_tags" class="cc-box-label"><?= JText::_('COM_EMUNDUS_EMAILS_CC_LABEL'); ?></label>
            <input type="text" id="cc-mails" class="cc-bcc-mails">
        </div>

        <!-- Add current user to Bcc -->
        <div id="bcc-box" class="input-group form-inline col-md-12 em-mt-12">
            <label for="select_action_tags" class="bcc-box-label"><?= JText::_('COM_EMUNDUS_EMAILS_BCC_LABEL'); ?></label>
            <input type="text" id="bcc-mails" class="cc-bcc-mails">
        </div>

        <!-- REPLY TO -->
        <div id="replyto-box" class="form-group em-form-sender em-mt-12">
            <div id="replyto-box-label" class="em-flex-row em-pointer" onclick="openReplyTo()">
                <label class="em-mb-0-important" for="reply_to_from"><?= JText::_('COM_EMUNDUS_EMAILS_FROM_REPLY_TO'); ?></label>
                <span id="replyto-icon" class="material-icons-outlined">chevron_right</span>
            </div>
            <div id="reply_to_div" style="display: none">
                <div id="reply_to_block" class="em-border-radius-8 em-mb-4 email-input-block em-cursor-text">
                    <div id="reply_to_from" class="em-p-4-6 em-cursor-text" contenteditable="true"></div>
                </div>
                <span class="em-font-size-14"><?= JText::_('COM_EMUNDUS_EMAILS_REPLY_TO_HELP_TEXT') ?></span>
            </div>
        </div>


        <div class="form-group em-form-subject em-mt-12">
            <label class='em-mr-8' for="mail_from"><?= JText::_('COM_EMUNDUS_EMAILS_SUBJECT'); ?> :</label>
            <div class="em-border-radius-8 email-input-block em-mb-12">
                <div id="mail_subject"
                     class="em-p-4-6"
                     contenteditable="true"><?= JFactory::getConfig()->get('sitename'); ?></div>
            </div>




            <!-- Email WYSIWYG -->
            <div id="editor">
            </div>

            <!-- TIP -->
            <p class="em-text-neutral-600 em-mt-8 em-font-size-14t">
                <?= JText::_('COM_EMUNDUS_ONBOARD_VARIABLESTIP'); ?>
            </p>
        </div>

        <div class="form-group">
            <br>
            <hr>
        </div>

        <div class="form-inline row em-form-attachments">
            <div class="form-group col-sm-12 col-md-5">
                <label for="em-select_attachment_type"><?= JText::_('COM_EMUNDUS_EMAILS_SELECT_ATTACHMENT_TYPE'); ?></label>
                <select name="em-select_attachment_type" id="em-select_attachment_type" class="em-border-radius-8 em-mb-16 email-input-block em-w-100 download"
                        onChange="toggleAttachmentType(this);">
                    <option value=""> <?= JText::_('COM_EMUNDUS_PLEASE_SELECT'); ?> </option>
                    <option value="upload"> <?= JText::_('COM_EMUNDUS_UPLOAD'); ?> </option>
                    <?php if (EmundusHelperAccess::asAccessAction(4, 'r')) : ?>
                        <option value="candidate_file"> <?= JText::_('COM_EMUNDUS_EMAILS_CANDIDATE_FILE'); ?> </option>
                    <?php endif; ?>
                    <?php if (!empty($_applicant_letters)) { ?>
                        <?php if (EmundusHelperAccess::asAccessAction(4, 'c') && EmundusHelperAccess::asAccessAction(27, 'c')) : ?>
                            <option value="setup_letters"> <?= JText::_('COM_EMUNDUS_EMAILS_SETUP_LETTERS_ATTACH'); ?> </option>
                        <?php endif; ?>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group col-sm-12 col-md-7">
                <!-- Upload a file from computer -->
                <div class="hidden upload-file em-form-attachments-uploadFile" id="upload_file">

                    <div class="file-browse">
                        <span id="em-filename"><?= JText::_('COM_EMUNDUS_ATTACHMENTS_FILE_NAME'); ?></span>

                        <label for="em-file_to_upload"
                               type="button"><?= JText::_('COM_EMUNDUS_ATTACHMENTS_SELECT_FILE_TO_UPLOAD') ?>
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
                        <label for="em-select_candidate_file"><?= JText::_('COM_EMUNDUS_UPLOAD'); ?></label>
                        <select id="em-select_candidate_file" name="candidate_file" class="form-control download"
                                onchange="addFile();">
                            <?php if (!$setup_attachments) : ?>
                                <option value="%"> <?= JText::_('COM_EMUNDUS_EMAILS_NO_FILES_FOUND'); ?> </option>
                            <?php else : ?>
                                <option value="%"> <?= JText::_('JGLOBAL_SELECT_AN_OPTION'); ?> </option>
                            <?php endif; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <!-- Get a file from setup_letters -->
                <?php if (!empty($_applicant_letters)) { ?>
                    <?php if (EmundusHelperAccess::asAccessAction(4, 'c') && EmundusHelperAccess::asAccessAction(27, 'c')) : ?>
                        <div class="hidden em-form-attachments-setupLetters" id="setup_letters">
                            <label for="em-select_setup_letters"><?= JText::_('COM_EMUNDUS_UPLOAD'); ?></label>
                            <select id="em-select_setup_letters" name="setup_letters" class="form-control"
                                    onchange="addFile();">
                                <?php if (!$setup_letters) : ?>
                                    <option value="%"> <?= JText::_('COM_EMUNDUS_EMAILS_NO_FILES_FOUND'); ?> </option>
                                <?php else : ?>
                                    <option value="%"> <?= JText::_('COM_EMUNDUS_PLEASE_SELECT'); ?> </option>
                                    <?php foreach ($setup_letters as $letter) : ?>
                                        <option value="<?= $letter->id; ?>"> <?= $letter->value; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                <?php } ?>
            </div>
        </div>
    </div>
    <br>
    <div class="form-group attachment em-form-attachments-location">
        <ul class="list-group" id="em-attachment-list">
            <!-- Files to be attached will be added here. -->
        </ul>
    </div>

    <input type="hidden" name="task" value=""/>
</form>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>
<script type="text/javascript">
    var DirectionAttribute = Quill.import('attributors/attribute/direction');
    Quill.register(DirectionAttribute, true);
    var AlignClass = Quill.import('attributors/class/align');
    Quill.register(AlignClass, true);
    var BackgroundClass = Quill.import('attributors/class/background');
    Quill.register(BackgroundClass, true);
    var ColorClass = Quill.import('attributors/class/color');
    Quill.register(ColorClass, true);
    var DirectionClass = Quill.import('attributors/class/direction');
    Quill.register(DirectionClass, true);
    var FontClass = Quill.import('attributors/class/font');
    Quill.register(FontClass, true);
    var SizeClass = Quill.import('attributors/class/size');
    Quill.register(SizeClass, true);
    var AlignStyle = Quill.import('attributors/style/align');
    Quill.register(AlignStyle, true);
    var BackgroundStyle = Quill.import('attributors/style/background');
    Quill.register(BackgroundStyle, true);
    var ColorStyle = Quill.import('attributors/style/color');
    Quill.register(ColorStyle, true);
    var DirectionStyle = Quill.import('attributors/style/direction');
    Quill.register(DirectionStyle, true);
    var FontStyle = Quill.import('attributors/style/font');
    Quill.register(FontStyle, true);
    var SizeStyle = Quill.import('attributors/style/size');
    Quill.register(SizeStyle, true);

    let editor = null;
    // update css
    $('#cc-mails-selectized').css('vertical-align', '-10px');
    $('#bcc-mails-selectized').css('vertical-align', '-10px');


    // add cc
    var $selectize_cc = $("#cc-mails").selectize({
        plugins: ["remove_button"],
        create: true,
        preload: true,
        placeholder: '',
        render: {
            item: function (data, escape) {
                var val = data.value;
                return '<div>' +
                    '<span class="title">' +
                    '<span class="name">' + escape(val.substring(val.indexOf(":") + 1)) + '</span>' +
                    '</span>' +
                    '</div>';
            }
        },
        onItemAdd: function(value, $item) {
            let email = value.substring(value.indexOf(":") + 1);
            email = email.trim();

            const regex = /^\S{1,64}@\S{1,255}\.\S{1,255}$/;
            if (!regex.test(email)) {
                this.removeItem(value);
            }
        }
    });

    // add bcc
    var $selectize_bcc = $("#bcc-mails").selectize({
        plugins: ["remove_button"],
        create: true,
        preload: true,
        placeholder: '',
        render: {
            item: function (data, escape) {
                var val = data.value;
                return '<div>' +
                    '<span class="title">' +
                    '<span class="name">' + escape(val.substring(val.indexOf(":") + 1)) + '</span>' +
                    '</span>' +
                    '</div>';
            }
        },
        onItemAdd: function(value, $item) {
            let email = value.substring(value.indexOf(":") + 1);
            email = email.trim();

            const regex = /^\S{1,64}@\S{1,255}\.\S{1,255}$/;
            if (!regex.test(email)) {
                this.removeItem(value);
            }
        }
    });

    // get attachments by profiles (fnums)
    let fnums = $('#fnums').val();
    $.ajax({
        type: 'post',
        url: 'index.php?option=com_emundus&controller=messages&task=getattachmentsbyprofiles',
        dataType: 'json',
        data: {
            fnums: fnums
        },
        success: function (data) {
            /// get all profile id
            let profile_id = Object.keys(data.attachments);
            // $('#em-select_candidate_file').append('<option value="0" selected>'+Joomla.JText._('JGLOBAL_SELECT_AN_OPTION')+'</option>');

            // attach profile id to #em-select_candidate_file
            profile_id.forEach(profile => {
                /// get profile label
                let profile_label = data.attachments[profile].label;

                $('#em-select_candidate_file').append('<optgroup label ="_______' + profile_label + '_______" style="color:#16afe1">');

                ///get all attachments for each profile_id
                let letters = data.attachments[profile].letters;
                letters.forEach(letter => {
                    $('#em-select_candidate_file').append('<option value="' + letter.letter_id + '">' + letter.letter_label + '</option>');
                })
            })

        },
        error: function (jqXHR) {
            console.log(jqXHR.responseText);
        }
    })

    $('#cc-box').css('display', 'none');
    $('#bcc-box').css('display', 'none');


    // Editor loads disabled by default, we apply must toggle it active on page load.
    $(document).ready(function () {
        initQuill();

	    <?php if(!empty($this->data['mail_subject'])) : ?>
            $("#mail_subject").text("<?= $this->data['mail_subject'] ?>");
        <?php endif; ?>

        <?php if(!empty($this->data['mail_from_name'])) : ?>
            $("#mail_from_name").text("<?= $this->data['mail_from_name'] ?>");
	    <?php endif; ?>

        <?php if(!empty($this->data['reply_to_from'])) : ?>
            $("#reply_to_from").html("<?= $this->data['reply_to_from'] ?>");
        <?php endif; ?>
    });

    // Change file upload string to selected file and reset the progress bar.
    $('#em-file_to_upload').change(function () {
        $('#em-filename').html(this.value.match(/([^\/\\]+)$/)[1]);
        $("#em-progress-wrp .progress-bar").css("width", +0 + "%");
        $("#em-progress-wrp .status").text(0 + "%");
    });

    function openCC()
    {
        let cc = $('#cc-box');
        let cc_input = $('#cc-box .selectize-control');
        if(cc.css('display') === 'block') {
            cc.css('display', 'none');
            cc_input.css('display', 'none');
            $('#cc-icon').css('transform', 'rotate(0deg)');
        } else {
            cc.css('display', 'block');
            cc_input.css('display', 'block');
            $('#cc-icon').css('transform', 'rotate(90deg)');
        }
    }

    function openBCC()
    {
        let bcc = $('#bcc-box');
        let bcc_input = $('#bcc-box .selectize-control');
        if(bcc.css('display') === 'block') {
            bcc.css('display', 'none');
            bcc_input.css('display', 'none');
            $('#bcc-icon').css('transform', 'rotate(0deg)');
        } else {
            bcc.css('display', 'block');
            bcc_input.css('display', 'block');
            $('#bcc-icon').css('transform', 'rotate(90deg)');
        }
    }

    function openReplyTo()
    {
        let replyto = $('#reply_to_div');
        if(replyto.css('display') === 'block') {
            replyto.css('display', 'none');
            $('#replyto-icon').css('transform', 'rotate(0deg)');
        } else {
            replyto.css('display', 'block');
            $('#replyto-icon').css('transform', 'rotate(90deg)');
        }
    }

    function initQuill() {
        let variables = [];

        fetch('index.php?option=com_emundus&controller=settings&task=geteditorvariables')
            .then(function (response) {
                return response.json();
            })
            .then(function (variables) {
                variables = variables.data;

                let options = {
                    modules: {
                        toolbar: [
                            ['bold', 'italic', 'underline', 'strike'],
                            ['link'],

                            [{'header': 1}, {'header': 2}],
                            [{'list': 'ordered'}, {'list': 'bullet'}],
                            [{'indent': '-1'}, {'indent': '+1'}],
                            [{'size': ['small', false, 'large', 'huge']}],

                            [{'color': []}],
                            [{'align': []}],
                        ],
                        imageResize: {},
                        mention: null
                    },
                    placeholder: '',
                    theme: 'snow'
                };
                options.modules.mention = {
                    allowedChars: /^[A-Za-z\sÅÄÖåäö]*$/,
                    mentionDenotationChars: ["/"],
                    source: (searchTerm, renderList, mentionChar) => {
                        let values;

                        if (mentionChar === "/") {
                            values = variables;
                        }

                        if (searchTerm.length === 0) {
                            renderList(values, searchTerm);
                        } else {
                            const matches = [];
                            for (let i = 0; i < values.length; i++)
                                if (
                                    ~values[i].value.toLowerCase().indexOf(searchTerm.toLowerCase())
                                )
                                    matches.push(values[i]);
                            renderList(matches, searchTerm);
                        }
                    },
                    onSelect: (item, insertItem) => {
                        insertItem(
                            {
                                denotationChar: "",
                                id: item.id,
                                value: "[" + item.value + ']',
                            },
                            true
                        );
                    },
                    renderItem: (item, searchTerm) => {
                        return `<div><p>${item.value}</p><p class="em-font-size-12">${item.description}</p></div>`;
                    }
                }
                editor = new Quill('#editor', options);
                const editorContent = "<?php echo addslashes($this->body) ?>";
                let delta = editor.clipboard.convert(editorContent);
                editor.setContents(delta);
                $('#mail_body').val(editor.root.innerHTML);

                editor.on('editor-change', (eventName, ...args) => {
                    if (eventName === 'text-change') {
                        if(editor.root.innerHTML === null){
                            editor.root.innerHTML = '';
                        }
                        $('#mail_body').val(editor.root.innerHTML);
                    }
                });
            });
    }

    // Loads the template and updates the WYSIWYG editor
    function getTemplate(select) {
        // clear CC and BCC
        var $select_cc = $(document.getElementById('cc-mails'));
        var selectize_cc = $select_cc[0].selectize;
        selectize_cc.clear();

        var $select_bcc = $(document.getElementById('bcc-mails'));
        var selectize_bcc = $select_bcc[0].selectize;
        selectize_bcc.clear();

        $('.cc-bcc-mails .plugin-remove_button').empty();

        // clear CC and BCC placeholder
        $("label[for='cc-emails']").empty();
        $("label[for='bcc-emails']").empty();

        // clear em-attachment-list
        $('#em-attachment-list').empty();

        // call ajax to getemailbyid
        $('#can-val').css('cursor', '');
        $('#can-val .btn-success').attr('disabled', false);

        /// reset #em-select_candidate_file
        $('#em-select_candidate_file option').each(function () {
            if ($(this).is(":disabled")) {
                $(this).prop('disabled', false);
            }
            $(this).attr('style', '');
            $('#em-select_candidate_file option:selected').removeAttr('selected');
        })

        /// reset #em-select_setup_letters
        $('#em-select_setup_letters option').each(function () {
            if ($(this).is(":disabled")) {
                $(this).prop('disabled', false);
            }
            $(this).attr('style', ''); /// reset style
            $('#em-select_setup_letters option:selected').removeAttr('selected');
        })

        /// reset #em-select_attachment_type
        $('#em-select_attachment_type option:selected').removeAttr('selected');

        if(select.value !== '%') {
            $.ajax({
                type: 'POST',
                url: 'index.php?option=com_emundus&controller=email&task=getemailbyid',
                dataType: 'JSON',
                data: {
                    id: select.value
                },
                success: function (data) {
                    if (data.status) {
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
                                selectize_cc.addOption({
                                    value: "CC: " + cc,
                                    text: cc
                                });
                                selectize_cc.addItem("CC: " + cc);
                            })

                            // bcc
                            receiver_bcc.forEach(bcc => {
                                selectize_bcc.addOption({
                                    value: "BCC: " + bcc,
                                    text: bcc
                                });
                                selectize_bcc.addItem("BCC: " + bcc);
                            })

                            if (fabrik_cc.length > 0 && fabrik_cc != "" && fabrik_cc != null && fabrik_cc != undefined) {
                                var REGEX_EMAIL = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                                // call to controller --> get fabrik value
                                $.ajax({
                                    type: 'post',
                                    url: 'index.php?option=com_emundus&controller=files&task=getfabrikvaluebyid',
                                    dataType: 'json',
                                    data: {
                                        elements: fabrik_cc
                                    },
                                    success: function (data) {
                                        let emails = [];

                                        for (email in data.data) {
                                            if (REGEX_EMAIL.test(data.data[email])) {
                                                emails.push(data.data[email]);
                                                selectize_cc.addOption({
                                                    value: "CC: " + data.data[email],
                                                    text: data.data[email]
                                                });
                                                selectize_cc.addItem("CC: " + data.data[email]);
                                            }
                                        }

                                    },
                                    error: function (jqXHR) {
                                        console.log(jqXHR.responseText);
                                    }
                                })
                            }

                            // do the same thing with bcc receivers
                            if (fabrik_bcc.length > 0 && fabrik_bcc != "" && fabrik_bcc != null && fabrik_bcc != undefined) {
                                var REGEX_EMAIL = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                                // call to controller --> get fabrik value
                                $.ajax({
                                    type: 'post',
                                    url: 'index.php?option=com_emundus&controller=files&task=getfabrikvaluebyid',
                                    dataType: 'json',
                                    data: {
                                        elements: fabrik_bcc
                                    },
                                    success: function (data) {
                                        let emails = [];

                                        for (email in data.data) {
                                            if (REGEX_EMAIL.test(data.data[email])) {
                                                emails.push(data.data[email]);
                                                selectize_bcc.addOption({
                                                    value: "BCC: " + data.data[email],
                                                    text: data.data[email]
                                                });
                                                selectize_bcc.addItem("BCC: " + data.data[email]);
                                            }
                                        }
                                    },
                                    error: function (jqXHR) {
                                        console.log(jqXHR.responseText);
                                    }
                                })
                            }
                        }

                        var email_block = document.getElementById("em_email_block");

                        // email raw info
                        let email = data.data.email;

                        $("#mail_subject").text(email.subject);

                        $("#reply_to_from").html(email.emailfrom);
                        if(email.name !== '')
                        {
                            $("#mail_from_name").text(email.name);
                        } else {
                            $("#mail_from_name").text("<?= JFactory::getConfig()->get('fromname'); ?>");
                        }


                        let delta = editor.clipboard.convert(email.message);
                        editor.setContents(delta);

                        /// get letter attachments block
                        if (data.data.letter_attachment !== null) {
                            let letters = data.data.letter_attachment;
                            letters.forEach(letter => {
                                $('#em-attachment-list').append('' +
                                    '<li class="list-group-item setup_letters" style="padding: 6px 12px; display: flex; align-content: center; justify-content: space-between">' +
                                    '<div class="value hidden">' + letter.id + '</div>' + letter.value +
                                    '<div>' +
                                    '<span class="badge">' + '<span class="glyphicon glyphicon-envelope">' + '</span>' + '</span>' +
                                    '<span class="badge btn-danger" onClick="removeAttachment(this);">' + '<span class="glyphicon glyphicon-remove"></span>' + '</span>' +
                                    '</div>' +
                                    '</li>');
                                /// set selected letter
                                $('#em-select_setup_letters option[value="' + letter.id + '"]').prop('disabled', true);
                                $('#em-select_setup_letters option[value="' + letter.id + '"]').css('font-style', 'italic');
                            })
                        }

                        /// get candidat attachments block * check in the user permission *
				        <?php if (EmundusHelperAccess::asAccessAction(4, 'r')) : ?>
                        if (data.data.candidate_attachment !== null) {
                            let attachments = data.data.candidate_attachment;
                            attachments.forEach(attachment => {
                                $('#em-attachment-list').append('' +
                                    '<li class="list-group-item candidate_file" style="padding: 6px 12px; display: flex; align-content: center; justify-content: space-between">' +
                                    '<div class="value hidden">' + attachment.id + '</div>' + attachment.value +
                                    '<div>' +
                                    '<span class="badge">' + '<span class="glyphicon glyphicon-paperclip">' + '</span>' + '</span>' +
                                    '<span class="badge btn-danger" onClick="removeAttachment(this);">' + '<span class="glyphicon glyphicon-remove"></span>' + '</span>' +
                                    '</div>' +
                                    '</li>');
                                /// set selected letter
                                $('#em-select_candidate_file option[value="' + attachment.id + '"]').prop('disabled', true);
                                $('#em-select_candidate_file option[value="' + attachment.id + '"]').css('font-style', 'italic');
                            })
                        }
				        <?php endif; ?>
                    } else {
                        /// lock send button
                        $('#can-val').css('cursor', 'not-allowed');
                    }
                },
                error: function (jqXHR) {
                    console.log(jqXHR.responseText);
                }
            })
        } else {
            $("#mail_subject").text("<?= JFactory::getConfig()->get('sitename'); ?>");
            $("#reply_to_from").text("");
            $("#mail_from_name").text("<?= JFactory::getConfig()->get('fromname'); ?>");

            let delta = editor.clipboard.convert('<p>Bonjour [NAME],</p>');
            editor.setContents(delta);
        }
    }

    // Used for toggling the options dipslayed in the message templates dropdown.
    function setCategory(element) {

        if (element.value == "%")
            category = 'all';
        else
            category = element.value;

        $.ajax({
            type: "GET",
            url: "index.php?option=com_emundus&controller=messages&task=setcategory&category=" + category,
            success: function (data) {

                data = JSON.parse(data);

                if (data.status) {

                    var $el = $("#message_template");
                    $('#message_template option:gt(0)').remove();

                    $.each(data.templates, function (key, value) {
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


    // Used for reseting a File upload input.
    function resetFileInput(e) {
        e.wrap('<form>').closest('form').get(0).reset();
        e.unwrap();
    }


    // Change the attachment type being uploaded.
    function toggleAttachmentType(toggle) {

        switch (toggle.value) {

            case 'upload':
                $('#upload_file').removeClass('hidden');
                $('#candidate_file').addClass('hidden');
                $('#setup_letters').addClass('hidden');
                $('#uploadButton').removeClass('hidden');
                break;

            case 'candidate_file':
                resetFileInput($('#upload_file'));
                $('#upload_file').addClass('hidden');
                $('#candidate_file').removeClass('hidden');
                $('#setup_letters').addClass('hidden');
                $('#uploadButton').removeClass('hidden');
                break;

            case 'setup_letters':
                resetFileInput($('#upload_file'));
                $('#upload_file').addClass('hidden');
                $('#candidate_file').addClass('hidden');
                $('#setup_letters').removeClass('hidden');
                $('#uploadButton').removeClass('hidden');
                break;

            default:
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

            case 'upload':

                // We need to get the file uploaded by the user.
                var file = $("#em-file_to_upload")[0].files[0];
                var upload = new Upload(file);
                // Verification of style size and type can be done here.
                upload.doUpload();

                break;


            case 'candidate_file':

                // we just need to note the reference to the setup_attachment file.
                var file = $('#em-select_candidate_file :selected');

                var alreadyPicked = $('#em-attachment-list li.candidate_file').find('.value:contains("' + file.val() + '")');

                if (alreadyPicked.length == 1) {

                    // Flash the line a certain color to show it's already picked.
                    alreadyPicked.parent().css("background-color", "#C5EFF7");
                    alreadyPicked.parent().css("display", "flex");
                    alreadyPicked.parent().css("align-items", "center");
                    alreadyPicked.parent().css("justify-content", "space-between");
                    alreadyPicked.parent().css("padding", "6px 12px");

                    setTimeout(function () {
                        alreadyPicked.parent().css("background-color", "");
                    }, 500);

                    // $('#em-select_candidate_file option[value="' + file.val() + '"]').css('font-style', 'italic');
                    // $('#em-select_candidate_file option[value="' + file.val() + '"]').prop('disabled', true);
                } else {

                    // Disable the file from the dropdown.
                    file.prop('disabled', true);
                    file.css('font-style', 'italic');
                    // Add the file to the list.
                    $('#em-attachment-list').append(
                        '<li class="list-group-item candidate_file" style="padding: 6px 12px; display: flex; align-content: center; justify-content: space-between">' +
                        '<div class="value hidden">' + file.val() + '</div>' + file.text() +
                        '<div>' +
                        '<span class="badge"><span class="glyphicon glyphicon-paperclip"></span></span>' +
                        '<span class="badge btn-danger" onclick="removeAttachment(this);"><span class="glyphicon glyphicon-remove"></span></span>' +
                        '</div>' +
                        '</li>');

                    // $('#em-select_candidate_file [value="' + file.val() + '"]').prop('disabled', true);
                    // $('#em-select_candidate_file option[value="' + file.val() + '"]').css('font-style', 'italic');
                }

                $('#em-select_candidate_file [value="' + file.val() + '"]').prop('disabled', true);
                $('#em-select_candidate_file option[value="' + file.val() + '"]').css('font-style', 'italic');

                break;

            case 'setup_letters':

                // We need to note the reference to the setup_letters file.
                var file = $('#em-select_setup_letters :selected');
                // var alreadyPicked = $('#em-attachment-list li.setup_letters').find('.value:contains("'+file.val()+'")');

                var alreadyPicked = $('#em-attachment-list li.setup_letters').find('.value:contains("' + file.val() + '")');

                if (alreadyPicked.length == 1) {

                    // Flash the line a certain color to show it's already picked.
                    alreadyPicked.parent().css("background-color", "#C5EFF7");
                    alreadyPicked.parent().css("display", "flex");
                    alreadyPicked.parent().css("align-items", "center");
                    alreadyPicked.parent().css("justify-content", "space-between");
                    alreadyPicked.parent().css("padding", "6px 12px");

                    setTimeout(function () {
                        alreadyPicked.parent().css("background-color", "");
                    }, 500);

                    $('#em-select_setup_letters option[value="' + file.val() + '"]').prop('disabled', true);
                    $('#em-select_setup_letters option[value="' + file.val() + '"]').css('font-style', 'italic');

                } else {

                    // Disable the file from the dropdown.
                    file.prop('disabled', true);
                    file.css('font-style', 'italic');
                    // Add the file to the list.
                    $('#em-attachment-list').append(
                        '<li class="list-group-item setup_letters" style="padding: 6px 12px; display: flex; align-content: center; justify-content: space-between">' +
                        '<div class="value hidden">' + file.val() + '</div>' + file.text() +
                        '<div>' +
                        '<span class="badge"><span class="glyphicon glyphicon-envelope"></span></span>' +
                        '<span class="badge btn-danger" onclick="removeAttachment(this);"><span class="glyphicon glyphicon-remove"></span></span>' +
                        '</div>' +
                        '</li>');

                }

                break;

            default:

                // Nothing selected, this case should not happen.
                $("#em-attachment-list").append('<span class="alert alert-danger"> <?= JText::_('ERROR'); ?> </span>')

                break;

        }
    }


    function removeAttachment(element) {

        element = $(element);

        if (element.parent().parent().hasClass('candidate_file')) {
            // Remove 'disabled' attr from select options.
            $('#em-select_candidate_file option[value="' + element.parent().parent().find('.value').text() + '"]').prop('disabled', false);

            // reset css style
            $('#em-select_candidate_file option[value="' + element.parent().parent().find('.value').text() + '"]').removeAttr('style');
        } else if (element.parent().parent().hasClass('setup_letters')) {
            // Remove 'disabled' attr from select options.
            $('#em-select_setup_letters option[value="' + element.parent().parent().find('.value').text() + '"]').prop('disabled', false);

            // reset css style
            $('#em-select_setup_letters option[value="' + element.parent().parent().find('.value').text() + '"]').removeAttr('style');
        }

        $(element).parent().parent().remove();
    }


    // Helper function for uploading a file via AJAX.
    var Upload = function (file) {
        this.file = file;
    };

    Upload.prototype.getType = function () {
        return this.file.type;
    };
    Upload.prototype.getSize = function () {
        return this.file.size;
    };
    Upload.prototype.getName = function () {
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
                    $('#em-attachment-list').append(
                        '<li class="list-group-item upload" style="padding: 6px 12px; display: flex; align-content: center; justify-content: space-between">' +
                        '<div class="value hidden">' + data.file_path + '</div>' + data.file_name +
                        '<div>' +
                        '<span class="badge"><span class="glyphicon glyphicon-saved"></span></span>' +
                        '<span class="badge btn-danger" onClick="removeAttachment(this);"><span class="glyphicon glyphicon-remove"></span></span>' +
                        '</div>' +
                        '</li>');
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
