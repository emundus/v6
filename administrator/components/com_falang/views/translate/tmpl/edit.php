<?php
/**
 * @version		3.0
 * @package		Joomla
 * @subpackage	Falang
 * @author      Stéphane Bouey
 * @copyright	Copyright (C) 2012 Faboba
 * @license		GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );
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

$act=$this->act;
$task=$this->task;
$select_language_id = $this->select_language_id;
$user = JFactory::getUser();
$db = JFactory::getDBO();
$elementTable = $this->actContentObject->getTable();

$document = JFactory::getDocument();
$document->addScript('components/com_falang/assets/js/mambojavascript.js');

//use for images type
Jhtml::_('behavior.modal');

//use for validation
JHTML::_('behavior.formvalidation');



//use to name form to allow form validation
$idForm = 'adminForm';
switch ($elementTable->Name) {
    case 'modules':
        $idForm = 'module-form';
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
				if (window.clipboardData){
					window.clipboardData.setData("Text",innerHTML);
					alert("<?php echo JText::_('CLIPBOARD_COPIED'); ?>");
				}
				else {
					srcEl = document.getElementById("text_origText_"+value);
					srcEl.value = innerHTML;
					srcEl.select();
					alert("<?php echo JText::_('CLIPBOARD_COPY');?>");
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


<script language="javascript" type="text/javascript">
    Joomla.submitbutton = function(task) {
        <?php
        if( isset($editorFields) && is_array($editorFields) ) {
            foreach ($editorFields as $editor) {
                // Where editor[0] = your areaname and editor[1] = the field name
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


<form action="index.php" method="post" name="adminForm" id="<?php echo $idForm; ?>" class="form-validate">
    <table width="100%">
      <tr>
        <td>

            <legend><?php echo JText::_('COM_FALANG_TRANSLATE_TITLE_TRANSLATION')?></legend>

            <table width="90%" border="0" cellpadding="2" cellspacing="2" class="adminform table table-striped">
			<?php
			$k=1;
			for( $i=0; $i<count($elementTable->Fields); $i++ ) {
				$field = $elementTable->Fields[$i];
				
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
							<tr><td colspan="3" style="display:none"><td>
							<input type="hidden" name="id_<?php echo $field->Name;?>" value="<?php echo $translationContent->id;?>" />
							<input type="hidden" name="origValue_<?php echo $field->Name;?>" value='<?php echo md5( $field->originalValue );?>' />
							<textarea  name="origText_<?php echo $field->Name;?>" style="display:none"><?php echo $field->originalValue;?></textarea>
							<textarea name="refField_<?php echo $field->Name;?>"  style="display:none"><?php echo $translationContent->value; ?></textarea>
							</td></tr>
							<?php
					}
					else {
				?>
		    <tr class="<?php echo "row$k"; ?>">
		      <th colspan="3" align="left" class="falang"><?php echo JText::_('COM_FALANG_DBFIELDLABLE') .': '. $field->Lable;?></th>
		    </tr>
	      	<?php
	      	if (strtolower($field->Type)!='params'){
	      	?>
		    <tr class="<?php echo "row$k"; ?>">
		      <td align="left" valign="top"><?php echo JText::_('COM_FALANG_ORIGINAL');?></td>
		      <td align="left" valign="top" id="original_value_<?php echo $field->Name?>" name="original_value_<?php echo $field->Name?>">
		      <?php
		      if (preg_match("/<form/i",$field->originalValue)){
		      	$ovhref = JRoute::_("index.php?option=com_falang&task=translate.originalvalue&field=".$field->Name."&cid=".$this->actContentObject->id."&lang=".$select_language_id);
		      	echo '<a class="modal" rel="{handler: \'iframe\', size: {x: 700, y: 500}}" href="'.$ovhref.'" >'.JText::_("Content contains form - click here to view in popup window").'</a>';
		      }
		      else {
		      	echo $field->originalValue;
		      }
		      ?>
		      </td>
			  <td valign="top" class="button">
				<input type="hidden" name="origValue_<?php echo $field->Name;?>" value='<?php echo md5( $field->originalValue );?>' />
				<textarea  name="origText_<?php echo $field->Name;?>" style="display:none"><?php echo $field->originalValue;?></textarea>
				<?php
                if ( strtolower($field->Type)=='readonlytext'){
                    //specific case for menutype link
                    if ($elementTable->Name == 'menu' && $field->Name == 'link') { ?>
                        <?php if (FALANG_J30) { ?>
                            <a class="button btn" onclick="document.adminForm.refField_<?php echo $field->Name;?>.value = document.adminForm.origText_<?php echo $field->Name;?>.value;"><i class="icon-copy"></i><?php echo JText::_('COM_FALANG_BTN_COPY'); ?></a>
                        <?php } else { ?>
                            <a class="toolbar" onclick="document.adminForm.refField_<?php echo $field->Name;?>.value = document.adminForm.origText_<?php echo $field->Name;?>.value;"><span class="icon-32-copy"></span><?php echo JText::_('COM_FALANG_BTN_COPY'); ?></a>
                        <?php } ?>
                    <?php } ?>

                <?php } else if( strtolower($field->Type)!='htmltext' ) {?>
                        <?php if (FALANG_J30) { ?>
            					<a class="button btn" onclick="document.adminForm.refField_<?php echo $field->Name;?>.value = document.adminForm.origText_<?php echo $field->Name;?>.value;"><i class="icon-copy"></i><?php echo JText::_('COM_FALANG_BTN_COPY'); ?></a>
                        <?php } else { ?>
                                <a class="toolbar" onclick="document.adminForm.refField_<?php echo $field->Name;?>.value = document.adminForm.origText_<?php echo $field->Name;?>.value;"><span class="icon-32-copy"></span><?php echo JText::_('COM_FALANG_BTN_COPY'); ?></a>
                        <?php } ?>
				<?php }	else { ?>
                  <?php if (FALANG_J30) { ?>
                        <a class="button btn" onclick="copyToClipboard('<?php echo $field->Name;?>','copy');"><i class="icon-copy"></i><?php echo JText::_('COM_FALANG_BTN_COPY'); ?></a>
                      <?php } else { ?>
                        <a class="toolbar" onclick="copyToClipboard('<?php echo $field->Name;?>','copy');" onmouseout="MM_swapImgRestore();"><span class="icon-32-copy"></span><?php echo JText::_('COM_FALANG_BTN_COPY'); ?></a>
                      <?php } ?>
				<?php }?>
			  </td>
		    </tr>
		    <tr class="<?php echo "row$k"; ?>">
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
							echo $wysiwygeditor->display("refField_".$field->Name,htmlspecialchars($translationContent->value, ENT_COMPAT, 'UTF-8'), '100%','300', '70', '15',false);
						}  else if( strtolower($field->Type)=='images' ) {
                            $length = ($field->Length>0)?$field->Length:60;
                            $maxLength = ($field->MaxLength>0) ? "maxlength=".$field->MaxLength:"";
                            ?>
                            <?php if (FALANG_J30) { ?>
                                      <div class="input-prepend input-append">
                                          <input class="input-large" type="text" name="refField_<?php echo $field->Name;?>" id="refField_<?php echo $field->Name;?>" size="<?php echo $length;?>" value="<?php echo $translationContent->value; ?>" "<?php echo $maxLength;?>"/>
                                          <a class="modal btn" title="<?php echo JText::_("JSELECT")?>"
                                             href="index.php?option=com_media&amp;view=images&amp;tmpl=component&amp;fieldid=refField_<?php echo $field->Name;?>"<?php echo $field->Name;?>"
                                          rel="{handler: 'iframe', size: {x: 800, y: 500}}"><?php echo JText::_("JSELECT")?></a>
                                          <a class="btn hasTooltip" href="#" onclick="jInsertFieldValue('', 'refField_<?php echo $field->Name;?>');return false;" data-original-title="<?php echo JText::_("JDELETE")?>">
                                              <i class="icon-remove"></i></a>
                                      </div>
                                <?php } else {?>
                                      <input class="fltlft inputbox" type="text" name="refField_<?php echo $field->Name;?>" id="refField_<?php echo $field->Name;?>" size="<?php echo $length;?>" value="<?php echo $translationContent->value; ?>" "<?php echo $maxLength;?>"/>
                                      <div class="button2-left">
                                          <div class="blank">
                                              <a class="modal-button" title="<?php echo JText::_("COM_FALANG_BROWSE_IMAGES")?>"
                                                 href="index.php?option=com_media&amp;view=images&amp;tmpl=component&amp;fieldid=refField_<?php echo $field->Name;?>"<?php echo $field->Name;?>"
                                              rel="{handler: 'iframe', size: {x: 800, y: 500}}"><?php echo JText::_("COM_FALANG_BROWSE_IMAGES")?></a>
                                          </div>
                                      </div>
                                <?php }?>
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
					<?php
					 if ( strtolower($field->Type)=='readonlytext'){
					} else if( strtolower($field->Type)!='htmltext' ) {?>
                            <?php if (FALANG_J30) { ?>
                                <a class="button btn" onclick="document.adminForm.refField_<?php echo $field->Name;?>.value = '';"><i class="icon-delete"></i><?php echo JText::_('Delete'); ?></a>
                            <?php } else {?>
                                <a class="toolbar" onclick="document.adminForm.refField_<?php echo $field->Name;?>.value = '';"><span class="icon-32-delete"></span><?php echo JText::_('Delete'); ?></a>
                            <?php }?>
					<?php } else {?>
                            <?php if (FALANG_J30) { ?>
                                <a class="button btn" onclick="copyToClipboard('<?php echo $field->Name;?>','clear');"><i class="icon-delete"></i><?php echo JText::_('Delete'); ?></a>
                            <?php } else {?>
                                <a class="toolbar" onclick="copyToClipboard('<?php echo $field->Name;?>','clear');"><span class="icon-32-delete"></span><?php echo JText::_('Delete'); ?></a>
                            <?php }?>
					<?php }?>
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
		    <tr class="<?php echo "row$k"; ?>">
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
	      	?>
				<?php
				}
				$k=1-$k;
			}
				?>
		</table>
	  </td>
	  <td valign="top" width="30%">
        <legend><?php echo JText::_('COM_FALANG_TRANSLATE_PUBLISHING')?></legend>
		<?php
           //echo JHtml::_('tabs.start','translation');
           //echo JHtml::_('tabs.panel',JText::_('COM_FALANG_TRANSLATE_PUBLISHING'),"ItemInfo-page");

	  ?>
            <table width="100%" border="0" cellpadding="4" cellspacing="2" class="adminForm">
              <tr>
                <td width="34%"><strong><?php echo JText::_('COM_FALANG_TRANSLATE_TITLE_STATE');?>:</strong></td>
                <td width="50%"><?php echo $this->actContentObject->state > 0 ? JText::_('COM_FALANG_STATE_OK') : ($this->actContentObject->state < 0 ? JText::_('COM_FALANG_STATE_NOTEXISTING') : JText::_('COM_FALANG_STATE_CHANGED'));?></td>
              </tr>
              <tr>
                <td><strong><?php echo JText::_('COM_FALANG_LANGUAGE');?>:</strong></td>
                <td><?php echo $this->langlist;?></td>
              </tr>
              <tr>
                <td><strong><?php echo JText::_('COM_FALANG_TRANSLATE_TITLE_PUBLISHED')?>:</strong></td>
                <td><input type="checkbox" name="published" value="1" <?php echo $this->actContentObject->published&0x0001 ? 'checked="checked"' : ''; ?> /></td>
              </tr>
              <tr>
                <td><strong><?php echo JText::_('COM_FALANG_TRANSLATE_TITLE_DATECHANGED');?>:</strong></td>
                <td><?php echo  $this->actContentObject->lastchanged ? JHTML::_('date',  $this->actContentObject->lastchanged, JText::_('DATE_FORMAT_LC2')):JText::_('new');?></td>
              </tr>
              </table>

              <input type="hidden" name="select_language_id" value="<?php echo $select_language_id;?>" />
              <input type="hidden" name="reference_id" value="<?php echo $this->actContentObject->id;?>" />
              <input type="hidden" name="reference_table" value="<?php echo (isset($elementTable->name) ? $elementTable->name : '');?>" />
              <input type="hidden" name="catid" value="<?php echo $this->catid;?>" />
	  <?php
           //echo JHtml::_('tabs.end'); ?>
           </td></tr>
	        </table>

    <!-- v 1.4 : add content for extra plugins k2 ....-->
    <div id="extras"></div>

	<input type="hidden" name="option" value="com_falang" />
	<input type="hidden" name="task" value="translate.edit" />
	<input type="hidden" name="direct" value="<?php echo intval(JRequest::getVar("direct",0));?>" />

	<?php echo JHTML::_( 'form.token' ); ?>
</form>

