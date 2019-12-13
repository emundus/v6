jQuery(document).ready(function($){

    $('.check-import-hidden').each(function (index) {
        html = $(this).parent().html();
        selector = $(this).parents('.control-group');
        selector.empty();
        selector.append(html);
    });

    $('#jform_dropbox_authorization_code').change(function() {

        var code = $(this).val();
        $.ajax({
            url: "index.php?option=com_dropfiles&task=config.getDropboxToken",
            type: "POST",
            dataType : 'json',
            data : {authencode : code}
        }).done(function(res){
            location.href = location.href;
        });

    });
    init_notification();

    $('input[name="jform[add_event]"],input[name="jform[edit_event]"],input[name="jform[download_event]"],input[name="jform[delete_event]"]').click(function() {
        init_notification();
    });


    function init_notification() {
        var add_event = $('input[name="jform[add_event]"]:checked');
        var edit_event = $('input[name="jform[edit_event]"]:checked');
        var download_event = $('input[name="jform[download_event]"]:checked');
        var delete_event = $('input[name="jform[delete_event]"]:checked');
        if (add_event.val() == 1) {
            $('#jform_add_event_subject').closest('.control-group').show();
            $('#jform_add_event_additional_email').closest('.control-group').show();
            $('#jform_add_event_editor').closest('.control-group').show();
        } else {
            $('#jform_add_event_subject').closest('.control-group').hide();
            $('#jform_add_event_additional_email').closest('.control-group').hide();
            $('#jform_add_event_editor').closest('.control-group').hide();
        }
        if (edit_event.val() == 1) {
            $('#jform_edit_event_subject').closest('.control-group').show();
            $('#jform_edit_event_additional_email').closest('.control-group').show();
            $('#jform_edit_event_editor').closest('.control-group').show();
        } else {
            $('#jform_edit_event_subject').closest('.control-group').hide();
            $('#jform_edit_event_additional_email').closest('.control-group').hide();
            $('#jform_edit_event_editor').closest('.control-group').hide();
        }
        if (download_event.val() == 1) {
            $('#jform_download_event_subject').closest('.control-group').show();
            $('#jform_download_event_additional_email').closest('.control-group').show();
            $('#jform_download_event_editor').closest('.control-group').show();
        } else {
            $('#jform_download_event_subject').closest('.control-group').hide();
            $('#jform_download_event_additional_email').closest('.control-group').hide();
            $('#jform_download_event_editor').closest('.control-group').hide();
        }
        if (delete_event.val() == 1) {
            $('#jform_delete_event_subject').closest('.control-group').show();
            $('#jform_delete_event_additional_email').closest('.control-group').show();
            $('#jform_delete_event_editor').closest('.control-group').show();
        } else {
            $('#jform_delete_event_subject').closest('.control-group').hide();
            $('#jform_delete_event_additional_email').closest('.control-group').hide();
            $('#jform_delete_event_editor').closest('.control-group').hide();
        }
    }

    $('a[href="#singlefile"]').parent().remove();
    $('#singlefile').detach().appendTo('#main');

    $('a[href="#google_drive"]').parent().remove();
    $('#google_drive').detach().appendTo('#cloud_connection');

    $('a[href="#onedrive_cloud"]').parent().remove();
    $('#onedrive_cloud').detach().appendTo('#cloud_connection');

    $('a[href="#dropbox_cloud"]').parent().remove();
    $('#dropbox_cloud').detach().appendTo('#cloud_connection');

    $('a[href="#advanced"]').parent().remove();
    $('#advanced').detach().appendTo('#main');

    $('a[href="#customthemelist"]').parent().remove();
    $('#customthemelist').detach().appendTo('#clonetheme');

    $('#docman_import_button').on('click', function (e) {

        e.preventDefault();
        var cat = $('#jform_doccat').val();
        if (cat == 0 ) {
            alert('please select a category to import');
            return false;
        } else {
            $('#docman_import_button').attr('disabled', true);
            $('#docman_import_button').html('Importing');
            $.ajax({
                url: "index.php?option=com_dropfiles&task=config.docimport",
                type: "POST",
                dataType : 'json',
                data : {doccat : cat}
            }).done(function(res){
                $('#docman_import_button').html('Done');
                alert('Docman import run with success!');
            });
        }
    })

    $('#jdownloads_import_button').on('click', function (e) {

        e.preventDefault();
        var cat = $('#jform_jdowncat').val();
        if (cat == 0 ) {
            alert('please select a category to import');
            return false;
        } else {
            $('#jdownloads_import_button').attr('disabled', true);
            $('#jdownloads_import_button').html('Importing');
            $.ajax({
                url: "index.php?option=com_dropfiles&task=config.downimport",
                type: "POST",
                dataType : 'json',
                data : {doccat : cat}
            }).done(function(res){
                $('#jdownloads_import_button').html('Done');
                alert('Jdownloads import run with success!');
            });
        }
    });

    $('#edoc_import_button').on('click', function (e) {

        e.preventDefault();
        var cat = $('#jformedocmancategory').val();
        if (cat == 0 ) {
            alert('please select a category to import');
            return false;
        } else {
            $('#edoc_import_button').attr('disabled', true);
            $('#edoc_import_button').html('Importing');
            $.ajax({
                url: "index.php?option=com_dropfiles&task=config.eDocImport",
                type: "POST",
                dataType : 'json',
                data : {doccat : cat}
            }).done(function(res){
                $('#edoc_import_button').html('Done');
                alert('eDocman import run with success!');
            });
        }
    })

    $('#phocadownload_import_button').on('click', function (e) {

        e.preventDefault();
        var cat = $('#jform_phocadownloadcat').val();
        if (cat == 0 ) {
            alert('please select a category to import');
            return false;
        } else {
            $('#phocadownload_import_button').attr('disabled', true);
            $('#phocadownload_import_button').html('Importing');
            $.ajax({
                url: "index.php?option=com_dropfiles&task=config.phocaDownloadImport",
                type: "POST",
                dataType : 'json',
                data : {phocadownloadcat : cat}
            }).done(function(res){
                $('#phocadownload_import_button').html('Done');
                alert('Phoca download import run with success!');
            });
        }
    })

    $(document).on('click', '#dropfiles_btn_google_changes', function(e) {
        e.preventDefault();

        var csrfToken = $(e.target).data('csrf');

        Joomla.request({
            url: "index.php?option=com_dropfiles&task=config.googleStopWatchChanges",
            type: "POST",
            headers: {
                'X-Csrf-Token': csrfToken
            },
            onSuccess: function(res, xhr){
                response = JSON.parse(res);
                if (response.response === true) {
                    setTimeout(function() {
                        document.location.reload();
                    }, 500);
                } else {
                    alert('Something wrong! Check Console Tab for details.');
                    console.log(xhr);
                }
            },
            onError: function (xhr) {
                alert('Something wrong! Check Console Tab for details.');
                console.log(xhr);
            }
        });

    });

});