<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><fieldset>
	<div class="toolbar" id="toolbar" style="float: right;">
		<button class="btn" type="button" onclick="submitbutton('save_translation');"><img src="<?php echo HIKASHOP_IMAGES; ?>save.png"/><?php echo JText::_('OK'); ?></button>
	</div>
</fieldset>
<div class="iframedoc" id="iframedoc"></div>
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=plugins" method="post"  name="adminForm" id="adminForm" enctype="multipart/form-data">

<?php
	$type=$this->plugin_type;
	$id_field = $type.'_id';
	echo $this->tabs->startPane( 'translations');
		if(!empty($this->element->translations)){
			foreach($this->element->translations as $language_id => $translation){
				$plugin_name = $type.'_name';
				$plugin_name_input =$plugin_name.'_input';
				$plugin_description = $type.'_description';
				$this->$plugin_name_input = "translation[".$plugin_name."][".$language_id."]";
				$this->element->$plugin_name = @$translation->$plugin_name->value;
				$this->editor->name = 'translation_'.$type.'_description_'.$language_id;
				$this->element->$plugin_description = @$translation->$plugin_description->value;
				$plugin_name_published = $plugin_name.'_published';
				$plugin_name_id = $plugin_name.'_id';
				if(!empty($this->transHelper->falang) && isset($translation->$plugin_name->published)){
					$this->$plugin_name_published = $translation->$plugin_name->published;
					$this->$plugin_name_id = $translation->$plugin_name->id;
				}
				$plugin_description_published = $plugin_description.'_published';
				$plugin_description_id = $plugin_description.'_id';
				if(!empty($this->transHelper->falang) && isset($translation->$plugin_description->published)){
					$this->$plugin_description_published = $translation->$plugin_description->published;
					$this->$plugin_description_id = $translation->$plugin_description->id;
				}
				echo $this->tabs->startPanel($this->transHelper->getFlag($language_id), 'translation_'.$language_id);
					$this->setLayout('normal');
					echo $this->loadTemplate();
				echo $this->tabs->endPanel();
			}
		}
	echo $this->tabs->endPane();
?>
	<input type="hidden" name="cid" value="<?php echo $this->element->$id_field;?>"/>
	<input type="hidden" name="type" value="<?php echo $type;?>"/>
	<input type="hidden" name="ctrl" value="plugins" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
