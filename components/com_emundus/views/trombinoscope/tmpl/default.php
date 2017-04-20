<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 28/03/2017
 * Time: 01:15
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

$document = JFactory::getDocument();
$document->addStyleSheet(JURI::base()."media/com_emundus/css/emundus_trombinoscope.css" );
$document->addStyleSheet(JURI::base()."media/com_emundus/lib/bootstrap-336/css/bootstrap.min.css" );
?>

<form action="" method="post" enctype="multipart/form-data" name="adminForm" id="job-form" class="form-validate">
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12 side-panel">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo JText::_('COM_EMUNDUS_TROMBI_SETTINGS')?></h3>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-3"><?php echo JText::_('COM_EMUNDUS_TROMBI_FORMAT')?></div>
                        <div class="col-md-9">
                            <label>
                                <input type="radio" class="trombi_format" name="trombi_format" value="trombi" <?php echo $this->trombi_checked;?>>
                                <?php echo JText::_('COM_EMUNDUS_TROMBI_WHOSWHO');?>
                            </label>
                            <br />
                            <label>
                                <input type="radio" class="trombi_format" name="trombi_format" value="badge" <?php echo $this->badge_checked;?>>
                                <?php echo JText::_('COM_EMUNDUS_TROMBI_BADGE');?>
                            </label>
                            <input type="hidden" id="selected_format" name="selected_format" value="<?php echo $this->selected_format;?>" />
                            <input type="hidden" id="string_fnums" name="string_fnums" value="<?php echo $this->string_fnums;?>" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3"><?php echo JText::_('COM_EMUNDUS_TROMBI_GRID')?></div>
                        <div class="col-md-9">
                            <select id="trombi_grid" name="trombi_grid" class="inputbox">
                                <option value="3x3">3 x 3</option>
                                <option value="2x6">2 x 6 (<?php echo JText::_('COM_EMUNDUS_TROMBI_BADGE');?>)</option>
                                <option value="5x3">5 x 3</option>
                                <option value="5x5">5 x 5</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3"><?php echo JText::_('COM_EMUNDUS_TROMBI_MARGIN')?></div>
                        <div class="col-md-9">
                            <input id="trombi_margin" name="trombi_margin" value="<?php echo $this->default_margin;?>" />&nbsp;(<?php echo JText::_('COM_EMUNDUS_TROMBI_IN_PIXELS')?>)<br />
                            <?php echo JText::_('COM_EMUNDUS_TROMBI_MARGIN_EXPLANATION1')?><br />
                            <?php echo JText::_('COM_EMUNDUS_TROMBI_MARGIN_EXPLANATION2')?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3"><?php echo JText::_('COM_EMUNDUS_TROMBI_TEMPLATE')?></div>
                        <div class="col-md-9"><?php echo $this->wysiwyg; ?></div>
                    </div>
                    <div class="row">
                        <div class="col-md-12"><a href="<?php echo $this->form_elements_id_list; ?>" target="_blank"><?php echo JText::_('COM_EMUNDUS_TROMBI_ID_LIST')?></a></div>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-md-3">
                            <button type="button" id="trombi_preview" class="btn btn-info"><?php echo JText::_('COM_EMUNDUS_TROMBI_PREVIEW')?>
                                <span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>
                            </button>
                            <button type="button" id="trombi_generate" class="btn btn-primary"><?php echo JText::_('COM_EMUNDUS_TROMBI_GENERATE')?>
                                <span class="glyphicon glyphicon-file" aria-hidden="true"></span>
                            </button>
                        </div>
                        <div class="col-md-9">
                            <button type="button" id="trombi_cancel" class="btn btn-danger" data-dismiss="modal"><?php echo JText::_('COM_EMUNDUS_TROMBI_CANCEL')?></button>
                        </div>
                    </div>
                </div>
                <div class="panel-footer preview" id="div-preview">
                    <div class="row print">
                        
                    </div>
                    <div class="row" id="preview"></div>
                </div>
                <div class="panel-footer download" id="div-download">
                    <div class="row print">
                        <a class="btn .btn-link" id="trombi_download" title="<?php echo JText::_('COM_EMUNDUS_TROMBI_DOWNLOAD')?>" href="" target="_blank">
                            <span class="glyphicon glyphicon-download-alt"></span>
                            <span><?php echo JText::_('COM_EMUNDUS_TROMBI_DOWNLOAD')?></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</form>


