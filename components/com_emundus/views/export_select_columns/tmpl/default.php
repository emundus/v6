<?php
defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.tooltip');
JHTML::_('behavior.modal');
$document = JFactory::getDocument();
$document->addStyleSheet("media/com_emundus/css/emundus_export_select_columns.css" );
$eMConfig       = JComponentHelper::getParams('com_emundus');
$current_user   = JFactory::getUser();
$view           = JRequest::getVar('v', null, 'GET', 'none',0);
$comments       = JRequest::getVar('comments', null, 'POST', 'none', 0);
$itemid         = JRequest::getVar('Itemid', null, 'GET', 'none',0);
$session        = JFactory::getSession();

$s_elements     = $session->get('s_elements');
$comments       = $session->get('comments');

if (!empty($s_elements)) {
    foreach ($s_elements as $s) {
        $t = explode('.',$s);
        $table_name[] = $t[0];
        $element_name[] = $t[1];
    }
}

?>

    <?php
    if (is_array($this->elements) && count($this->elements) > 0) {
        // If the form is set then this means that we are getting the Admission form details or some other extra form
        // We are going to use the form value as a way to make unique IDs so the JS works correctly :)
        if (isset($this->form) && !empty($this->form)) {

            echo '<div id="emundus_elements_'.$this->form.'" class="otherForm">';
            $tbl_tmp='';
            $grp_tmp='';

            foreach ($this->elements as $t) {
                if ($tbl_tmp == '') {
                    echo '<div class="panel panel-primary excel otherForm" id="emundus_table_'.$this->form.'_'.$t->table_id.'">
                            <div class="panel-heading"><div><input type="checkbox" ';

                    echo ' id="emundus_checkall_tbl_'.$this->form.'_'.$t->table_id.'" class="emunduspage otherForm" data-check=".emundusgroup_'.$this->form.'_'.$t->table_id.'" onClick="javascript:check_all(\'emundus_checkall_tbl_'.$this->form.'_'.$t->table_id.'\')" /><label for="emundus_checkall_tbl_'.$this->form.'_'.$t->table_id.'">'.$t->table_label.'</label></div></div><div class="panel-body">
                        <div class="panel panel-info excel" id="emundus_grp_'.$t->group_id.'">
                            <div class="panel-heading"><div><input type="checkbox" ';

                    echo ' id="emundus_checkall_grp_'.$this->form.'_'.$t->group_id.'" class="emundusgroup_'.$this->form.'_'.$t->table_id.' otherForm" data-check=".emundusitem_'.$this->form.'_'.$t->group_id.'" onClick="javascript:check_all(\'emundus_checkall_grp_'.$this->form.'_'.$t->group_id.'\')" /><label for="emundus_checkall_grp_'.$this->form.'_'.$this->form.'_'.$t->group_id.'">'.$t->group_label.'</label></div></div><div class="panel-body">';
                } elseif ($t->table_id != $tbl_tmp && $tbl_tmp != '') {
                        echo '</div></div></div></div>
                            <div class="panel panel-primary excel" id="emundus_table_'.$this->form.'_'.$t->table_id.'">
                                <div class="panel-heading"><div><input type="checkbox" ';

                        echo ' id="emundus_checkall_tbl_'.$this->form.'_'.$t->table_id.'" class="emunduspage otherForm" data-check=".emundusgroup_'.$this->form.'_'.$t->table_id.'" onClick="javascript:check_all(\'emundus_checkall_tbl_'.$this->form.'_'.$t->table_id.'\')" /><label for="emundus_checkall_tbl_'.$this->form.'_'.$t->table_id.'">'.$t->table_label.'</label></div></div><div class="panel-body">
                            <div class="panel panel-info excel otherForm" id="emundus_grp_'.$this->form.'_'.$t->group_id.'">
                                <div class="panel-heading"><div><input type="checkbox" ';

                        echo ' id="emundus_checkall_grp_'.$this->form.'_'.$t->group_id.'" class="emundusgroup_'.$this->form.'_'.$t->table_id.' otherForm" data-check=".emundusitem_'.$this->form.'_'.$t->group_id.'" onClick="javascript:check_all(\'emundus_checkall_grp_'.$this->form.'_'.$t->group_id.'\')" /><label for="emundus_checkall_grp_'.$this->form.'_'.$t->group_id.'">'.$t->group_label.'</label></div></div><div class="panel-body">';
                } else {
                    if ($t->group_id != $grp_tmp && $grp_tmp != '') {
                            echo '</div></div><div class="panel panel-info excel otherForm" id="emundus_grp_'.$t->group_id.'">
                                    <div class="panel-heading"><div><input type="checkbox" ';

                            echo ' id="emundus_checkall_grp_'.$this->form.'_'.$t->group_id.'" class="emundusgroup_'.$this->form.'_'.$t->table_id.' otherForm" data-check=".emundusitem_'.$this->form.'_'.$t->group_id.'" onClick="javascript:check_all(\'emundus_checkall_grp_'.$this->form.'_'.$t->group_id.'\')"/><label for="emundus_checkall_grp_'.$this->form.'_'.$t->group_id.'">'.$t->group_label.'</div></div><div class="panel-body">';
                    }
                }
                echo ' <input name="ud[]" type="checkbox" id="emundus_elm_'.$t->id.'" class="emundusitem_'.$this->form.'_'.$t->group_id.' otherForm" onClick="javascript:check_all(\'emundus_elm_'.$t->id.'\')" ';
                if (!empty($s_elements) && in_array($t->table_name,$table_name) && in_array($t->element_name,$element_name)) {
	                echo "checked=checked";
                }
                echo ' value="'.$t->id.'"/><label class="label-element" for="emundus_elm_'.$t->id.'">'.preg_replace('#<[^>]+>#', ' ', JText::_($t->element_label)).'</label> ';

                $tbl_tmp=$t->table_id;
                $grp_tmp=$t->group_id;
            }
            echo '</div></div></div></div>';
            echo '</div>';

        } else {

            /*echo '<input type="checkbox" id="emundus_checkall" class="emundusall" data-check=".emunduspage" onClick="javascript:check_all(\'emundus_checkall\')" /> ';
            echo '<label for="emundus_checkall">'.JText::_('SELECT_ALL').'</label>';*/
            echo '<div id="emundus_elements">';
            $tbl_tmp='';
            $grp_tmp='';

            foreach ($this->elements as $t) {
                if ($tbl_tmp == '') {
                    echo '<div class="panel panel-primary excel" id="emundus_table_'.$t->table_id.'">
                            <div class="panel-heading"><div><input type="checkbox" ';
                    if ($t->created_by_alias == 'comment' && $comments == 1) {
	                    echo "checked=checked";
                    }
                    $label = explode("-", $t->table_label);
                    $label = !empty($label[1]) ? $label[1] : $label[0];
                    echo ' id="emundus_checkall_tbl_'.$t->table_id.'" class="emunduspage" data-check=".emundusgroup_'.$t->table_id.'" onClick="javascript:check_all(\'emundus_checkall_tbl_'.$t->table_id.'\')" /><label for="emundus_checkall_tbl_'.$t->table_id.'">'.$label.' <i>['.$t->label.']</i></label></div></div><div class="panel-body">
                        <div class="panel panel-info excel" id="emundus_grp_'.$t->group_id.'">
                            <div class="panel-heading"><div><input type="checkbox" ';

                    if ($t->created_by_alias == 'comment' && $comments == 1) {
	                    echo "checked=checked";
                    }
                    echo ' id="emundus_checkall_grp_'.$t->group_id.'" class="emundusgroup_'.$t->table_id.'" data-check=".emundusitem_'.$t->group_id.'" onClick="javascript:check_all(\'emundus_checkall_grp_'.$t->group_id.'\')" /><label for="emundus_checkall_grp_'.$t->group_id.'">'.$t->group_label.'</label></div></div><div class="panel-body">';
                } elseif ($t->table_id != $tbl_tmp && $tbl_tmp != '') {
                        echo '</div></div></div></div>
                            <div class="panel panel-primary excel" id="emundus_table_'.$t->table_id.'">
                                <div class="panel-heading"><div><input type="checkbox" ';
                        if ($t->created_by_alias == 'comment' && $comments == 1) {
	                        echo "checked=checked";
                        }
                    $label = explode("-", $t->table_label);
                    $label = !empty($label[1]) ? $label[1] : $label[0];
                        echo ' id="emundus_checkall_tbl_'.$t->table_id.'" class="emunduspage" data-check=".emundusgroup_'.$t->table_id.'" onClick="javascript:check_all(\'emundus_checkall_tbl_'.$t->table_id.'\')" /><label for="emundus_checkall_tbl_'.$t->table_id.'">'.$label.' <i>['.$t->label.']</i></label></div></div><div class="panel-body">
                            <div class="panel panel-info excel" id="emundus_grp_'.$t->group_id.'">
                                <div class="panel-heading"><div><input type="checkbox" ';

                        if ($t->created_by_alias == 'comment' && $comments == 1) {
	                        echo "checked=checked";
                        }
                        echo ' id="emundus_checkall_grp_'.$t->group_id.'" class="emundusgroup_'.$t->table_id.'" data-check=".emundusitem_'.$t->group_id.'" onClick="javascript:check_all(\'emundus_checkall_grp_'.$t->group_id.'\')" /><label for="emundus_checkall_grp_'.$t->group_id.'">'.$t->group_label.'</label></div></div><div class="panel-body">';
                } else {
                    if ($t->group_id != $grp_tmp && $grp_tmp != '') {
                            echo '</div></div><div class="panel panel-info excel" id="emundus_grp_'.$t->group_id.'">
                                    <div class="panel-heading"><div><input type="checkbox" ';

                            if ($t->created_by_alias == 'comment' && $comments == 1) {
	                            echo "checked=checked";
                            }
                            echo ' id="emundus_checkall_grp_'.$t->group_id.'" class="emundusgroup_'.$t->table_id.'" data-check=".emundusitem_'.$t->group_id.'" onClick="javascript:check_all(\'emundus_checkall_grp_'.$t->group_id.'\')"/><label for="emundus_checkall_grp_'.$t->group_id.'">'.$t->group_label.'</div></div><div class="panel-body">';
                    }
                }
                echo ' <input name="ud[]" type="checkbox" id="emundus_elm_'.$t->id.'" class="emundusitem_'.$t->group_id.'" onClick="javascript:check_all(\'emundus_elm_'.$t->id.'\')" ';
                if ((!empty($s_elements) && in_array($t->table_name,$table_name) && in_array($t->element_name,$element_name)) || ($t->created_by_alias == 'comment' && $comments == 1)) {
	                echo "checked=checked";
                }
                echo ' value="'.$t->id.'"/><label class="label-element" for="emundus_elm_'.$t->id.'">'.preg_replace('#<[^>]+>#', ' ', JText::_($t->element_label)).'</label> ';

                $tbl_tmp=$t->table_id;
                $grp_tmp=$t->group_id;
            }
            echo '</div></div></div></div>';
            echo '</div>';
        }

    } else echo JText::_('NO_FORM_DEFINED');
