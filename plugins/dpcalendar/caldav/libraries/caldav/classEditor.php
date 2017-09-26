<?php
/**
* Class for editing a record using a templated form.
*
* @package   awl
* @subpackage   classEditor
* @author    Andrew McMillan <andrew@mcmillan.net.nz>
* @copyright Catalyst IT Ltd, Morphoss Ltd <http://www.morphoss.com/>
* @license   http://gnu.org/copyleft/gpl.html GNU GPL v2
*/

require_once("DataUpdate.php");
require_once("DataEntry.php");

/**
* A class for the fields in the editor
* @package   awl
*/
class EditorField
{
  var $Field;
  var $Sql;
  var $Value;
  var $Attributes;
  var $LookupSql;
  var $OptionList;

  /**
   * Creates an EditorField for use in the Editor, possibly initialising the SQL for calculating it's
   * value, and lookup_sql for use in drop-down lists.
   *
   * @param unknown $field
   * @param string $sql
   * @param string $lookup_sql
   */
  function __construct( $field, $sql="", $lookup_sql="" ) {
    global $session;
    $this->Field      = $field;
    $this->Sql        = $sql;
    $this->LookupSql  = $lookup_sql;
    $this->Attributes = array();
  }

  function Set($value) {
    $this->Value = $value;
  }

  /**
   * Set the SQL used for this field, if it is more than just a field name.
   * @param unknown $sql
   */
  function SetSql( $sql ) {
    $this->Sql  = $sql;
  }

  /**
   * Set the lookup SQL to use to populate a SELECT for this field.
   * @param string $lookup_sql
   */
  function SetLookup( $lookup_sql ) {
    $this->LookupSql  = $lookup_sql;
  }

  /**
   * Set the SELECT values explicitly, if they are not available in SQL.
   *
   * For example:
   *
   *   SetOptionList(array('M' => 'Male', 'F' => 'Female', 'O' => 'Other'), 'F', array('maxwidth' => 6, 'translate' => true));
   *
   * This would present Male/Female/Other drop-down, when in another language the values
   * would be translated (if available), e.g. in German as Männlich/Weiblich/Andere, except
   * that in this case Männli/Weibli/Andere, since the values would be truncated to maxwidth.
   *
   * @param array $options An array of key => value pairs
   * @param string $current The currently selected key
   * @param string $parameters An array of parameters (maxwidth & translate are the only valid parameters)
   */
  function SetOptionList( $options, $current = null, $parameters = null) {
    if ( gettype($options) == 'array' ) {
      $this->OptionList = '';

      if ( is_array($parameters) ) {
        if ( isset($parameters['maxwidth']) ) $maxwidth = max(4,intval($parameters['maxwidth']));
        if ( isset($parameters['translate']) ) $translate = true;
      }

      foreach( $options AS $k => $v ) {
        if (is_array($current)) {
          $selected = ( ( in_array($k,$current,true) || in_array($v,$current,true)) ? ' selected="selected"' : '' );
        }
        else {
          $selected = ( ( "$k" == "$current" || "$v" == "$current" ) ? ' selected="selected"' : '' );
        }
        if ( isset($translate) ) $v = translate( $v );
        if ( isset($maxwidth) ) $v = substr( $v, 0, $maxwidth);
        $this->OptionList .= "<option value=\"".htmlspecialchars($k)."\"$selected>".htmlspecialchars($v)."</option>";
      }
    }
    else {
      $this->OptionList = $options;
    }
  }

  function GetTarget() {
    if ( $this->Sql == "" ) return $this->Field;
    return "$this->Sql AS $this->Field";
  }

  /**
   * Add some kind of attribute to this field, such as a 'class' => 'fancyinputthingy'
   *
   * @param string $k The attribute name
   * @param string $v The attribute value
   */
  function AddAttribute( $k, $v ) {
    $this->Attributes[$k] = $v;
  }

