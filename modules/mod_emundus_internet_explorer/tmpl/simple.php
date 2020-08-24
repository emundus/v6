<?php
defined('_JEXEC') or die;
header('Content-Type: text/html; charset=utf-8');
?>

<center>
	<div class="container">
		<div class="bandeau-internet-explorer">
			<a class="closeButton" onclick="deleteMessage()">&#10006;</a>
			<b><?= stripslashes(JText::_($message)) ?></b>
		</div>
	</div>
</center>

<script>
	function deleteMessage() {
		jQuery.ajax({
			url : "index.php?option=com_ajax&module=emundus_internet_explorer&method=closeMessage&format=json",
			async: true,
			cache: false,
			success : function(data) {
				document.getElementsByClassName('bandeau-internet-explorer')[0].style.display = "none";
			}
		});
	}
</script>