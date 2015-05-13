<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
function selected($users) {

$app = JFactory::getApplication();

$query = 'SELECT con.id, con.first_name, con.last_name,con.title,con.email, con.primary_address_street,con.phone_work,con.account_name,acc.address_street,acc.address_postalcode,acc.address_city,acc.address_country ,acc.account_speciality,acc.cours_list, acc.degrees_list,acc.research_areas_list FROM #__jcrm_contacts as con left join #__jcrm_accounts as acc on con.account_id=acc.id WHERE 1';

$elements = array('id', 'first_name', 'last_name','title','email', 'primary_address_street','phone_work','account_name','address_street','address_postalcode','address_city','address_country', 'account_speciality', 'cours_list', 'degrees_list', 'research_areas_list');

$name = array('id', 'First Name', 'Last Name','Title','email','Address','Phone Number', 'Organisation','Address','Postal Code','City','Country', 'Speciality', 'Cours', 'Degrees', 'Research Areas');
$db	= &JFactory::getDBO();
$myquery = $db->setQuery($query);
$fields_cnt = $db->loadRowlist($myquery);
$export_schema = '';
$line_terminated = "\n";
 foreach($name as $n){
 $export_schema.=strtoupper($n).'  ';
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
$export_schema .= $row[$j].'  ';
} else
{
$export_schema .= 'null  ';
} 
} 
$output .= $export_schema;
$output .= $line_terminated;
}
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-Length:".strlen($output));
header("Content-type: application/force-download");
header("Content-type: application/txt");
header('Content-Disposition: attachment; filename=jcrm_contacts_'.date("Y.m.d").'.txt');
set_time_limit(480);
echo $output;
exit;
$app->close();

}
?>