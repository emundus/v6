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
$document->addStyleSheet(JURI::base()."media/com_emundus/lib/bootstrap-232/css/bootstrap.min.css" );
unset($document->_styleSheets[$this->baseurl .'/media/com_emundus/lib/bootstrap-emundus/css/bootstrap.min.css']);
?>

<form action="" method="post" enctype="multipart/form-data" name="adminForm" id="job-form" class="form-validate">
    <div class="em-container-trombi">
        <div class="em-col-trombi">
            <label for="trombi_format"><?= JText::_('COM_EMUNDUS_TROMBI_FORMAT'); ?></label>
            <select name="trombi_format" id="trombi_format" class="trombi_format">
            <?php foreach ($this->htmlLetters as $htmlLetter) :?>
                <option value="<?= $htmlLetter['attachment_id']; ?>"><?= $htmlLetter['title']; ?></option>
            <?php endforeach; ?>
            </select>
            <label for="trombi_grid"><?= JText::_('COM_EMUNDUS_TROMBI_GRID');  ?></label>
            <div class="em-container-grid">
                <div>
                    <label for="trombi_grid_width"><?= JText::_('COM_EMUNDUS_TROMBI_COLUMN'); ?></label>
                    <select id="trombi_grid_width" name="trombi_grid_width" class="inputbox">
                        <?php for ($i = 1; $i <= 10; $i++) :?>
                            <option value="<?= $i; ?>"><?= $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div>
                    <label for="trombi_grid_height"><?= JText::_('COM_EMUNDUS_TROMBI_LINE'); ?></label>
                    <select id="trombi_grid_height" name="trombi_grid_height" class="inputbox">
                        <?php for ($i = 1; $i <= 10; $i++) :?>
                            <option value="<?= $i; ?>"><?= $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            <label for="trombi_margin"><?= JText::_('COM_EMUNDUS_TROMBI_MARGIN'); ?><span> (<?= JText::_('COM_EMUNDUS_TROMBI_IN_PIXELS'); ?>)</span></label>
            <input id="trombi_margin" name="trombi_margin" value="<?= $this->default_margin; ?>" class="trombi_margin" />

            <p class="trombi_margin_desc"><?= JText::_('COM_EMUNDUS_TROMBI_MARGIN_EXPLANATION1'); ?></p>
            <p class="trombi_margin_desc trombi_margin_btm"><?= JText::_('COM_EMUNDUS_TROMBI_MARGIN_EXPLANATION2'); ?></p>

            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="" id="trombi_check">
                <label class="form-check-label" for="trombi_check">
                    <?= JText::_('COM_EMUNDUS_TROMBI_CHECKBOX'); ?>
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="" id="trombi_border">
                <label class="form-check-label" for="trombi_border">
                    <?= JText::_('COM_EMUNDUS_TROMBI_BORDER'); ?>
                </label>
            </div>
            <input type="hidden" id="selected_format" name="selected_format" value="<?= $this->selected_format; ?>" />
            <input type="hidden" id="string_fnums" name="string_fnums" value="<?= $this->string_fnums; ?>" />
            <input type="hidden" id="string_generate" name="string_generate" value="0" />
            <input type="hidden" id="trombi_header" name="trombi_header" value="" />
            <input type="hidden" id="trombi_footer" name="trombi_footer" value="" />
        </div>
        <div class="em-col-trombi trombi-col-button">
            <button onclick="window.location.hash='preview';" type="button" id="trombi_preview" class="btn btn-info trombi_button"><?= JText::_('COM_EMUNDUS_TROMBI_PREVIEW'); ?>
                <span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>
            </button>
            <button type="button" id="trombi_generate" class="btn btn-primary trombi_button"><?= JText::_('COM_EMUNDUS_TROMBI_GENERATE'); ?>
                <span class="glyphicon glyphicon-file" aria-hidden="true"></span>
            </button>
            <div id="div-download">
                <a class="btn btn-link trombi_download" id="trombi_download" title="<?= JText::_('COM_EMUNDUS_TROMBI_DOWNLOAD'); ?>" href="" target="_blank">
                    <span class="glyphicon glyphicon-download-alt"></span>
                    <span><?= JText::_('COM_EMUNDUS_TROMBI_DOWNLOAD'); ?></span>
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <h3 class="title_wysiwyg"><?= JText::_('COM_EMUNDUS_TROMBI_TEMPLATE'); ?></h3>
            <?= $this->wysiwyg; ?>
        </div>
    </div>
    <div class="row">
        <label for="trombi_header_height"><?= JText::_('COM_EMUNDUS_TROMBI_HEIGHT_HEADER'); ?></label>
        <input id="trombi_header_height" name="trombi_header_height" value="" class="trombi_margin" />
    </div>
    <div class="row">
        <div class="col-md-12">
            <h3 class="title_wysiwyg"><?= JText::_('COM_EMUNDUS_TROMBI_HEADER'); ?></h3>
		    <?= $this->wysiwyg_header; ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <h3 class="title_wysiwyg"><?= JText::_('COM_EMUNDUS_TROMBI_FOOTER'); ?></h3>
		    <?= $this->wysiwyg_footer; ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12"><a href="<?= $this->form_elements_id_list; ?>" target="_blank"><?= JText::_('COM_EMUNDUS_TROMBI_ID_LIST'); ?></a></div>
        <div id="div-preview"></div>
    </div>

