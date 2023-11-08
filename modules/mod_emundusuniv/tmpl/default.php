<?php // no direct access
defined('_JEXEC') or die('Restricted access');
JHTML::stylesheet('emundus.css', 'modules/mod_emundusuniv/style/');

echo '<div id="emundusuniv">';
foreach ($univ as $u) {
	echo '> ' . $u . '<br />';
}
echo '</div>';
?>