  /**
   * Render a LABEL around something.  In particular it is useful to render a label around checkbox fields to include their labels and make them clickable.
   *
   * The label value itself must be in the '_label' attribute, and the field must also have an 'id' attribute.
   *
   * @param string $wrapme The rendered field to be wrapped
   * @return string
   */
  function RenderLabel( $wrapme ) {
    if ( !isset($this->Attributes['_label']) || !isset($this->Attributes['id'])) return $wrapme;
    $class = (isset($this->Attributes['class']) ? $this->Attributes['class'] : 'entry');
    $title = (isset($this->Attributes['title']) ? ' title="'.str_replace('"', '&#39;', $this->Attributes['title']) . '"' : '');
    return( sprintf( '<label for="%s" class="%s"%s>%s %s</label>',
             $this->Attributes['id'], $class, $title, $wrapme, $this->Attributes['_label']) );
  }

  /**
   * Render the array of attributes for inclusion in the input tag.
   * @return string
   */
  function RenderAttributes() {
    $attributes = "";
    if ( count($this->Attributes) == 0 ) return $attributes;
    foreach( $this->Attributes AS $k => $v ) {
      if ( $k == '_label' ) continue;
      $attributes .= " $k=\"" . str_replace('"', '&#39;', $v) . '"';
    }
    return $attributes;
  }



}



/**
* The class for the Editor form in full
* @package awl
*/
class Editor
{
  var $Title;
  var $Action;
  var $Fields;
  var $OrderedFields;
  var $BaseTable;
  var $Joins;
  var $Where;
  var $NewWhere;
  var $Order;
  var $Limit;
  var $Query;
  var $Template;
  var $RecordAvailable;
  var $Record;
  var $SubmitName;
  var $Id;

  /**
   * Constructs an editor widget, with a title and fields.
   *
   * The second parameter maybe passed as a string, to be interpreted as the name of a table, from
   * which all fields will be included, or as an array of specific fields, in which case you should
   * make sure to call SetBaseTable('tablename') so the editor knows where to find those fields!
   *
   * @param string $title
   * @param array or string $fields See above
   */
  function __construct( $title = "", $fields = null ) {
    global $c, $session, $form_id_increment;
    $this->Title = $title;
    $this->Order = "";
    $this->Limit = "";
    $this->Template = "";
    $this->RecordAvailable = false;
    $this->SubmitName = 'submit';
    $form_id_increment = (isset($form_id_increment)? ++$form_id_increment : 1);
    $this->Id = 'editor_'.$form_id_increment;

    if ( isset($fields) ) {
      if ( is_array($fields) ) {
        foreach( $fields AS $k => $v ) {
          $this->AddField($v);
        }
      }
      else if ( is_string($fields) ) {
        // We've been given a table name, so get all fields for it.
        $this->BaseTable = $fields;
        $field_list = get_fields($fields);
        foreach( $field_list AS $k => $v ) {
          $this->AddField($k);
        }
      }
    }
    @dbg_error_log( 'editor', 'DBG: New editor called %s', $title);
  }

  /**
   * Creates a new field in the Editor, possibly initialising the SQL for calculating it's
   * value, and lookup_sql for use in drop-down lists.
   *
   * @param string $field The name for the field.
   * @param string $sql The SQL for the target list. Think: "$sql AS $field"
   * @param string $lookup_sql The SQL for looking up a list of possible stored values and displayed values.
   */
  function &AddField( $field, $sql="", $lookup_sql="" ) {
    $this->Fields[$field] = new EditorField( $field, $sql, $lookup_sql );
    $this->OrderedFields[] = $field;
    return $this->Fields[$field];
  }

  /**
   * Set the SQL for this field for the target list.  Think: "$sql AS $field"
   * @param string $field
   * @param string $sql
   */
  function SetSql( $field, $sql ) {
    $this->Fields[$field]->SetSql( $sql );
  }

  /**
   * Set the SQL for looking up a list of possible stored values and displayed values.
   * @param string $field
   * @param string $lookup_sql
   */
  function SetLookup( $field, $lookup_sql ) {
    if (is_object($this->Fields[$field])) {
      $this->Fields[$field]->SetLookup( $lookup_sql );
    }
  }

  /**
   * Gets the value of a field in the record currently assigned to this editor.
   * @param string $value_field_name
   */
  function Value( $value_field_name ) {
    if ( !isset($this->Record->{$value_field_name}) ) return null;
    return $this->Record->{$value_field_name};
  }

  /**
   * Assigns the value of a field in the record currently associated with this editor.
   * @param string $value_field_name
   * @param string $new_value
   */
  function Assign( $value_field_name, $new_value ) {
    if ( !isset($this->Record) ) $this->Record = (object) array();
    $this->Record->{$value_field_name} = $new_value;
  }

