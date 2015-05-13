<?php
function saySomething($msg, $formModel) {
	$example_element_value = $formModel->getElementData('fab_calc_test___calc_test_2', false, 'default value');
	$app = JFactory::getApplication();
	$app->enqueueMessage($msg . ": " . $example_element_value);
	return true;
}
?>