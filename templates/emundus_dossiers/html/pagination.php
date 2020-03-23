<?php
defined('_JEXEC') or die;
function pagination_list_render($list)
{
$html = '<ul class="pagination">';
$html .= '<li>' . $list['start']['data'] . '</li>';
$html .= '<li>' . $list['previous']['data'] . '</li>';
foreach ($list['pages'] as $page){
if (!$page['active']){
$html .= '<li class="active" >' . $page['data'] . '</li>';
}
else{
$html .= '<li>' . $page['data'] . '</li>';
}
}
$html .= '<li>' . $list['next']['data'] . '</li>';
$html .= '<li>' . $list['end']['data'] . '</li>';
$html .= '</ul>';
return $html;
}
?>