  /**
   * Sets or returns the form ID used for differentiating this form from others in the page.
   * @param string $id
   */
  function Id( $id = null ) {
    if ( isset($id) ) $this->Id = preg_replace( '#[^a-z0-9_+-]#', '', $id);
    return $this->Id;
  }

  /**
   * Set the explicit options & parameters for a list of stored/displayed values.  See the
   * description under EditorField::SetOptionList() for full details.
   *
   * @param string $field
   * @param array $options A key => value array of valid store => display values.
   * @param string $current The key of the current row
   * @param string $parameters Set maxwidth & whether displayed values are translated.
   */
  function SetOptionList( $field, $options, $current = null, $parameters = null) {
    $this->Fields[$field]->SetOptionList( $options, $current, $parameters );
  }

  /**
   * Add an attribute to this field.
   * @param unknown $field
   * @param unknown $k
   * @param unknown $v
   */
  function AddAttribute( $field, $k, $v ) {
    $this->Fields[$field]->AddAttribute($k,$v);

  }

  /**
   * Set the base table for the row query.
   * @param unknown $base_table
   */
  function SetBaseTable( $base_table ) {
    $this->BaseTable = $base_table;
  }

  /**
   * Set any joins
   * @param unknown $join_list
   */
  function SetJoins( $join_list ) {
    $this->Joins = $join_list;
  }


  /**
  * Accessor for the Title for the editor, which could set the title also.
  *
  * @param string $new_title The new title for the browser
  * @return string The current title for the browser
  */
  function Title( $new_title = null ) {
    if ( isset($new_title) ) $this->Title = $new_title;
    return $this->Title;
  }


  /**
   * Set the name of the SUBMIT button
   * @param unknown $new_submit
   */
  function SetSubmitName( $new_submit ) {
    $this->SubmitName = $new_submit;
  }

  function IsSubmit() {
    return isset($_POST[$this->SubmitName]);
  }

  /**
   * Magically knows whether you are in the processing the result of an update or a create.
   * @return boolean
   */
  function IsUpdate() {
    $is_update = $this->Available();
    if ( isset( $_POST['_editor_action']) && isset( $_POST['_editor_action'][$this->Id]) ) {
      $is_update = ( $_POST['_editor_action'][$this->Id] == 'update' );
      @dbg_error_log( 'editor', 'Checking update: %s => %d', $_POST['_editor_action'][$this->Id], $is_update );
    }
    return $is_update;
  }

  /**
   * The opposite of IsUpdate.  Really.
   * @return boolean
   */
  function IsCreate() {
    return ! $this->IsUpdate();
  }

  /**
   * Set the row selection criteria
   * @param unknown $where_clause
   */
  function SetWhere( $where_clause ) {
    $this->Where = $where_clause;
  }

  /**
   * Set the criteria used to find the new row after it got created.
   * @param unknown $where_clause
   */
  function WhereNewRecord( $where_clause ) {
    $this->NewWhere = $where_clause;
  }

  /**
   * Append more stuff to the WHERE clause
   * @param unknown $operator
   * @param unknown $more_where
   */
  function MoreWhere( $operator, $more_where ) {
    if ( $this->Where == "" ) {
      $this->Where = $more_where;
      return;
    }
    $this->Where = "$this->Where $operator $more_where";
  }

  function AndWhere( $more_where ) {
    $this->MoreWhere("AND",$more_where);
  }

  function OrWhere( $more_where ) {
    $this->MoreWhere("OR",$more_where);
  }

  /**
   * Set this to be the form display template.  It's better to use Layout($template) in general.
   *
   * @deprecated
   * @param string $template
   */
  function SetTemplate( $template ) {
    deprecated('Editor::SetTemplate');
    $this->Template = $template;
  }

  /**
   * Like SetTemplate($template) except it surrounds the template with a ##form## ... </form> if
   * there is not a form already in the template.
   *
   * @param string $template
   */
  function Layout( $template ) {
    if ( strstr( $template, '##form##' ) === false && stristr( $template, '<form' ) === false )
      $template = '##form##' . $template;
    if ( stristr( $template, '</form' ) === false ) $template .= '</form>';
    $this->Template = $template;
  }

