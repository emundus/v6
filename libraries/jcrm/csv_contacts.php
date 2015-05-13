<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    switch($last) {
        // Le modifieur 'G' est disponible depuis PHP 5.1.0
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }

    return $val;
}

function getProfiles()
{
	$db = JFactory::getDBO();
	$query = 'SELECT esp.id, esp.label, esp.acl_aro_groups, caag.lft FROM #__emundus_setup_profiles esp 
	INNER JOIN #__core_acl_aro_groups caag on esp.acl_aro_groups=caag.id 
	ORDER BY caag.lft, esp.label';
	$db->setQuery( $query );
	return $db->loadObjectList('id');
}
function selected($users) {
/* JoomlaFox! Code Snippet to export Joomla table as .csv, Version 1.0, 2009-12-05
* by Emerson Rocha Luiz - Licenced by Creative Commons By 3.0* http://www.fititnt.org/codigo/joomlafox/export-csv.html */


  
//TODO: make this code a bit more 'Joomla Framework Like'
$app = JFactory::getApplication();

$query = 'SELECT con.id, con.first_name, con.last_name,con.title,con.email, con.primary_address_street,con.phone_work,con.account_name,acc.address_street,acc.address_postalcode,acc.address_city,acc.address_country,acc.account_speciality,acc.cours_list, acc.degrees_list,acc.research_areas_list FROM #__jcrm_contacts as con left join #__jcrm_accounts as acc on con.account_id=acc.id WHERE 1';

$elements = array('id', 'first_name', 'last_name','title','email', 'primary_address_street','phone_work','account_name','address_street','address_postalcode','address_city','address_country', 'account_speciality', 'cours_list', 'degrees_list', 'research_areas_list');

$name = array('id', 'First Name', 'Last Name','Title','email','Address','Phone Number', 'Organisation','Address','Postal Code','City','Country', 'Speciality', 'Cours', 'Degrees', 'Research Areas');

$db	=JFactory::getDBO();
$myquery = $db->setQuery($query);
$fields_cnt = $db->loadRowlist($myquery);
//Output CSV Options
$line_terminated = "\n";
$field_terminated = ",";
$enclosed = '"';
$escaped = '\\';
$export_schema = '';
 foreach($name as $n){
 $export_schema.=strtoupper($n).';';
 } 
$output =$export_schema;
$output .= $line_terminated;

foreach($fields_cnt as $row)
{
$export_schema = '';
for ($j = 0; $j < count($elements); $j++)
{
if ($row[$j] == '0' || $row[$j] != '')
{
$export_schema .= $enclosed.$row[$j].$enclosed.';';
} else
{
$export_schema .= $enclosed.'null'.$enclosed.';';
} 
} 
$output .= $export_schema;
$output .= $line_terminated;
}
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-Length:".strlen($output));
header("Content-type: application/force-download");
header("Content-type: application/csv");
header('Content-Disposition: attachment; filename=jcrm_contacts_'.date("Y.m.d").'.csv');
set_time_limit(480);
echo $output;
exit;
$app->close();
}
?>