<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

/**
	* @return void
	* @param object $this->actContentObject
	* @param array $this->langlist
	* @param string $this->catid
	* @desc Shows the dialog for the content translation
	*/

if ($this->showMessage) {
	echo $this->loadTemplate('message');
}

$params = JComponentHelper::getParams('com_falang');

//if no translator selected.
$translate_button_available = false;
if (!empty($params->get('translator')) && (!empty($params->get('translator_bingkey')) || !empty($params->get('translator_yandexkey')) ) ){
	require_once __DIR__ .'/../../../classes/translator.php';
	translatorFactory::getTranslator($this->select_language_id);
	$translate_button_available = true;
}

$act=$this->act;
$task=$this->task;
$select_language_id = $this->select_language_id;
$user = JFactory::getUser();
$db = JFactory::getDBO();
$elementTable = $this->actContentObject->getTable();
$input = JFactory::getApplication()->input;

$document = JFactory::getDocument();
$document->addScript('components/com_falang/assets/js/mambojavascript.js');
//use for images type
Jhtml::_('behavior.modal');

//use for validation
JHTML::_('behavior.formvalidation');

//use for toggle description
JHtml::_('jquery.framework');
$document->addScript('components/com_falang/assets/js/jquery.cookie.js');

JHtml::_('formbehavior.chosen', 'select');

//use to name form to allow form validation
$idForm = 'adminForm';
switch ($elementTable->Name) {
    case 'modules':
        $idForm = 'module-form';
		//add view-module for widgetkit support
		$input->set('view','module');
        break;
    case 'banners':
        $idForm = 'banner-form';
        break;
    case 'menu':
        $idForm = 'item-form';
        break;
    case 'categories':
        $idForm = 'item-form';
        break;
    case 'contact_details':
        $idForm = 'contact-form';
        break;
    case 'weblinks':
        $idForm = 'weblink-form';
        break;
}

jimport( 'joomla.html.editor' );
$wysiwygeditor = JFactory::getEditor();

$editorFields=null;
foreach ($this->tranFilters as $filter) {
	echo "<input type='hidden' name='".$filter->filterType."_filter_value' value='".$filter->filter_value."'/>";
}

// check system and user editor and load appropriate copying script
$user = JFactory::getUser();
$conf = JFactory::getConfig();

$editor = $conf->get('editor');
//TODO sbou check this
// Place a reference to the element Table in the config so that it can be used in translation of urlparams !!!
$conf->set('falang.elementTable',$elementTable);


echo "\n<!-- editor is $editor //-->\n";
$editorFile = FALANG_ADMINPATH."/editors/".strtolower($editor).".php";
if (file_exists($editorFile)){
	require_once($editorFile);
}
else {
	?>
	<script language="javascript" type="text/javascript">
	function copyToClipboard(value,action) {
		try {
			if (document.getElementById) {
				innerHTML="";
				if (action=="copy") {
					srcEl = document.getElementById("original_value_"+value);
					innerHTML = srcEl.innerHTML;
				}
				if (action=="translate") {
					srcEl = document.getElementById("original_value_"+value);
					innerHTML = translateService(srcEl.innerHTML);
				}
				if (window.clipboardData){
					window.clipboardData.setData("Text",innerHTML);
					alert("<?php echo JText::_('CLIPBOARD_COPIED'); ?>");
				}
				else {
					srcEl = document.getElementById("text_origText_"+value);
					if (srcEl != null) {
						srcEl.value = innerHTML;
						srcEl.select();
						alert("<?php echo JText::_('CLIPBOARD_COPY');?>");
					} else {
						srcEl = document.getElementById("refField_"+value);
						if (srcEl != null) {
							srcEl.value = innerHTML;
							srcEl.select();
							alert("<?php echo JText::_('CLIPBOARD_COPY');?>");
						}
					}
				}
			}
		}
		catch(e){
			alert("<?php echo JText::_('CLIPBOARD_NOSUPPORT');?>");
		}
	}
    </script>
<?php } ?>

