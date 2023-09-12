<?php
defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.tooltip');
JHTML::_('behavior.modal');
$document = JFactory::getDocument();
$document->addStyleSheet("media/com_emundus/css/emundus_export_select_columns.css" );
$eMConfig       = JComponentHelper::getParams('com_emundus');
$current_user   = JFactory::getUser();
$view           = JFactory::getApplication()->input->get('v', null, 'GET', 'none',0);
$comments       = JFactory::getApplication()->input->get('comments', null, 'POST', 'none', 0);
$itemid         = JFactory::getApplication()->input->get('Itemid', null, 'GET', 'none',0);
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
                echo '<div class="em-p-8 em-border-radius-8 em-white-bg em-mb-8" id="emundus_table_'.$this->form.'_'.$t->table_id.'">
                            <div class="panel-heading"><div class="em-flex-row"><input type="checkbox" ';

                echo ' id="emundus_checkall_tbl_'.$this->form.'_'.$t->table_id.'" class="emunduspage otherForm" data-check=".emundusgroup_'.$this->form.'_'.$t->table_id.'"/><label style="margin-bottom: 0" for="emundus_checkall_tbl_'.$this->form.'_'.$t->table_id.'">'.$t->table_label.'</label></div></div><div class="panel-body">
                        <div class="em-p-8 em-border-radius-8 em-white-bg em-mb-8" id="emundus_grp_'.$t->group_id.'">
                            <div class="panel-heading"><div class="em-flex-row"><input type="checkbox" ';

                echo ' id="emundus_checkall_grp_'.$this->form.'_'.$t->group_id.'" class="emundusgroup_'.$this->form.'_'.$t->table_id.' otherForm" data-check=".emundusitem_'.$this->form.'_'.$t->group_id.'"/><label for="emundus_checkall_grp_'.$this->form.'_'.$this->form.'_'.$t->group_id.'">'.$t->group_label.'</label></div></div><div class="panel-body">';
            } elseif ($t->table_id != $tbl_tmp && $tbl_tmp != '') {
                echo '</div></div></div></div>
                            <div class="em-p-8 em-border-radius-8 em-white-bg em-mb-8" id="emundus_table_'.$this->form.'_'.$t->table_id.'">
                                <div class="panel-heading"><div class="em-flex-row"><input type="checkbox" ';

                echo ' id="emundus_checkall_tbl_'.$this->form.'_'.$t->table_id.'" class="emunduspage otherForm" data-check=".emundusgroup_'.$this->form.'_'.$t->table_id.'" /><label style="margin-bottom: 0" for="emundus_checkall_tbl_'.$this->form.'_'.$t->table_id.'">'.$t->table_label.'</label></div></div><div class="panel-body">
                            <div class="em-p-8 em-border-radius-8 em-white-bg em-mb-8" id="emundus_grp_'.$this->form.'_'.$t->group_id.'">
                                <div class="panel-heading"><div class="em-flex-row"><input type="checkbox" ';

                echo ' id="emundus_checkall_grp_'.$this->form.'_'.$t->group_id.'" class="emundusgroup_'.$this->form.'_'.$t->table_id.' otherForm" data-check=".emundusitem_'.$this->form.'_'.$t->group_id.'"/><label style="margin-bottom: 0" for="emundus_checkall_grp_'.$this->form.'_'.$t->group_id.'">'.$t->group_label.'</label></div></div><div class="panel-body">';
            } else {
                if ($t->group_id != $grp_tmp && $grp_tmp != '') {
                    echo '</div></div><div class="em-p-8 em-border-radius-8 em-white-bg em-mb-8" id="emundus_grp_'.$t->group_id.'">
                                    <div class="panel-heading"><div class="em-flex-row"><input type="checkbox" ';

                    echo ' id="emundus_checkall_grp_'.$this->form.'_'.$t->group_id.'" class="emundusgroup_'.$this->form.'_'.$t->table_id.' otherForm" data-check=".emundusitem_'.$this->form.'_'.$t->group_id.'"/><label style="margin-bottom: 0" for="emundus_checkall_grp_'.$this->form.'_'.$t->group_id.'">'.$t->group_label.'</div></div><div class="panel-body">';
                }
            }

            echo '<div class="em-flex-row"><input name="ud[]" type="checkbox" id="emundus_elm_'.$t->id.'" class="emundusitem_'.$this->form.'_'.$t->group_id.' otherForm"';
            if (!empty($s_elements) && in_array($t->table_name,$table_name) && in_array($t->element_name,$element_name)) {
                echo "checked=checked";
            }
            echo ' value="'.$t->id.'"/><label style="margin-bottom: 0" for="emundus_elm_'.$t->id.'">'.preg_replace('#<[^>]+>#', ' ', JText::_($t->element_label)).'</label></div>';

            $tbl_tmp=$t->table_id;
            $grp_tmp=$t->group_id;
        }
        echo '</div></div></div></div>';
        echo '</div>';

    } else {
        echo '<div class="em-flex-row em-mb-16"><input type="checkbox" id="emundus_checkall' . $this->elements[0]->profil_id . '" class="emundusall" data-check=".emunduspage"/>';
        echo '<label for="emundus_checkall' . $this->elements[0]->profil_id . '" style="margin-bottom: 0">'.JText::_('COM_EMUNDUS_SELECT_ALL').'</label></div>';
        echo '<div id="emundus_elements">';
        $tbl_tmp='';
        $grp_tmp='';

        foreach ($this->elements as $t) {
            if ($tbl_tmp == '') {
                echo '<div class="em-p-8 em-border-radius-8 em-white-bg em-mb-8" id="emundus_table_'.$t->table_id.'">
                            <div class="panel-heading"><div class="em-flex-row"><input type="checkbox" ';
                if ($t->created_by_alias == 'comment' && $comments == 1) {
                    echo "checked=checked";
                }
                $label = explode("-", $t->table_label);
                $label = !empty($label[1]) ? $label[1] : $label[0];

                echo ' id="emundus_checkall_tbl_'.$t->table_id.'" class="emunduspage" data-check=".emundusgroup_'.$t->table_id.'"/><label style="margin-bottom: 0" for="emundus_checkall_tbl_'.$t->table_id.'">'.$label.' <i>['.$t->label.']</i></label></div></div><div class="panel-body">
                        <div class="em-p-8 em-border-radius-8 em-white-bg em-mb-8" id="emundus_grp_'.$t->group_id.'">
                            <div class="panel-heading"><div class="em-flex-row"><input type="checkbox" ';

                if ($t->created_by_alias == 'comment' && $comments == 1) {
                    echo "checked=checked";
                }

                echo ' id="emundus_checkall_grp_'.$t->group_id.'" class="emundusgroup_'.$t->table_id.'" data-check=".emundusitem_'.$t->group_id.'"/><label style="margin-bottom: 0" for="emundus_checkall_grp_'.$t->group_id.'">'.$t->group_label.'</label></div></div><div class="panel-body">';
            } elseif ($t->table_id != $tbl_tmp && $tbl_tmp != '') {
                echo '</div></div></div></div>
                            <div class="em-p-8 em-border-radius-8 em-white-bg em-mb-8" id="emundus_table_'.$t->table_id.'">
                                <div class="panel-heading"><div class="em-flex-row"><input type="checkbox" ';
                if ($t->created_by_alias == 'comment' && $comments == 1) {
                    echo "checked=checked";
                }
                $label = explode("-", $t->table_label);
                $label = !empty($label[1]) ? $label[1] : $label[0];

                echo ' id="emundus_checkall_tbl_'.$t->table_id.'" class="emunduspage" data-check=".emundusgroup_'.$t->table_id.'"/><label style="margin-bottom: 0" for="emundus_checkall_tbl_'.$t->table_id.'">'.$label.' <i>['.$t->label.']</i></label></div></div><div class="panel-body">
                            <div class="em-p-8 em-border-radius-8 em-white-bg em-mb-8" id="emundus_grp_'.$t->group_id.'">
                                <div class="panel-heading"><div class="em-flex-row"><input type="checkbox" ';

                if ($t->created_by_alias == 'comment' && $comments == 1) {
                    echo "checked=checked";
                }

                echo ' id="emundus_checkall_grp_'.$t->group_id.'" class="emundusgroup_'.$t->table_id.'" data-check=".emundusitem_'.$t->group_id.'"/><label style="margin-bottom: 0" for="emundus_checkall_grp_'.$t->group_id.'">'.$t->group_label.'</label></div></div><div class="panel-body">';
            } else {
                if ($t->group_id != $grp_tmp && $grp_tmp != '') {
                    echo '</div></div><div class="em-p-8 em-border-radius-8 em-white-bg em-mb-8" id="emundus_grp_'.$t->group_id.'">
                                    <div class="panel-heading"><div class="em-flex-row"><input type="checkbox" ';

                    if ($t->created_by_alias == 'comment' && $comments == 1) {
                        echo "checked=checked";
                    }

                    echo ' id="emundus_checkall_grp_'.$t->group_id.'" class="emundusgroup_'.$t->table_id.'" data-check=".emundusitem_'.$t->group_id.'"/><label style="margin-bottom: 0" for="emundus_checkall_grp_'.$t->group_id.'">'.$t->group_label.'</div></div><div class="panel-body">';
                }
            }

            echo '<div class="em-flex-row"><input name="ud[]" type="checkbox" id="emundus_elm_'.$t->id.'" class="emundusitem_'.$t->group_id.'" ';
            if ((!empty($s_elements) && in_array($t->table_name,$table_name) && in_array($t->element_name,$element_name)) || ($t->created_by_alias == 'comment' && $comments == 1)) {
                echo "checked=checked";
            }
            echo ' value="'.$t->id.'"/><label style="margin-bottom: 0" for="emundus_elm_'.$t->id.'">'.preg_replace('#<[^>]+>#', ' ', JText::_($t->element_label)).'</label></div>';

            $tbl_tmp=$t->table_id;
            $grp_tmp=$t->group_id;
        }
        echo '</div></div></div></div>';
        echo '</div>';
    }

} else echo JText::_('COM_EMUNDUS_FORM_NO_FORM_DEFINED');                /// corriger ici en changeant par 'AUCUN ELEMENT EST DEFINI'
?>

<script>
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
