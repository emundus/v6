<?php
defined('_JEXEC') or die('Restricted access');

?>

<?php if ($this->params->get('show-title', 0))
{?>
    <h1><?php echo $this->row->label;?></h1>
<?php }
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<div id="map_container" style="height=800px;">
</div>