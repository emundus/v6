<?php

defined('JPATH_BASE') or die;

?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<div id="<?php echo $displayData['attributes']['name']; ?>___map_container" class="fabrikSubElementContainer fabrikEmundusGeolocalisation">
</div>

<input id="<?php echo $displayData['attributes']['name']; ?>" type="text"/>