</form>


<script>

    $('#trombi_check').click(function() {
        $('#trombi_check').val(1);
    });


    $('#trombi_check').change(function() {
        if( $('#trombi_check').is(':checked') ){
            $('#trombi_check').val(1);
        } else {
            $('#trombi_check').val(0);
        }
    });


    $('#trombi_border').change(function() {
        if( $('#trombi_border').is(':checked') ){
            $('#trombi_border').val(1);
        } else {
            $('#trombi_border').val(0);
        }
    });


    $('.trombi_format').change(function() {
        var selected_format = $(this).val();

        var default_tmpl = '';

        var templ = <?= json_encode($this->templ); ?>;

        var actual_tmpl = $('#trombi_tmpl').val();

        $('#selected_format').val(selected_format);
        default_tmpl = templ[selected_format].body;
        var header = templ[selected_format].header;
        var footer = templ[selected_format].footer;

        $('#trombi_tmpl').val(default_tmpl);
        $('#trombi_header').val(header);
        $('#trombi_footer').val(footer);

        var heightHeader = $('#trombi_header').height();

        tinyMCE.execCommand("mceSetContent", false, default_tmpl);
        tinyMCE.execCommand("mceRepaint");
    });


    $('#trombi_preview').click(function (e) { 
        e.preventDefault();
        //tinyMCE.execCommand("mceRepaint");
        // use to refresh content... double ToggleEditor
        tinyMCE.execCommand('mceToggleEditor', false, 'trombi_tmpl');
        tinyMCE.execCommand('mceToggleEditor', false, 'trombi_tmpl');

        var string_fnums = JSON.stringify(<?= $this->string_fnums; ?>);//$('#string_fnums').val();
        var selected_grid_width = $('#trombi_grid_width').val();
        var selected_grid_height = $('#trombi_grid_height').val();
        var selected_margin = $('#trombi_margin').val();
        var selected_tmpl = $('#trombi_tmpl').val();
        var header_height = $('#trombi_header_height').val();

        var format = $('#selected_format').val();

        $('#string_generate').val(0);
        var string_generate = $('#string_generate').val();
        var selected_border = $('#trombi_border').val();

        $('#trombi_generate').prop('disabled', false);

        var data = $.ajax({
            type: 'POST',
            url: 'index.php?option=com_emundus&controller=trombinoscope&task=generate_preview',
            async: false,
            dataType: "json",
            data: {
                string_fnums: string_fnums,
                gridL: selected_grid_width,
                gridH: selected_grid_height,
                margin: selected_margin,
                template: selected_tmpl,
                format: format,
                generate: string_generate,
                border: selected_border,
                headerHeight: header_height
            },
            success: function (data) {
                var html_content = data.html_content;

                $('#div-preview').html(html_content);
                $('#data_for_pdf').html(html_content);
                $('#div-preview').slideDown("slow");
                $('#div-download').hide();
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                alert('ERROR');
            }
        });
    });

    $('#trombi_generate').click(function (e) {
        e.preventDefault();
        tinyMCE.execCommand('mceToggleEditor', false, 'trombi_head');
        tinyMCE.execCommand('mceToggleEditor', false, 'trombi_foot');

        var string_fnums = JSON.stringify(<?= $this->string_fnums; ?>);
        var selected_grid_width = $('#trombi_grid_width').val();
        var selected_grid_height = $('#trombi_grid_height').val();
        var selected_margin = $('#trombi_margin').val();
        var selected_tmpl = $('#trombi_tmpl').val();
        var header = $('#trombi_head').val();
        var header_height = $('#trombi_header_height').val();
        var footer = $('#trombi_foot').val();

        var format = $('#selected_format').val();

        var selected_check = $('#trombi_check').val();
        var selected_border = $('#trombi_border').val();

        $('#string_generate').val(1); //For display the header and footer only for the pdf
        var string_generate = $('#string_generate').val();

        $(this).prop('disabled', true);

        var data = $.ajax({
            type: 'POST',
            url: 'index.php?option=com_emundus&controller=trombinoscope&task=generate_pdf',
            async: false,
            dataType: "json",
            data : {
                string_fnums: string_fnums,
                gridL: selected_grid_width,
                gridH: selected_grid_height,
                margin: selected_margin,
                template: selected_tmpl,
                header: header,
                footer: footer,
                format: format,
                generate: string_generate,
                checkHeader: selected_check,
                border: selected_border,
                headerHeight : header_height
            },
            success: function (data) {
                $pdf_url = data.pdf_url;
                $('#trombi_download').attr("href", $pdf_url);
                $('#div-download').css({
                    'display':'block',
                    'textAlign':'left'
                });
                $('#div-preview').hide();
                $(this).prop('disabled', false);
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                //alert('ERROR');
            }
        });
    });
</script>
