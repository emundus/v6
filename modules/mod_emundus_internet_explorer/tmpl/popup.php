<?php
defined('_JEXEC') or die;
header('Content-Type: text/html; charset=utf-8');
$document = JFactory::getDocument();
//Chart.js is the libary used for this module's graphs
$document->addScript('media'.DS.'com_emundus'.DS.'lib'.DS.'Chart.min.js');
//moment.js is a Date libary, using to retrieve missing dates
$document->addScript('media'.DS.'com_emundus'.DS.'lib'.DS.'moment.min.js');
$document->addStyleSheet('media'.DS.'com_emundus'.DS.'lib'.DS.'bootstrap-336'.DS.'css'.DS.'bootstrap.min.css');
$document->addStyleSheet('media'.DS.'com_emundus'.DS.'lib'.DS.'Semantic-UI-CSS-master'.DS.'semantic.min.css');
?>

<?php if($show) { ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
<script>
	jQuery(document).ready( function() {
		Swal.fire({
			title: '',
			html: '<?php echo addslashes(JText::_($message)) ?>',
			icon: 'warning'
		}).then(function(result) {
			jQuery.ajax({
				url : "index.php?option=com_ajax&module=emundus_internet_explorer&method=closeMessage&format=json",
				async: true,
				cache: false
			});
		});
	});
</script>
<?php } ?>