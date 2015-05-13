<?php 
defined( '_JEXEC' ) or die( 'Restricted access' );


function selected($id_cont){

include_once('vCard.php');
$vCard=new vCard('','');
$db	= &JFactory::getDBO();
$query = 'SELECT con.first_name, con.last_name,con.title,con.email, con.primary_address_street,con.primary_address_postalcode,con.primary_address_city,con.phone_work,con.phone_fax,con.website,con.account_name,acc.address_street,acc.address_postalcode,acc.address_city,acc.address_country,con.primary_address_state, acc.address_state, con.department, cou.name_en FROM #__jcrm_contacts as con left join #__jcrm_accounts as acc on con.account_id=acc.id LEFT JOIN #__emundus_country as cou on cou.iso2=con.country_code WHERE con.id='.$id_cont;
$db->setQuery( $query );
$profil = $db->loadRowList();
foreach($profil as $card){
		
$vCard->setFirstName("$card[0]");
$vCard->setMiddleName('');
$vCard->setLastName("$card[1]");
$vCard->setEducationTitle("$card[2]");
$vCard->setAddon('');
$vCard->setNickname('');
$vCard->setCompany("$card[10]");
$vCard->setOrganisation("$card[10]");
$vCard->setDepartment("$card[17]");
$vCard->setJobTitle("$card[2]");
$vCard->setNote('Additional Note go here');
$vCard->setTelephoneWork1("$card[7]");
$vCard->setTelephoneWork2("");
$vCard->setTelephoneHome1("");
$vCard->setTelephoneHome2("");
$vCard->setCellphone("");
$vCard->setCarphone("");
$vCard->setPager('');
$vCard->setAdditionalTelephone('');
$vCard->setFaxWork("$card[8]");
$vCard->setFaxHome("");
$vCard->setISDN('');
$vCard->setPreferredTelephone("$card[7]");
$vCard->setTelex("");
$vCard->setWorkStreet("$card[11]");
$vCard->setWorkZIP("$card[12]");
$vCard->setWorkCity("$card[13]");
$vCard->setWorkRegion("$card[16]");
$vCard->setWorkCountry("$card[14]");
$vCard->setHomeStreet("$card[4]");
$vCard->setHomeZIP("$card[5]");
$vCard->setHomeCity("$card[6]");
$vCard->setHomeRegion("$card[15]");
$vCard->setHomeCountry("$card[18]");
$vCard->setPostalStreet("$card[4]");
$vCard->setPostalZIP("$card[5]");
$vCard->setPostalCity("$card[6]");
$vCard->setPostalRegion("$card[15]");
$vCard->setPostalCountry('');
$vCard->setURLWork("$card[9]");
$vCard->setRole('');
$vCard->setBirthday(time());
$vCard->setEMail("$card[3]"); 
ob_clean();
$card[0]=str_replace(' ','-',$card[0]);
$card[1]=str_replace(' ','-',$card[1]);
header('Content-Type: text/x-vcard');
header('Content-Disposition: attachment; filename='.$id_cont.'_' . $card[0].'_'.$card[1] . '.vcf');
echo $vCard->getCardOutput(); 
} 
exit; 

}
?>