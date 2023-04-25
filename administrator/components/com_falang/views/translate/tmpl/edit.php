<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\WebAsset\WebAssetManager;
use Joomla\Component\Finder\Administrator\Indexer\Parser\Html;

$input = Factory::getApplication()->input;
$document = Factory::getDocument();


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

$params = ComponentHelper::getParams('com_falang');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $document->getWebAssetManager();
$wa->useScript('form.validate');

//if no translator selected.
$translate_button_available = false;
if (!empty($params->get('translator')) && ('none' != strtolower($params->get('translator'))) && (!empty($params->get('translator_bingkey')) ||
                                           !empty($params->get('translator_googlekey'))  ||
                                           !empty($params->get('translator_yandexkey'))  ||
                                           !empty($params->get('translator_lingvanex')) )){
	require_once __DIR__ .'/../../../classes/translator.php';
	translatorFactory::getTranslator($this->select_language_id);
	$translate_button_available = true;
}

$act=$this->act;
$task=$this->task;
$select_language_id = $this->select_language_id;

$jfmanager = FalangManager::getInstance();
$active_language = $jfmanager->getLanguageByID($select_language_id);

$user = JFactory::getUser();
$db = JFactory::getDBO();
$elementTable = $this->actContentObject->getTable();
$input = JFactory::getApplication()->input;

$document = JFactory::getDocument();

//sbou4
$document = JFactory::getDocument();
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $document->getWebAssetManager();
$wa->useScript('form.validate');
$document->addScript('components/com_falang/assets/js/falang.js', array('version' => 'auto', 'relative' => true));
//use for images type
HTMLHelper::_('bootstrap.renderModal');

//use for toggle description
HTMLHelper::_('jquery.framework');
$document->addScript('components/com_falang/assets/js/jquery.cookie.js', array('version' => 'auto', 'relative' => true));

HTMLHelper::_('formbehavior.chosen', 'select');

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
$wysiwygeditor = \Joomla\CMS\Editor\Editor::getInstance();

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