  /**
   * Returns 'true' if we have read a row from the database (or set one through SetRecord()), 'false' otherwise.
   *
   * @return boolean
   */
  function Available( ) {
    return $this->RecordAvailable;
  }

  /**
   * Set a database row to load the field values from.
   *
   * @param object $row
   * @return object The row that was passed in.
   */
  function SetRecord( $row ) {
    $this->Record = $row;
    $this->RecordAvailable = is_object($this->Record);
    return $this->Record;
  }

  /**
  * Set some particular values to the ones from the array.
  *
  * @param array $values An array of fieldname / value pairs
  */
  function Initialise( $values ) {
    $this->RecordAvailable = false;
    if ( !isset($this->Record) ) $this->Record = (object) array();
    foreach( $values AS $fname => $value ) {
      $this->Record->{$fname} = $value;
    }
  }


  /**
  * This will assign $_POST values to the internal Values object for each
  * field that exists in the Fields array.
  */
  function PostToValues( $prefix = '' ) {
    foreach ( $this->Fields AS $fname => $fld ) {
      @dbg_error_log( 'editor', ":PostToValues: %s => %s", $fname, $_POST["$prefix$fname"] );
      if ( isset($_POST[$prefix.$fname]) ) {
        $this->Record->{$fname} = $_POST[$prefix.$fname];
        @dbg_error_log( 'editor', ":PostToValues: %s => %s", $fname, $_POST["$prefix$fname"] );
      }
    }
  }

  /**
   * Read the record from the database, optionally overriding the WHERE clause.
   *
   * @param string $where (optional) An SQL WHERE clause to override any previous SetWhere call.
   * @return object The row that was read from the database.
   */
  function GetRecord( $where = "" ) {
    global $session;
    $target_fields = "";
    foreach( $this->Fields AS $k => $column ) {
      if ( $target_fields != "" ) $target_fields .= ", ";
      $target_fields .= $column->GetTarget();
    }
    if ( $where == "" ) $where = $this->Where;
    $sql = sprintf( "SELECT %s FROM %s %s WHERE %s %s %s",
             $target_fields, $this->BaseTable, $this->Joins, $where, $this->Order, $this->Limit);
    $this->Query = new AwlQuery( $sql );
    @dbg_error_log( 'editor', "DBG: EditorGetQry: %s", $sql );
    if ( $this->Query->Exec("Browse:$this->Title:DoQuery") ) {
      $this->Record = $this->Query->Fetch();
      $this->RecordAvailable = is_object($this->Record);
    }
    if ( !$this->RecordAvailable ) {
      $this->Record = (object) array();
    }
    return $this->Record;
  }