<script>
    $('.trombi_format').change(function() {
        var selected_format = $(this).val();
        var default_tmpl = '';
        var trombi_tmpl = '<?php echo str_replace("\r\n", '', $this->trombi_tmpl);?>';
        var badge_tmpl = '<?php echo str_replace("\r\n", '', $this->badge_tmpl);?>';
        var actual_tmpl = $('#trombi_tmpl').val();

        $('#selected_format').val(selected_format);
        if (selected_format == 'trombi') {default_tmpl = trombi_tmpl;}
        if (selected_format == 'badge') {default_tmpl = badge_tmpl;}

        $('#trombi_tmpl').val(default_tmpl);

        tinyMCE.execCommand("mceSetContent", false, default_tmpl);
        tinyMCE.execCommand("mceRepaint");
    });

    $('#trombi_preview').click(function (e) { 
        e.preventDefault();
        //tinyMCE.execCommand("mceRepaint");
        // use to refresh content... double ToggleEditor
        tinyMCE.execCommand('mceToggleEditor', false, 'trombi_tmpl');
        tinyMCE.execCommand('mceToggleEditor', false, 'trombi_tmpl');

        var string_fnums = JSON.stringify(<?php echo $this->string_fnums;?>);//$('#string_fnums').val();
        var selected_grid = $('#trombi_grid').val();
        var selected_margin = $('#trombi_margin').val();
        var selected_tmpl = $('#trombi_tmpl').val();
        var format = $('#selected_format').val();

        $('#trombi_generate').prop('disabled', false);

        var data = $.ajax({
            type: 'POST',
            url: 'index.php?option=com_emundus&controller=trombinoscope&task=generate_preview',
            async: false,
            dataType: "json",
            data: 'string_fnums='+string_fnums+'&grid='+selected_grid+'&margin='+selected_margin+'&template='+selected_tmpl+'&format='+format,
            success: function (data) {
                //console.log('data:');
                //console.log(data);
                var html_content = data.html_content;
                $('#preview').html(html_content);
                $('#data_for_pdf').html(html_content);
                $('#div-preview').slideDown("slow");
                $('#div-download').hide();
            },
            error: function (xhr, type, exception) {
                //$('#preview').html(xhr.responseText);
                console.log(xhr.responseText);
                alert('ERROR');
            }
        });
    });
    $('#trombi_generate').click(function (e) {
        e.preventDefault();
        var string_fnums = JSON.stringify(<?php echo $this->string_fnums;?>);//$('#string_fnums').val();
        var selected_grid = $('#trombi_grid').val();
        var selected_margin = $('#trombi_margin').val();
        var selected_tmpl = $('#trombi_tmpl').val();
        var format = $('#selected_format').val();
        $(this).prop('disabled', true);

        var data = $.ajax({
            type: 'POST',
            url: 'index.php?option=com_emundus&controller=trombinoscope&task=generate_pdf',
            async: false,
            dataType: "json",
            data: 'string_fnums='+string_fnums+'&grid='+selected_grid+'&margin='+selected_margin+'&template='+selected_tmpl+'&format='+format,
            success: function (data) {
                console.log('data:');
                console.log(data);
                $pdf_url = data.pdf_url;
                $('#trombi_download').attr("href", $pdf_url);
                $('#div-download').show();
                $('#div-preview').hide();
                $(this).prop('disabled', false);
            },
            error: function (xhr, type, exception) {
                //$('#div-preview').html(xhr.responseText);
                console.log(xhr);
                alert('ERROR');
            }
        });
    });
</script>