<!-- Panel Header -->
<form action="index.php" method="post" name="adminForm" id="<?php echo $idForm; ?>" class="form-validate form-falang">
    <div class="container-fluid ">
        <div class="row falang-controls">
            <div class="left form-horizontal">
                <button id="toogle-source-panel" class="btn btn-sm btn-secondary"
                        data-show-reference="<?php echo JText::_('COM_FALANG_EDIT_SHOW_REFERENCE');?>"
                        data-hide-reference="<?php echo JText::_('COM_FALANG_EDIT_HIDE_REFERENCE');?>"><?php echo JText::_('COM_FALANG_EDIT_HIDE_REFERENCE');?>
                </button>            </div>
            <div class="right form-horizontal ">
                    <div class="alert alert-info span12">
                        <div class="span4">
                            <div class="control-group">
                                <div class="control-label"><?php echo JText::_('COM_FALANG_TRANSLATE_TITLE_PUBLISHED')?></div>
                                <div class="btn-group btn-group-yesno radio falang_publish_btn">
                                    <?php echo JHtml::_('select.booleanlist','published','class="inputbox"',$this->actContentObject->published);?>
                                </div>
                            </div>
                        </div>
                        <div class="span6">
                            <div class="control-group">
                                <div class="control-label"><?php echo JText::_('COM_FALANG_TRANSLATE_TITLE_DATECHANGED').': ';?><?php echo  $this->actContentObject->lastchanged ? JHTML::_('date',  $this->actContentObject->lastchanged, JText::_('DATE_FORMAT_LC2')):JText::_('new');?></div>
                            </div>
                        </div>
                        <div class="span2">
                            <div class="control-group">
                                <div class="control-label"><?php echo JText::_('COM_FALANG_TRANSLATE_TITLE_STATE').': ';?><?php echo $this->actContentObject->state > 0 ? JText::_('COM_FALANG_STATE_OK') : ($this->actContentObject->state < 0 ? JText::_('COM_FALANG_STATE_NOTEXISTING') : JText::_('COM_FALANG_STATE_CHANGED'));?></div>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
    </div>
    <div class="container-fluid ">
        <div class="row falang-headers">
            <div class="left form-horizontal">
                <h3><?php echo JText::_('COM_FALANG_EDIT_REFERENCE_TITLE');?></h3>
            </div>
            <div class="right form-horizontal ">
                <h3><?php echo JText::_('COM_FALANG_EDIT_TARGET_TITLE').' : '.$active_language->title;?><span id="flag">
							<?php echo JHtml::_('image', 'mod_languages/' .$active_language->image  . '.gif', $active_language->title , null, true); ?>
						</span>
                </h3>
            </div>
        </div>
    </div>


    <div class="falang-sidebyside">
            <?php
                foreach ($elementTable->Fields as $field) { ?>


                    <?php
                    //field params is not sidebyside field
                    if (strtolower($field->Type)=='params'){continue;}

	                $field->preHandle($elementTable);

	                // if we supress blank originals
	                if ($field->ignoreifblank && $field->originalValue==="") continue;

	                //display translatable field only
                    if( $field->Translate )
                    {
                        $translationContent = $field->translationContent;

                        //dispay title and alias
                        ?>
                        <!-- ************************* Field Translation <?php echo $field->Name;?>  *************************-->

                        <!-- set id to allow edit or update of the field  -->
                        <input type="hidden" name="id_<?php echo $field->Name;?>" value="<?php echo $translationContent->id;?>" />

                        <!-- hiddentext display only here and loop to the other field -->

                        <?php if( strtolower($field->Type)=='hiddentext') { ?>
                            <input type="hidden" name="id_<?php echo $field->Name;?>" value="<?php echo $translationContent->id;?>" />
                            <input type="hidden" name="origValue_<?php echo $field->Name;?>" value='<?php echo md5( $field->originalValue );?>' />
                            <textarea  name="origText_<?php echo $field->Name;?>" style="display:none"><?php echo $field->originalValue;?></textarea>
                            <textarea name="refField_<?php echo $field->Name;?>"  style="display:none"><?php echo $translationContent->value; ?></textarea>
                            <?php

                            continue;
                            }  ?>

                        <!-- ************************* SOURCE   ***************************** -->

                        <div class="outer-panel source-panel form-horizontal">

	                        <?php if ( $field->Name =='title' || $field->Name =='alias' ) { ?>
                                <div class="form-inline form-inline-header"><!--form-inline form-inline-header -->
                                    <div class="control-group">
                                        <div class="control-label">
                                            <label><?php echo $field->Lable; ?></label>
                                        </div>
                                        <div class="controls">
                                            <input class="form-control" type="text" readonly value="<?php echo htmlspecialchars($field->originalValue);?>" >
                                        </div>
                                    </div>
                                </div>
	                        <?php }  else { // end if title - label ?>

                            <!-- display other field exept title and alias -->
                            <div class="control-group">
                                <!-- display label on htmltext is different  and not display for hiidentext (aka fulltext) -->
                                    <?php if (strtolower($field->Type)!='htmltext') { ?>
                                        <div class="control-label">
                                                <label><?php echo $field->Lable; ?></label>
                                        </div>
                                    <?php } else { ?>
                                        <label><?php echo $field->Lable; ?></label>
                                    <?php } ?>

                                    <div class="controls <?php echo strtolower($field->Type)=='htmltext'?'controls-htmltext':''; ?>">

                                    <!-- fin display other field exept title and alias -->
                                    <!-- htmltext,text,textarea,image,param's,readonlytext,hiddentext-->
                                    <?php if (strtolower($field->Type)=='htmltext') { ?>
                                        <?php
                                            $editorFields[] = array( "editor_".$field->Name, "origText_".$field->Name );
                                            echo $wysiwygeditor->display("origText_".$field->Name,htmlspecialchars($field->originalValue, ENT_COMPAT, 'UTF-8'), '100%','300', '70', '15',$field->ebuttons);
                                        ?>
                                    <?php } //end if htmltext?>
                                    <?php if (strtolower($field->Type)=='titletext') { ?>
                                        <input class="form-control" type="text"  readonly value="<?php echo $field->originalValue;?>" >
                                    <?php } //end if text?>
                                    <?php if (strtolower($field->Type)=='text') { ?>
                                        <input class="form-control" type="text"  readonly value="<?php echo $field->originalValue;?>" >
                                    <?php } //end if text?>
                                    <?php if (strtolower($field->Type)=='textarea') { ?>
                                        <textarea class="form-control" readonly ><?php echo $field->originalValue; ?></textarea>
                                    <?php } //end if textarea?>

                                    <?php if (strtolower($field->Type)=='readonlytext') { ?>
                                        <input class="form-control" type="text" readonly placeholder="<?php echo $field->originalValue;?>">
                                    <?php } //end if readonlytext ?>

                                    <?php if (strtolower($field->Type)=='images') { ?>
                                        <input class="form-control" type="text"  readonly value="<?php echo $field->originalValue;?>" >
                                    <?php } //end if textarea?>

                                    <?php if (strtolower($field->Type)!='htmltext' &&
	                                    strtolower($field->Type)!='referenceid' &&
	                                    strtolower($field->Type)!='titletext' &&
                                        strtolower($field->Type)!='text' &&
                                        strtolower($field->Type)!='textarea' &&
                                        strtolower($field->Type)!='readonlytext' &&
	                                    strtolower($field->Type)!='images' &&
                                        strtolower($field->Type)!='hiddentext') { ?>
                                        <?php echo JText::_('COM_FALANG_TRANSATE_TYPE_NOT_EXIST')?>
                                    <?php } //end if other ?>
                                </div>
                            </div>
                        <?php }//end else title,alias ?>
                        </div><!-- source panel -->

                        <!-- ************************** ACTION   ******************************* -->
                        <div class="outer-panel action-panel">
                            <!-- add hidden use for translate/copy -->
                            <textarea  name="origText_<?php echo $field->Name;?>" style="display:none"><?php echo $field->originalValue;?></textarea>
                            <!-- use for html copy/translate htmltext -->
                            <span style="display:none" id="original_value_<?php echo $field->Name?>" name="original_value_<?php echo $field->Name;?>">
                                <?php
                                      if (preg_match("/<form/i",$field->originalValue)){
                                        $ovhref = JRoute::_("index.php?option=com_falang&task=translate.originalvalue&field=".$field->Name."&cid=".$this->actContentObject->id."&lang=".$select_language_id);
                                        echo '<a class="modal" rel="{handler: \'iframe\', size: {x: 700, y: 500}}" href="'.$ovhref.'" >'.JText::_("Content contains form - click here to view in popup window").'</a>';
                                      }
                                      else {
                                        echo $field->originalValue;
                                      }
                                      ?>
                            </span>
                            <!-- use for -->
                            <input type="hidden" name="origValue_<?php echo $field->Name;?>" value='<?php echo md5( $field->originalValue );?>' />


		                    <?php if ( strtolower($field->Type)=='readonlytext'){
			                    //specific case for menutype link
			                    if ($elementTable->Name == 'menu' && $field->Name == 'link') { ?>
                                    <a class="button btn" onclick="document.adminForm.refField_<?php echo $field->Name;?>.value = document.adminForm.origText_<?php echo $field->Name;?>.value;" title="<?php echo JText::_('COM_FALANG_BTN_COPY'); ?>"><i class="icon-copy"></i></a>
			                    <?php }
                                    if ($elementTable->Name == 'menu' && $field->Name == 'path') { ?>
                                        <!-- space need to have a side-->
                                        &nbsp;
			                    <?php } ?>
		                    <?php } ?>

		                    <?php if( strtolower($field->Type)!='htmltext' && strtolower($field->Type)!='readonlytext') {?>
                                <!-- Translate button -->
                                <a class="button btn" <?php echo $translate_button_available ? '': 'disabled="disabled"';?> onclick="document.adminForm.refField_<?php echo $field->Name;?>.value = translateService(document.adminForm.origText_<?php echo $field->Name;?>.value);" title="<?php echo JText::_('COM_FALANG_BTN_TRANSLATE'); ?>"><i class="icon-shuffle"></i></a>
                                <!-- Copy button -->
                                <a class="button btn" onclick="document.adminForm.refField_<?php echo $field->Name;?>.value = document.adminForm.origText_<?php echo $field->Name;?>.value;" title="<?php echo JText::_('COM_FALANG_BTN_COPY'); ?>"><i class="icon-copy"></i></a>
                                <!-- Delete button -->
                                <a class="button btn" onclick="document.adminForm.refField_<?php echo $field->Name;?>.value = '';" title="<?php echo JText::_('Delete'); ?>"><i class="icon-delete"></i></a>
		                    <?php } ?>

		                    <?php if( strtolower($field->Type)=='htmltext' && strtolower($field->Type)!='readonlytext') {?>
                                <!-- Translate button -->
                                <a class="button btn" <?php echo $translate_button_available ? '': 'disabled="disabled"';?>  onclick="copyToClipboard('<?php echo $field->Name;?>','translate');" title="<?php echo JText::_('COM_FALANG_BTN_TRANSLATE'); ?>"><i class="icon-shuffle"></i></a>
                                <!-- Copy button -->
                                <a class="button btn" onclick="copyToClipboard('<?php echo $field->Name;?>','copy');" title="<?php echo JText::_('COM_FALANG_BTN_COPY'); ?>"><i class="icon-copy"></i></a>
                                <!-- Delete button -->
                                <a class="button btn" onclick="copyToClipboard('<?php echo $field->Name;?>','clear');" title="<?php echo JText::_('Delete'); ?>"><i class="icon-delete"></i></a>
		                    <?php } ?>

                        </div>

                        <!-- ********************** TARGET   ************************** -->
                        <!-- display title and alias -->
                        <div class="outer-panel target-panel form-horizontal">
                            <?php if ( $field->Name =='title' || $field->Name =='alias' ) { ?>
                                <div class="form-inline form-inline-header"><!--form-inline form-inline-header -->
                                    <div class="control-group">
                                        <div class="control-label">
                                            <label><?php echo $field->Lable; ?></label>
                                        </div>
                                        <div class="controls">
                                            <input class="form-control" type="text" name="refField_<?php echo $field->Name;?>" id="refField_<?php echo $field->Name;?>" value="<?php echo htmlspecialchars($translationContent->value); ?>" >
                                        </div>
                                    </div>
                                </div>
                            <?php } else { // end if title - label ?>

                            <!-- display other field exept title and alias -->
                            <div class="control-group">
                                <!-- display label on htmltext  -->
                                <?php if (strtolower($field->Type)!='htmltext') { ?>
                                    <div class="control-label">
                                        <label><?php echo $field->Lable; ?></label>
                                    </div>
                                <?php } else { ?>
                                    <label><?php echo $field->Lable; ?></label>
                                <?php } ?>
                                <div class="controls <?php echo strtolower($field->Type)=='htmltext'?'controls-htmltext':''; ?>">
                                    <!-- fin display other field exept title and alias -->
                                    <!-- htmltext,text,textarea,image,param's,readonly,hiddentext-->
                                    <?php if (strtolower($field->Type)=='htmltext') { ?>
                                        <?php $editorFields[] = array( "editor_".$field->Name, "refField_".$field->Name );
                                        echo $wysiwygeditor->display("refField_".$field->Name,htmlspecialchars($translationContent->value, ENT_COMPAT, 'UTF-8'), '100%','300', '70', '15',$field->ebuttons);
                                        ?>
                                    <?php } //end if htmltext?>

	                                <?php if (strtolower($field->Type)=='titletext') { ?>
		                                <?php
		                                $length = ($field->Length>0)?$field->Length:60;
		                                $maxLength = ($field->MaxLength>0) ? "maxlength=".$field->MaxLength:"";?>
                                        <input class="form-control" type="text" name="refField_<?php echo $field->Name;?>" size="<?php echo $length;?>" value="<?php echo htmlspecialchars($translationContent->value); ?>" "<?php echo $maxLength;?>"/>
	                                <?php } //end if titletext?>

                                    <?php if (strtolower($field->Type)=='text') { ?>
                                        <?php
                                        $length = ($field->Length>0)?$field->Length:60;
	                                    $maxLength = ($field->MaxLength>0) ? "maxlength=".$field->MaxLength:"";?>
                                        <input class="form-control" type="text" name="refField_<?php echo $field->Name;?>" size="<?php echo $length;?>" value="<?php echo htmlspecialchars($translationContent->value); ?>" "<?php echo $maxLength;?>"/>
                                    <?php } //end if text?>

                                    <?php if (strtolower($field->Type)=='textarea') { ?>
                                        <textarea class="form-control" name="refField_<?php echo $field->Name;?>" ><?php echo $translationContent->value; ?></textarea>
                                    <?php } //end if textarea?>

                                    <?php if (strtolower($field->Type)=='readonlytext') {
                                        $value =  strlen($translationContent->value)>0? $translationContent->value:$field->originalValue;
	                                    $length = ($field->Length>0)?$field->Length:60;
	                                    $maxLength = ($field->MaxLength>0) ? "maxlength=".$field->MaxLength:"";
                                        ?>
                                        <input class="form-control" type="text" name="refField_<?php echo $field->Name;?>" size="<?php echo $length;?>" placeholder="<?php echo $value; ?>" value="<?php echo $value; ?>" maxlength="<?php echo $maxLength;?>" readonly>
                                    <?php } //end if readonlytext ?>

	                                <?php if (strtolower($field->Type)=='images') {
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
	                                <?php } //end if images ?>

                                    <?php if (strtolower($field->Type)!='htmltext' &&
	                                    strtolower($field->Type)!='referenceid' &&
	                                    strtolower($field->Type)!='titletext' &&
	                                    strtolower($field->Type)!='text' &&
	                                    strtolower($field->Type)!='textarea' &&
	                                    strtolower($field->Type)!='readonlytext' &&
	                                    strtolower($field->Type)!='images' &&
	                                    strtolower($field->Type)!='hiddentext') { ?>
                                        <?php echo JText::_('COM_FALANG_TRANSATE_TYPE_NOT_EXIST').':'.$field->Type; ?>
                                    <?php } //end if other ?>
                                </div>
                        </div>
                        <?php } //end else title,alias?>
                    </div><!-- target panel -->

                    <?php } // end if translatable  ?>
                    <div class="clr"></div>
                <?php }//end foreach ?>



    </div> <!-- sidebyside-->

    <!-- ********************  PARAMS   ********************* -->


    <h2><?php echo JText::_('COM_FALANG_TRANSLATE_PARAMS')?></h2>
    <div id="falang-params" class="form-horizontal falang-params">
        <?php   foreach ($elementTable->Fields as $field)
        {

	        //field params is the only filed managed here
	        //skip other
	        if (strtolower($field->Type)!='params'){continue;}

	        $field->preHandle($elementTable);

            if( $field->Translate )
            {
	            $translationContent = $field->translationContent;

	            $falangManager =  FalangManager::getInstance();
	            if ($falangManager->getCfg('copyparams',1) &&  $translationContent->value==""){
		            $translationContent->value = $field->originalValue;
	            }
	            ?>

                <input type="hidden" name="id_<?php echo $field->Name; ?>"
                       value="<?php echo $translationContent->id; ?>"/>
                <input type="hidden" name="origValue_<?php echo $field->Name; ?>"
                       value='<?php echo md5($field->originalValue); ?>'/>

                <textarea name="origText_<?php echo $field->Name; ?>"
                          style="display:none"><?php echo $field->originalValue; ?></textarea>
	            <?php
	            JLoader::import('models.TranslateParams', FALANG_ADMINPATH);
	            $tpclass = "TranslateParams_" . $elementTable->Name;
	            if (!class_exists($tpclass))
	            {
		            $tpclass = "TranslateParams";
	            }
	            $transparams = new $tpclass($field->originalValue, $translationContent->value, $field->Name, $elementTable->Fields);
	            // TODO sort out default value for author in params when editing new translation
	            $retval = $transparams->editTranslation();
	            if ($retval)
	            {
		            $editorFields[] = $retval;
	            }
            }//if translate
        }//end foreach
        ?>

    </div>
    <!-- ************************ Extra   ************************* -->
    <!-- extra for k2 items -->
    <div id="extras"></div>


	<!-- v 2.8.1 : submit code put at the end to have the editorFields set-->
	<script language="javascript" type="text/javascript">
		Joomla.submitbutton = function(task) {
			<?php
			if( isset($editorFields) && is_array($editorFields) ) {
				foreach ($editorFields as $editor) {
					// Where editor[0] = your areaname and editor[1] = the field name (ex 0:editor_introtext , 1:refField_introtext)
					//TODO 4.0 check below why it's not working anymore
                    //echo $wysiwygeditor->save( $editor[1]);
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

        jQuery(document).ready(function($) {
            // Attach behaviour to toggle button.
            $(document).on('click', '#toogle-source-panel', function () {
                var referenceHide = this.getAttribute('data-hide-reference');
                var referenceShow = this.getAttribute('data-show-reference');

                //trim necessary here but not in the joomla association ??
                if ($(this).text().trim() === referenceHide.trim()) {
                    $(this).text(referenceShow);
                }
                else {
                    $(this).text(referenceHide);
                }

                $('.source-panel').toggle();
                $('.action-panel').toggle();
                $('.falang-headers .left').toggle();
                $('.target-panel').toggleClass('full-width');
                //return false the toggle button is in the form
                return false;
            });

        });


	</script>


    <input type="hidden" name="select_language_id" value="<?php echo $select_language_id;?>" />
    <input type="hidden" name="reference_id" value="<?php echo $this->actContentObject->id;?>" />
    <input type="hidden" name="reference_table" value="<?php echo (isset($elementTable->name) ? $elementTable->name : '');?>" />
    <input type="hidden" name="catid" value="<?php echo $this->catid;?>" />
	<input type="hidden" name="option" value="com_falang" />
	<input type="hidden" name="task" value="translate.edit" />
	<input type="hidden" name="direct" value="<?php echo $input->getInt('direct',0);?>" />

	<?php echo HTMLHelper::_( 'form.token' ); ?>

</form>