  /**
  * Replace parts into the form template.  Parts that are replaceable are listed below:
  *   ##form##        A <form ...> tag.  You should close this with </form> or use Layout($template) which will take care of it for you.
  *   ##submit##      A <input type="submit" ...> tag for the form.
  *   ##f.options##   A list of options explicitly specified
  *   ##f.select##    A select list from the lookup SQL specified
  *   ##f.checkbox##  A checkbox, perhaps with a "_label" attribute
  *   ##f.input##     A normal input field.
  *   ##f.file##      A file upload field.
  *   ##f.money##     A money input field.
  *   ##f.date##      A date input field.
  *   ##f.textarea##  A textarea
  *   ##f.hidden##    A hidden input field
  *   ##f.password##  An input field for entering passwords without them being echoed to the screen
  *   ##f.enc##       Just print the value with special chars escaped for use in URLs.
  *   ##f.submit##    An <input type="submit" where you specify the field name.
  *
  * Most of these begin with "f", which should be replaced by the name of the field.  Many also take an option
  * after the name as well, so (for example) you can force the current value in ##options## or ##select## by
  * setting ##field.select.current##.  The input, file, money & date all accept the third parameter as a size
  * value, so ##fieldname.date.14## would be a 14-character-wide date field. Similarly a textarea allows for
  * a COLSxROWS value, so ##myfield.textarea.80x5## would be an 80-column textarea, five rows high.
  *
  * For ##fieldname.password.fakevalue## you can set the 'fake' value used to populate the password field so
  * that you can check for this on submit to be able to tell whether the password field has been edited.
  *
  * Other attributes are added to the <input ...> tag based on any SetAttributes() that may have been applied.
  *
  * @param array $matches The matches found which preg_replace_callback is calling us for.
  * @return string What we want to replace this match with.
  */
  function ReplaceEditorPart($matches)
  {
    global $session;

    // $matches[0] is the complete match
    switch( $matches[0] ) {
      case "##form##": /** @todo It might be nice to construct a form ID */
        return sprintf('<form method="POST" enctype="multipart/form-data" class="editor" id="%s">', $this->Id);
      case "##submit##":
        $action =  ( $this->RecordAvailable ? 'update' : 'insert' );
        $submittype = ($this->RecordAvailable ? translate('Apply Changes') : translate('Create'));
        return sprintf('<input type="hidden" name="_editor_action[%s]" value="%s"><input type="submit" class="submit" name="%s" value="%s">',
                                                              $this->Id, $action,                           $this->SubmitName, $submittype );
    }

    // $matches[1] the match for the first subpattern
    // enclosed in '(...)' and so on
    $field_name = $matches[1];
    $what_part = (isset($matches[3]) ? $matches[3] : null);
    $part3 = (isset($matches[5]) ? $matches[5] : null);

    $value_field_name = $field_name;
    if ( substr($field_name,0,4) == 'xxxx' ) {
        // Sometimes we will prepend 'xxxx' to the field name so that the field
        // name differs from the column name in the database.  We also remove it
        // when it's submitted.
        $value_field_name = substr($field_name,4);
    }

    $attributes = "";
    if ( isset($this->Fields[$field_name]) && is_object($this->Fields[$field_name]) ) {
      $field = $this->Fields[$field_name];
      $attributes = $field->RenderAttributes();
    }
    $field_value = (isset($this->Record->{$value_field_name}) ? $this->Record->{$value_field_name} : null);

    switch( $what_part ) {
      case "options":
        $currval = $part3;
        if ( ! isset($currval) && isset($field_value) )
          $currval = $field_value;
        if ( isset($field->OptionList) && $field->OptionList != "" ) {
          $option_list = $field->OptionList;
        }
        else {
          @dbg_error_log( 'editor', "DBG: Current=%s, OptionQuery: %s", $currval, $field->LookupSql );
          $opt_qry = new AwlQuery( $field->LookupSql );
          $option_list = EntryField::BuildOptionList($opt_qry, $currval, "FieldOptions: $field_name" );
          $field->OptionList = $option_list;
        }
        return $option_list;
      case "select":
        $currval = $part3;
        if ( ! isset($currval) && isset($field_value) )
          $currval = $field_value;
        if ( isset($field->OptionList) && $field->OptionList != "" ) {
          $option_list = $field->OptionList;
        }
        else {
          @dbg_error_log( 'editor', 'DBG: Current=%s, OptionQuery: %s', $currval, $field->LookupSql );
          $opt_qry = new AwlQuery( $field->LookupSql );
          $option_list = EntryField::BuildOptionList($opt_qry, $currval, 'FieldOptions: '.$field_name );
          $field->OptionList = $option_list;
        }
        return '<select class="entry" name="'.$field_name.'"'.$attributes.'>'.$option_list.'</select>';
      case "checkbox":
        if ( !isset($field) ) {
          @dbg_error_log("ERROR","Field '$field_name' is not defined.");
          return "<p>Error: '$field_name' is not defined.</p>";
        }
        if ( $field_value === true ) {
          $checked = ' CHECKED';
        }
        else {
          switch ( $field_value ) {
            case 'f':
            case 'off':
            case 'false':
            case '':
            case '0':
              $checked = "";
              break;

            default:
              $checked = ' CHECKED';
          }
        }
        return $field->RenderLabel('<input type="hidden" value="off" name="'.$field_name.'"><input class="entry" type="checkbox" value="on" name="'.$field_name.'"'.$checked.$attributes.'>' );
      case "input":
        $size = (isset($part3) ? $part3 : 6);
        return "<input class=\"entry\" value=\"".htmlspecialchars($field_value)."\" name=\"$field_name\" size=\"$size\"$attributes>";
      case "file":
        $size = (isset($part3) ? $part3 : 30);
        return "<input type=\"file\" class=\"entry\" value=\"".htmlspecialchars($field_value)."\" name=\"$field_name\" size=\"$size\"$attributes>";
      case "money":
        $size = (isset($part3) ? $part3 : 8);
        return "<input class=\"money\" value=\"".htmlspecialchars(sprintf("%0.2lf",$field_value))."\" name=\"$field_name\" size=\"$size\"$attributes>";
      case "date":
        $size = (isset($part3) ? $part3 : 10);
        return "<input class=\"date\" value=\"".htmlspecialchars($field_value)."\" name=\"$field_name\" size=\"$size\"$attributes>";
      case "textarea":
        list( $cols, $rows ) = explode( 'x', $part3);
        return "<textarea class=\"entry\" name=\"$field_name\" rows=\"$rows\" cols=\"$cols\"$attributes>".htmlspecialchars($field_value)."</textarea>";
      case "hidden":
        return sprintf( "<input type=\"hidden\" value=\"%s\" name=\"$field_name\">", htmlspecialchars($field_value) );
      case "password":
        return sprintf( "<input type=\"password\" value=\"%s\" name=\"$field_name\" size=\"10\">", htmlspecialchars($part3) );
      case "encval":
      case "enc":
        return htmlspecialchars($field_value);
      case "submit":
        $action =  ( $this->RecordAvailable ? 'update' : 'insert' );
        return sprintf('<input type="hidden" name="_editor_action[%s]" value="%s"><input type="submit" class="submit" name="%s" value="%s">',
                                                              $this->Id, $action, $this->SubmitName, $value_field_name );
      default:
        return str_replace( "\n", "<br />", $field_value );
    }
  }

