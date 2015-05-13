<?php
/**
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emailalert.php
 * @link       http://www.decisionpublique.fr
 * @license    GNU/GPL
*/

echo "<h1>".JText::_('List of reminder emails to send')."</h1>";

foreach( $this->users as $l ){
	echo 'Id: '.$l->id.'<br />';
	echo 'Name: '.$l->name.'<br />';
	echo 'Email: '.$l->email.'<br />';
	if($l->email_id == 16) echo 'Start program on '.$l->time_date.'<br />';
	echo $l->subject.'<br /><br />';
}
?>