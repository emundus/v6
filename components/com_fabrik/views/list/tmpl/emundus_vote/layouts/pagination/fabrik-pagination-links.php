<?php
/**
 * Layout: List Pagination Footer
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.3.3
 */

$d = $displayData;
$list = $d->list;
$startClass = $list['start']['active'] == 1 ? ' ' : ' active';
$prevClass = $list['previous']['active'] == 1 ? ' ' : ' active';
$nextClass = $list['next']['active'] == 1 ? ' ' : ' active';
$endClass = $list['end']['active'] == 1 ? ' ' : ' active';

$nav_mapping = [
    'start' => 'keyboard_double_arrow_left',
    'previous' => 'keyboard_arrow_left',
    'next' => 'keyboard_arrow_right',
    'end' => 'keyboard_double_arrow_right'
];

foreach ($nav_mapping as $position => $nav) {
	$dom = new DOMDocument;
	$dom->loadHTML($list[$position]['data']);
	$xpath = new DOMXPath($dom);
	$nodes = $xpath->query("//a");
	foreach ($nodes as $node) {
		$node->textContent = '';
		$icon              = $dom->createElement('span', $nav);
		$icon->setAttribute('class', 'material-icons-outlined');
		$node->appendChild($icon);
	}
	$list[$position]['data'] = $dom->saveHTML();
}

?>
<div class="pagination">
	<ul class="pagination-list em-flex-row">
		<li class="pagination-start<?php echo $startClass; ?>">
			<?php echo $list['start']['data']; ?>
		</li>
		<li class="pagination-prev<?php echo $prevClass; ?>">
			<?php echo $list['previous']['data']; ?>
		</li>
		<?php
		foreach ($list['pages'] as $page) :
			$class = $page['active'] == 1 ? '' : 'active'; ?>
			<li class="<?php echo $class; ?>">
				<?php echo $page['data']; ?>
			</li>
		<?php endforeach ;?>

		<li class="pagination-next<?php echo $nextClass; ?>">
			<?php echo $list['next']['data'];?>
		</li>
		<li class="pagination-end<?php echo $endClass; ?>">
			<?php echo $list['end']['data'];?>
		</li>
	</ul>
</div>