  /**
  * Render the templated component.  The heavy lifting is done by the callback...
  */
  function Render( $title_tag = null ) {
    @dbg_error_log( 'editor', "classEditor", "Rendering editor $this->Title" );
    if ( $this->Template == "" ) $this->DefaultTemplate();

    $html = sprintf('<div class="editor" id="%s">', $this->Id);
    if ( isset($this->Title) && $this->Title != "" ) {
      if ( !isset($title_tag) ) $title_tag = 'h1';
      $html = "<$title_tag>$this->Title</$title_tag>\n";
    }

    // Stuff like "##fieldname.part## gets converted to the appropriate value
    $replaced = preg_replace_callback("/##([^#.]+)(\.([^#.]+))?(\.([^#.]+))?##/", array(&$this, "ReplaceEditorPart"), $this->Template );
    $html .= $replaced;

    $html .= '</div>';
    return $html;
  }

  /**
  * Write the record.  You might want to consider calling Editor::WhereNewRecord() before this if it might be creating a new record.
  * @param boolean $is_update Explicitly tell the write whether it's an update or insert.  Generally it should be able to figure it out though.
  */
  function Write( $is_update = null ) {
    global $c, $component;

    @dbg_error_log( 'editor', 'DBG: Writing editor %s', $this->Title);

    if ( !isset($is_update) ) {
      if ( isset( $_POST['_editor_action']) && isset( $_POST['_editor_action'][$this->Id]) ) {
        $is_update = ( $_POST['_editor_action'][$this->Id] == 'update' );
      }
      else {
        /** @todo Our old approach will not work for translation.  We need to have a hidden field
        * containing the submittype.  Probably we should add placeholders like ##form##, ##script## etc.
        * which the editor can use for internal purposes.
        */
        // Then we dvine the action by looking at the submit button value...
        $is_update = preg_match( '/(save|update|apply)/i', $_POST[$this->SubmitName] );
        dbg_error_log('WARN', $_SERVER['REQUEST_URI']. " is using a deprecated method for controlling insert/update" );
      }
    }
    $this->Action = ( $is_update ? "update" : "create" );
    $qry = new AwlQuery( sql_from_post( $this->Action, $this->BaseTable, "WHERE ".$this->Where ) );
    if ( !$qry->Exec("Editor::Write") ) {
      $c->messages[] = "ERROR: $qry->errorstring";
      return 0;
    }
    if ( $this->Action == "create" && isset($this->NewWhere) ) {
      $this->GetRecord($this->NewWhere);
    }
    else {
      $this->GetRecord($this->Where);
    }
    return $this->Record;
  }
}

