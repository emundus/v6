<?php
defined('JPATH_BASE') or die;

$d = $displayData;

foreach ($d->data as $d)
{
    echo '<span style="display:inline-block; width:20px; height:20px; background-color:' . $d . '; border-radius:50%; margin: 5px;"></span>';
}
