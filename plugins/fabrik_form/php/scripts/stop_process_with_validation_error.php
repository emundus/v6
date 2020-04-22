<?php
/**
 * this is a very simple example of a script to stop the form being processed
 * The form is then redisplayed with an error
 * notice at the top of the form (and the previously filled in data still visible)
 */
$this->formModel->_arErrors['tablename___elementname'][] = 'woops!';
return false;
?>