?>

<script>
    function check_all( id ) {
        var inputname = $('#'+id).data('check');

        if (inputname != null) { // Si on a cliqué sur Select All, Page ou groupe

            // We are generating the id name to search for, if the class "otherForm" is not present in that ID then we revert to the classic method
            var formname = inputname.split('_');
            if (formname[1])
                formname = '_'+formname[1];
            if (!$('#emundus_elements'+formname).hasClass('otherForm'))
                formname = '';

            $('#emundus_elements'+formname).find('input:checkbox' + inputname).each(function () {
                $(this).prop("checked", $('#' + id).is(':checked'));
                var datacheck = $(this).attr('data-check');

                if (datacheck != null) {
                    var classdatacheck = datacheck.split('_');
                    classdatacheck = classdatacheck[0];

                    if (classdatacheck == ".emundusgroup") { // Si on a coché Select All, alors il faut parcourir les groupes de chaque page
                        $('#emundus_elements'+formname).find('input:checkbox' + datacheck).each(function () { // Pour chaque groupe
                            $(this).prop("checked", $('#' + id).is(':checked'));
                            datacheck = $(this).attr('data-check');

                            $('#emundus_elements'+formname).find('input:checkbox' + datacheck).each(function () { // pour chaque item
                                var itemid = $(this).attr('id');
                                itemid = itemid.split('_');
                                itemid = itemid[2];
                                var checked = $('#' + id).is(':checked');
                                $(this).prop("checked", checked);

                                if (checked) {
                                    var text = $("label[for='emundus_elm_" + itemid + "']").text();
                                    var exists = $('#' + itemid + '-item').length;
                                    if(exists==0)
                                        $('#em-export').append('<li class="em-export-item" id="' + itemid + '-item"><button class="btn btn-danger btn-xs" id="' + itemid + '-itembtn"><span class="glyphicon glyphicon-trash"></span></button> <span class="em-excel_elts"><strong>' + text + '</strong></span></li>');
                                } else {
                                    $('#' + itemid + '-item').remove();
                                }

                            });
                        });

                    } else if (classdatacheck == ".emundusitem") {  // Dans le cas où on clique sur page, les groupes sont cochés et il faut donc ensuite parcourir les items de chaque groupe

                        $('#emundus_elements'+formname).find('input:checkbox' + datacheck).each(function () {
                            var itemid = $(this).attr('id');
                            itemid = itemid.split('_');
                            itemid = itemid[2];
                            var checked = $('#' + id).is(':checked');
                            $(this).prop("checked", checked);

                            if (checked) {
                                var text = $("label[for='emundus_elm_" + itemid + "']").text();
                                var exists = $('#' + itemid + '-item').length;
                                if(exists==0)
                                    $('#em-export').append('<li class="em-export-item" id="' + itemid + '-item"><button class="btn btn-danger btn-xs" id="' + itemid + '-itembtn"><span class="glyphicon glyphicon-trash"></span></button> <span class="em-excel_elts"><strong>' + text + '</strong></span></li>');
                    
                            } else {
                                $('#' + itemid + '-item').remove();
                            }

                        });
                    }

                } else { // Sinon c'est que l'on a coché directement un groupe (les item n'ayant pas de data-check)

                    var itemid = $(this).attr('id');
                    itemid = itemid.split('_');
                    itemid = itemid[2];
                    var checked = $('#' + id).is(':checked');
                    $(this).prop("checked", checked);

                    if (checked) {
                        var text = $("label[for='emundus_elm_" + itemid + "']").text();
                        var exists = $('#' + itemid + '-item').length;
                        if(exists==0)
                            $('#em-export').append('<li class="em-export-item" id="' + itemid + '-item"><button class="btn btn-danger btn-xs" id="' + itemid + '-itembtn"><span class="glyphicon glyphicon-trash"></span></button> <span class="em-excel_elts"><strong>' + text + '</strong></span></li>');
            
                    } else {
                        $('#' + itemid + '-item').remove();
                    }

                }
            });

        } else { // Sinon on a coché directement un item

            var itemid = id.split('_');
            itemid = itemid[2];
            var checked = $('#' + id).is(':checked');
            $('#' + id).prop("checked", checked);

            if (checked) {
                var text = $("label[for='emundus_elm_" + itemid + "']").text();
                var exists = $('#' + itemid + '-item').length;
                if(exists==0)
                    $('#em-export').append('<li class="em-export-item" id="' + itemid + '-item"><button class="btn btn-danger btn-xs" id="' + itemid + '-itembtn"><span class="glyphicon glyphicon-trash"></span></button> <span class="em-excel_elts"><strong>' + text + '</strong></span></li>');
            } else {
                $('#' + itemid + '-item').remove();
            }

        }
    };


    function copyid() {
        /* Get the text field */
        console.log(this);
        var copyText = document.getElementById("myInput");

        /* Select the text field */
        copyText.select();

        /* Copy the text inside the text field */
        document.execCommand("copy");

        /* Alert the copied text */
        alert("Copied the text: " + copyText.value);
    }

</script>