<script language="javascript" type="text/javascript">

    //add insert image name for image type
    function jInsertFieldValue(value,id) {
        var old_id = document.getElementById(id).value;
        if (old_id != id) {
            document.getElementById(id).value = value;
        }
    }

	function confirmChangeLanguage(fromLang, fromIndex){
		selections = document.getElementsByName("language_id")[0].options;
		selection = document.getElementsByName("language_id")[0].selectedIndex;
		//alert(selection+" from "+ fromIndex+" which is "+fromLang+" xx "+document.getElementsByName("language_id")[0].value);
		var toLang = selections[selection].text;
		var toValue = selection = document.getElementsByName("language_id")[0].value;
		if (fromIndex!=toValue){
			answer = confirm("<?php echo preg_replace( '#<br\s*/>#', '\n', JText::_('JS_CHANGE_TRANSLATION_LANGUAGE')); ?>");
			if (!answer) {
				document.getElementsByName("language_id")[0].selectedIndex=fromIndex;
			}
		}
		else {
			alert("<?php echo preg_replace( '#<br\s*/>#', '\n', JText::_('JS_REINSTATE_TRANSLATION_LANGUAGE',true)); ?>");
		}
	}
    </script>


<form action="index.php" method="post" name="adminForm" id="<?php echo $idForm; ?>" class="form-validate">
    <div class="span9 form-horizontal">
            <legend><?php echo JText::_('COM_FALANG_TRANSLATE_TITLE_TRANSLATION')?></legend>

            <table width="90%" border="0" cellpadding="2" cellspacing="2" class="adminform table table-striped">
			<?php
			foreach ($elementTable->Fields as $field) {

				$field->preHandle($elementTable);
				$originalValue = $field->originalValue;

				// if we supress blank originals
				if ($field->ignoreifblank && $field->originalValue==="") continue;

				if( $field->Translate ) {
					$translationContent = $field->translationContent;

					// This causes problems in Japanese/Russian etc. ??
					//jimport('joomla.filter.output');
					//JFilterOutput::objectHTMLSafe( $translationContent );


					if( strtolower($field->Type)=='hiddentext') {
							?>
							<tr style="display: none"><td colspan="3">
							<input type="hidden" name="id_<?php echo $field->Name;?>" value="<?php echo $translationContent->id;?>" />
							<input type="hidden" name="origValue_<?php echo $field->Name;?>" value='<?php echo md5( $field->originalValue );?>' />
							<textarea  name="origText_<?php echo $field->Name;?>" style="display:none"><?php echo $field->originalValue;?></textarea>
							<textarea name="refField_<?php echo $field->Name;?>"  style="display:none"><?php echo $translationContent->value; ?></textarea>
							</td></tr>
							<?php
					}
					else {
				?>
		    <tr>
		      <th colspan="3" align="left" class="falang">


                  <div class="falang_field_lable">
                  <?php echo JText::_('COM_FALANG_DBFIELDLABLE') .': '. $field->Lable;?>
                  </div>
                  <div class="falang_field_action">
                      <!-- add hidden use for translate/copy -->
                      <input type="hidden" name="origValue_<?php echo $field->Name;?>" value='<?php echo md5( $field->originalValue );?>' />
                      <textarea  name="origText_<?php echo $field->Name;?>" style="display:none"><?php echo $field->originalValue;?></textarea>
                      <?php
						if ( strtolower($field->Type)=='params'){
						    //no action on params field
						} else if ( strtolower($field->Type)=='readonlytext'){
                          //specific case for menutype link
                          if ($elementTable->Name == 'menu' && $field->Name == 'link') { ?>
                              <a class="button btn" onclick="document.adminForm.refField_<?php echo $field->Name;?>.value = document.adminForm.origText_<?php echo $field->Name;?>.value;"><i class="icon-copy"></i><?php echo JText::_('COM_FALANG_BTN_COPY'); ?></a>
                          <?php } ?>

                      <?php } else if( strtolower($field->Type)!='htmltext' ) {?>
                          <!-- Translate button -->

                          <a class="button btn" <?php echo $translate_button_available ? '': 'disabled="disabled"';?> onclick="document.adminForm.refField_<?php echo $field->Name;?>.value = translateService(document.adminForm.origText_<?php echo $field->Name;?>.value);"><i class="icon-shuffle"></i><?php echo JText::_('COM_FALANG_BTN_TRANSLATE'); ?></a>
                          <!-- Copy button -->
                          <a class="button btn" onclick="document.adminForm.refField_<?php echo $field->Name;?>.value = document.adminForm.origText_<?php echo $field->Name;?>.value;"><i class="icon-copy"></i><?php echo JText::_('COM_FALANG_BTN_COPY'); ?></a>
                          <!-- Delete button -->
                          <a class="button btn" onclick="document.adminForm.refField_<?php echo $field->Name;?>.value = '';"><i class="icon-delete"></i><?php echo JText::_('Delete'); ?></a>
                      <?php }	else { ?>
                          <!-- Translate button -->
                          <a class="button btn" <?php echo $translate_button_available ? '': 'disabled="disabled"';?>  onclick="copyToClipboard('<?php echo $field->Name;?>','translate');"><i class="icon-shuffle"></i><?php echo JText::_('COM_FALANG_BTN_TRANSLATE'); ?></a>
                          <!-- Copy button -->
                          <a class="button btn" onclick="copyToClipboard('<?php echo $field->Name;?>','copy');"><i class="icon-copy"></i><?php echo JText::_('COM_FALANG_BTN_COPY'); ?></a>
                          <!-- Delete button -->
                          <a class="button btn" onclick="copyToClipboard('<?php echo $field->Name;?>','clear');"><i class="icon-delete"></i><?php echo JText::_('Delete'); ?></a>
                      <?php }?>
                  </div>
              </th>
		    </tr>
	      	<?php
	      	if (strtolower($field->Type)!='params'){
	      	?>
		    <tr  class="row_original">
		      <td align="left" valign="top"><?php echo JText::_('COM_FALANG_ORIGINAL');?></td>
				<?php
				// Hrvoje -
				// if text is introtext than remove id from td.
				// instead copyfunction will use text from collapsible div
				if ($field->Name =="introtext") : ?>
				<td align="left" valign="top">
					<?php
					// Hrvoje - use original table code
					else : ?>
				<td align="left" valign="top" id="original_value_<?php echo $field->Name?>" name="original_value_<?php echo $field->Name ?>">
					<?php endif; ?>




					<?php
					// Hrvoje - Text Toggler part 1 Start - adds my code to introtext only
					if ($field->Name =="introtext") : ?>
						<div id="ToggleButton" class="btn btn-small">Toggle Text</div>
						<div id="original_value_<?php echo $field->Name?>">
					<?php endif;?>

		      <?php
		      if (preg_match("/<form/i",$field->originalValue)){
		      	$ovhref = JRoute::_("index.php?option=com_falang&task=translate.originalvalue&field=".$field->Name."&cid=".$this->actContentObject->id."&lang=".$select_language_id);
		      	echo '<a class="modal" rel="{handler: \'iframe\', size: {x: 700, y: 500}}" href="'.$ovhref.'" >'.JText::_("Content contains form - click here to view in popup window").'</a>';
		      }
		      else {
		      	echo $field->originalValue;
		      }
		      ?>

		<?php // Hrvoje - Text Toggler - part 2 Start
		if ($field->Name =="introtext") : ?>

        </div>
        <script type="text/javascript">
        jQuery(document).ready(function(){
            // FOTTState = Falang Original Text Toggle State Cookie
            // Reads FOTTState cookie
            var ToggleState = jQuery.cookie('FOTTState');


            // if FOTTState cookie exists and if its value is 0 it hides layer
            // else it shows it. It also shows it by default if FOTTState is not defined yet
            if(ToggleState == '0'){
                    jQuery("#original_value_<?php echo $field->Name?>").hide();
					jQuery("#CopyButton").hide();
            }


			jQuery("#ToggleButton").click(function(){

				jQuery("#original_value_<?php echo $field->Name?>").slideToggle(500);
				jQuery("#CopyButton").slideToggle(500);

                // click on toggle button also creates FOTTState cookie or updates its value+
                if(ToggleState == '1' || !ToggleState){
					jQuery.cookie('FOTTState', '0');
                } else {
					jQuery.cookie('FOTTState', '1');
                }
                // FOTTState cookie exipres at end of session
            });
        });
        </script>

		<?php endif; // Hrvoje - Text Toggler - part 2 End ?>

		      </td>
			  <td valign="top" class="button">
			  </td>
		    </tr>
		    <tr>
		      <td align="left" valign="top"><?php echo JText::_('COM_FALANG_TRANSLATION');?></td>
		      <td align="left" valign="top">
					  <input type="hidden" name="id_<?php echo $field->Name;?>" value="<?php echo $translationContent->id;?>" />
						<?php
						if( strtolower($field->Type)=='text' || strtolower($field->Type)=='titletext' ) {
							$length = ($field->Length>0)?$field->Length:60;
							$maxLength = ($field->MaxLength>0) ? "maxlength=".$field->MaxLength:"";
							?>
							<input class="inputbox" type="text" name="refField_<?php echo $field->Name;?>" size="<?php echo $length;?>" value="<?php echo htmlspecialchars($translationContent->value); ?>" "<?php echo $maxLength;?>"/>

							<?php
						} else if( strtolower($field->Type)=='textarea' ) {
							$ta_rows = ($field->Rows>0)?$field->Rows:15;
							$ta_cols = ($field->Columns>0)?$field->Columns:30;
							?>
							<textarea name="refField_<?php echo $field->Name;?>"  rows="<?php echo $ta_rows;?>" cols="<?php echo $ta_cols;?>" ><?php echo $translationContent->value; ?></textarea>
							<?php
						} else if( strtolower($field->Type)=='htmltext' ) {
							?>
							<?php
							$editorFields[] = array( "editor_".$field->Name, "refField_".$field->Name );
							// parameters : areaname, content, hidden field, width, height, rows, cols
							//v2.1 fix html encoding display
							//v2.1.1 fix extra button due to 2.1 regression
							echo $wysiwygeditor->display("refField_".$field->Name,htmlspecialchars($translationContent->value, ENT_COMPAT, 'UTF-8'), '100%','300', '70', '15',$field->ebuttons);
						}  else if( strtolower($field->Type)=='images' ) {
                            $length = ($field->Length>0)?$field->Length:60;
                            $maxLength = ($field->MaxLength>0) ? "maxlength=".$field->MaxLength:"";
                            ?>
                              <div class="input-prepend input-append">
                                  <input class="input-large" type="text" name="refField_<?php echo $field->Name;?>" id="refField_<?php echo $field->Name;?>" size="<?php echo $length;?>" value="<?php echo $translationContent->value; ?>" "<?php echo $maxLength;?>"/>
                                  <a class="modal btn" title="<?php echo JText::_("JSELECT")?>"
                                     href="index.php?option=com_media&amp;view=images&amp;tmpl=component&amp;fieldid=refField_<?php echo $field->Name;?>"<?php echo $field->Name;?>"
                                  rel="{handler: 'iframe', size: {x: 800, y: 500}}"><?php echo JText::_("JSELECT")?></a>
                                  <a class="btn hasTooltip" href="#" onclick="jInsertFieldValue('', 'refField_<?php echo $field->Name;?>');return false;" data-original-title="<?php echo JText::_("JDELETE")?>">
                                      <i class="icon-remove"></i></a>
                              </div>
                           <?php
                        } else if( strtolower($field->Type)=='readonlytext') {
							$length = ($field->Length>0)?$field->Length:60;
							$maxLength = ($field->MaxLength>0)?$field->MaxLength:60;
							$value =  strlen($translationContent->value)>0? $translationContent->value:$field->originalValue;
							?>
							<input class="inputbox" readonly="yes" type="text" name="refField_<?php echo $field->Name;?>" size="<?php echo $length;?>" value="<?php echo $value; ?>" maxlength="<?php echo $maxLength;?>"/>
							<?php
						}
						?>
				</td>
				<td valign="top" class="button">
				</td>
		    </tr>
	      	<?php
	      	}
	      	// else if params
	      	else {
	      		// Special Params handling
	      		// if translated value is blank then we always copy across the original value
	      		$falangManager =  FalangManager::getInstance();
	      		if ($falangManager->getCfg('copyparams',1) &&  $translationContent->value==""){
		      		$translationContent->value = $field->originalValue;
	      		}
	      	?>
		    <tr class="">
		      <td colspan="3">
                      <input type="hidden" name="origValue_<?php echo $field->Name;?>" value='<?php echo md5( $field->originalValue );?>' />
                      <textarea  name="origText_<?php echo $field->Name;?>" style="display:none"><?php echo $field->originalValue;?></textarea>
                      <input type="hidden" name="id_<?php echo $field->Name;?>" value="<?php echo $translationContent->id;?>" />

			      <?php
                  JLoader::import( 'models.TranslateParams',FALANG_ADMINPATH);
			      $tpclass = "TranslateParams_".$elementTable->Name;
			      if (!class_exists($tpclass)){
			      	$tpclass = "TranslateParams";
			      }
			      $transparams = new $tpclass($field->originalValue,$translationContent->value, $field->Name,$elementTable->Fields);
				// TODO sort out default value for author in params when editing new translation
				$retval = $transparams->editTranslation();
				if ($retval){
					$editorFields[] = $retval;
				}
				?>
		      </td>
		    </tr>
      	<?php
	      			}
				}
			}
		}
		?>
		</table>
	  </div>

	  <div class="span3 form-horizontal alert alert-info">
        <h4><?php echo JText::_('COM_FALANG_TRANSLATE_PUBLISHING')?></h4>
          <div class="control-group">
              <div class="control-label"><?php echo JText::_('COM_FALANG_TRANSLATE_TITLE_STATE');?></div>
              <div class="controls"><?php echo $this->actContentObject->state > 0 ? JText::_('COM_FALANG_STATE_OK') : ($this->actContentObject->state < 0 ? JText::_('COM_FALANG_STATE_NOTEXISTING') : JText::_('COM_FALANG_STATE_CHANGED'));?></div>
          </div>
          <div class="control-group">
              <div class="control-label"><?php echo JText::_('COM_FALANG_LANGUAGE');?></div>
              <div class="controls"><?php echo $this->langlist;?></div>
          </div>
          <div class="control-group">
              <div class="control-label"><?php echo JText::_('COM_FALANG_TRANSLATE_TITLE_PUBLISHED')?></div>
              <div class="btn-group btn-group-yesno radio falang_publish_btn">
                     <?php echo JHtml::_('select.booleanlist','published','class="inputbox"',$this->actContentObject->published);?>
              </div>
          </div>
          <div class="control-group">
              <div class="control-label"><?php echo JText::_('COM_FALANG_TRANSLATE_TITLE_DATECHANGED');?></div>
              <div class="controls"><?php echo  $this->actContentObject->lastchanged ? JHTML::_('date',  $this->actContentObject->lastchanged, JText::_('DATE_FORMAT_LC2')):JText::_('new');?></div>
          </div>
              <input type="hidden" name="select_language_id" value="<?php echo $select_language_id;?>" />
              <input type="hidden" name="reference_id" value="<?php echo $this->actContentObject->id;?>" />
              <input type="hidden" name="reference_table" value="<?php echo (isset($elementTable->name) ? $elementTable->name : '');?>" />
              <input type="hidden" name="catid" value="<?php echo $this->catid;?>" />
		</div>

    <!-- v 1.4 : add content for extra plugins k2 ....-->
    <div class="span9" id="extras"></div>

	<!-- v 2.8.1 : submit code put at the end to have the editorFields set-->
	<script language="javascript" type="text/javascript">
		Joomla.submitbutton = function(task) {
			<?php
			if( isset($editorFields) && is_array($editorFields) ) {
				foreach ($editorFields as $editor) {
					// Where editor[0] = your areaname and editor[1] = the field name (ex 0:editor_introtext , 1:refField_introtext)
					echo $wysiwygeditor->save( $editor[1]);
				}
			}
			?>

			if (task == 'translate.cancel') {
				Joomla.submitform( task, document.getElementById('<?php echo $idForm;?>') );
				return;
			} else {
				Joomla.submitform( task, document.getElementById('<?php echo $idForm;?>') );
			}
		}
	</script>



	<input type="hidden" name="option" value="com_falang" />
	<input type="hidden" name="task" value="translate.edit" />
	<input type="hidden" name="direct" value="<?php echo intval(JRequest::getVar("direct",0));?>" />

	<?php echo JHTML::_( 'form.token' ); ?>
</form>
