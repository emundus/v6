<?php

defined('_JEXEC') or die;
header('Content-Type: text/html; charset=utf-8');

$document = JFactory::getDocument();

?>


<div class="container">
    <button type="button" class="updateButton" data-toggle="modal"
            data-target="#em-modal-form"><?php echo JText::_("BUTTON_TITLE"); ?></button>
    <span class="alert alert-danger hidden" id="em-action-text"></span>
</div>

<div class="modal fade" id="em-modal-form" tabindex="-1" style="z-index:10" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo JText::_("MODAL_TITLE") . " " . $update['version']; ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><?php echo JText::_("MODAL_BODY"); ?></p>
                <a href=<?php echo "'" . $update['article'] . "'" ?>>
                    <button type="button"
                            class="articleInfoButton"><?php echo JText::_("MODAL_ARTICLE_BUTTON"); ?></button>
                </a>
            </div>
            <div class="modal-footer">
                <button type="button" class="updateAcceptButton"><?php echo JText::_("MODAL_ACCEPT_BUTTON"); ?></button>
				<?php
				if ($update['important'] == 1)
					echo '<button type="button" class="updateIgnoreButton">' . JText::_("MODAL_IGNORE_BUTTON_DESC") . '</button>';
				?>

                <button type="button"
                        class="updateLaterButton"><?php echo JText::_("MODAL_LATER_BUTTON_DESC"); ?></button>
                <div id="updateForm" class="updateForm">
                    <input type="date" id="updateDate" name="update"/>

                    <button class="confirmDate">Confirm</button>
                </div>

            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
    var version = <?php echo "'" . $update['version'] . "'";?>;
    var oldVersion = <?php echo "'" . $siteVersion->version . "'"; ?>;
    var ignoreVersion = <?php echo "'" . $siteVersion->ignore . "'"; ?>;

    /// Accept Update
    jQuery('.updateAcceptButton').on('click', function () {
        jQuery.ajax({
            type: "post",
            url: "index.php?option=com_emundus&controller=update&task=accept",
            dataType: 'json',
            data: ({
                version: version,
                oldversion: oldVersion,
                ignoreversion: ignoreVersion
            }),
            success: function (result) {
                if (result.status) {
                    window.location.reload();
                } else {
                    var actionText = document.getElementById('em-action-text');
                    actionText.classList.remove('hidden');
                    actionText.innerHTML = result.msg;
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
            }
        });
    });

    /// Ignore Update
    jQuery('.updateIgnoreButton').on('click', function () {
        jQuery.ajax({
            type: "post",
            url: "index.php?option=com_emundus&controller=update&task=ignore",
            dataType: 'json',
            data: ({
                version: version,
                oldversion: oldVersion,
                ignoreversion: ignoreVersion
            }),
            success: function (result) {
                if (result.status) {
                    window.location.reload();
                } else {
                    var actionText = document.getElementById('em-action-text');
                    actionText.classList.remove('hidden');
                    actionText.innerHTML = result.msg;
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
            }
        });
    });

    /// Just to show the date form
    jQuery('.updateLaterButton').on('click', function () {
        var updateForm = document.getElementById("updateForm");
        updateForm.style.display = "flex";
    });

    /// Choose Update
    jQuery('.confirmDate').on('click', function () {
        var updateDate = jQuery('#updateDate').val();

        jQuery.ajax({
            type: "post",
            url: "index.php?option=com_emundus&controller=update&task=choose",
            dataType: 'json',
            headers: {
                "Access-Control-Request-Headers": "*",
                "Access-Control-Request-Method": "*"
            },
            data: ({
                version: version,
                oldversion: oldVersion,
                ignoreversion: ignoreVersion,
                updateDate: updateDate
            }),
            success: function (result) {
                if (result.status) {
                    window.location.reload();
                } else {
                    var actionText = document.getElementById('em-action-text');
                    actionText.classList.remove('hidden');
                    actionText.innerHTML = result.msg;
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
            }
        });
    });


</script>

<style type='text/css'>

    .modal-backdrop {
        z-index: 0;
    }

    #updateForm {
        display: none;

    }


</style>

