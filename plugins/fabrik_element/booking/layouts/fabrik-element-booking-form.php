<?php
defined('JPATH_BASE') or die;
$d = $displayData;
JFactory::getDocument()->addStyleSheet('plugins/fabrik_element/booking/dist/app_booking.css');
?>

<div id="root" data-attributes='<?php echo json_encode($d); ?>' class="fabrikinput fabrikElementReadOnly">
</div>

<div id="<?php echo $d->id; ?>" class="fabrikinput fabrikElementReadOnly">
    <script type="module" src="plugins/fabrik_element/booking/dist/app_booking.js"></script>
    <script type="module" src="plugins/fabrik_element/booking/dist/chunk.js"></script>
</div>
