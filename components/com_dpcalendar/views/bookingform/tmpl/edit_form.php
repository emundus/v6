<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

?>
<div class="form-horizontal" id="dp-booking-payment-details">
	<?php
	echo $this->form->renderField('name');
	echo $this->form->renderField('email');
	echo $this->form->renderField('country');
	echo $this->form->renderField('province');
	echo $this->form->renderField('city');
	echo $this->form->renderField('zip');
	echo $this->form->renderField('street');
	echo $this->form->renderField('number');
	echo $this->form->renderField('telephone');

	$fieldSets = $this->form->getFieldsets('params');
	foreach ($fieldSets as $name => $fieldSet)
	{
		foreach ($this->form->getFieldset($name) as $field)
		{
			echo $field->renderField();
		}
	}

	echo $this->form->renderField('captcha');
	?>
	</div>
<?php
echo $this->form->getInput('id');
echo $this->form->getInput('user_id');
echo $this->form->getInput('book_date');
?>
<input type="hidden" name="return" value="<?php echo $this->return_page;?>" />
<input type="hidden" name="task" value="" />
<?php echo JHtml::_('form.token'); ?>
