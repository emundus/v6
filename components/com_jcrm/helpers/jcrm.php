<?php

/**
 * @version     1.0.0
 * @package     com_jcrm
 * @copyright   Copyright (C) 2014. Tous droits réservés.
 * @license     GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 * @author      Décision Publique <dev@emundus.fr> - http://www.emundus.fr
 */
defined('_JEXEC') or die;

/**
 * Class JcrmFrontendHelper
 */
class JcrmFrontendHelper
{
	/**
	 * @param $contact
	 * @return array
	 */
	public static function buildJCard($contact) {
		if (!isset($contact->img)) {
			$photo = array("mediatype"=>"image/svg", "uri"=>"/media/com_jcrm/images/contacts/user.svg");
			if ($contact->type == 1) {
				$photo = array("mediatype"=>"image/svg", "uri"=>"/media/com_jcrm/images/contacts/org.svg");
			}
		} else {
			$ext = explode('.', $contact->img);
			$photo = array("mediatype"=>"image/".$ext[count($ext) - 1], "uri"=>$contact->img);
		}
		if ($contact->type == 0) {
			if (isset($contact->first_name)) {
				$fn = $contact->last_name." ".$contact->first_name;
			} else {
				$fn = $contact->last_name;
			}
		} elseif (isset($contact->organisation)) {
			$fn = $contact->organisation;
		}
		$jcard = array(
			'version' => "4.0",
			"n" => array($contact->last_name, (isset($contact->first_name)?$contact->first_name:""), "", "", ""),
			"fn" => $fn,
			"org" => (isset($contact->organisation))?$contact->organisation:"",
			"title" => isset($contact->title)?$contact->title:"",
			"photo" => $photo,
			'infos' => $contact->infos
		);

		if (!empty($contact->email[0]->uri)) {
			$jcard['email'] = $contact->email;
		}

		if (!empty($contact->phone[0]->tel)) {
			$jcard['phone'] = $contact->phone;
		}

		if (!empty($contact->adr[0]->array)) {
			$jcard['adr'] = $contact->adr;
		}
		if (!empty($contact->other)) {
			$jcard['other'] = $contact->other;
		}
		return $jcard;
	}

	public static function extractFromJcard($contact) {
		$jcard = json_decode($contact['jcard']);
		$contact['photo'] = $jcard->photo;
		if ($contact['type'] == "0") {
			$contact['type'] = false;
		} else {
			$contact['type'] = true;
		}
		if (isset($jcard->email)) {
			$contact['email'] = $jcard->email;
		} else {
			$email = new stdClass();
			$email->type="work";
			$email->uri = "";
			$contact['email'] = array($email);
		}

		if (isset($jcard->other)) {
			$contact['other'] = $jcard->other;
		} else {
			$contact['other'] = array();
		}

		if (isset($jcard->infos)) {
			$contact['infos'] =$jcard->infos;
		} else {
			$contact['infos'] ="";
		}


		if (isset($jcard->phone)) {
			$contact['phone'] = $jcard->phone;
		} else {
			$phone = new stdClass();
			$phone->type="work";
			$phone->tel = "";
			$contact['phone'] = array($phone);
		}

		if (isset($jcard->adr)) {
			$contact['adr'] = $jcard->adr;
		} else {
			$adr = new stdClass();
			$adr->type="work";
			$adr->array = array();
			$contact['adr'] = array($adr);
		}
		return $contact;
	}

	public static function buildCSV($contacts) {
		$crypt = mcrypt_create_iv(16);
		$rand = md5(JUser::getInstance()->id.$crypt.time());
		$path = JPATH_BASE.DS.'tmp';
		$fileName = $rand.'c'.time().'-contacts.csv';
		$path .= DS.$fileName;

		$file = fopen($path, 'w');

		$fileLine = array('last_name' /*0*/,
		                  'first_name' /*1*/,
		                  'full_name'/*2*/,
		                  'organisation'/*3*/,
		                  'email1'/*4*/,
		                  'email2'/*5*/,
		                  'email3'/*6*/,
		                  'email4' /*7*/,
		                  'phone1'/*8*/,
		                  'phone2'/*9*/,
		                  'phone3'/*10*/,
		                  'phone4'/*11*/,
		                  'home street'/*12*/,
		                  'home zipcode'/*13*/,
		                  'home city'/*14*/,
		                  'home country'/*15*/,
		                  'work street'/*16*/,
		                  'work zipcode'/*17*/,
		                  'work city'/*18*/,
		                  'work country'/*19*/,
		                  'infos'/*20*/,
		                  'birthday'/*21*/,
		                  'categories'/*23*/,
		                  'geo'/*25*/,
		                  'mailer'/*28*/,
		                  'nickname'/*29*/,
		                  'role'/*30*/,
		                  'source'/*31*/,
		                  'title'/*32*/,
		                  'time zone'/*33*/,
		                  'url'/*34*/);
		$firstLine = array('last_name' /*0*/,
		                  'first_name' /*1*/,
		                  'full_name'/*2*/,
		                  'organisation'/*3*/,
		                  'email1'/*4*/,
		                  'email2'/*5*/,
		                  'email3'/*6*/,
		                  'email4' /*7*/,
		                  'phone1'/*8*/,
		                  'phone2'/*9*/,
		                  'phone3'/*10*/,
		                  'phone4'/*11*/,
		                  'home street'/*12*/,
		                  'home zipcode'/*13*/,
		                  'home city'/*14*/,
		                  'home country'/*15*/,
		                  'work street'/*16*/,
		                  'work zipcode'/*17*/,
		                  'work city'/*18*/,
		                  'work country'/*19*/,
		                  'infos'/*20*/,
		                  'bday'/*21*/,
		                  'categories'/*22*/,
		                  'geo'/*23*/,
		                  'mailer'/*24*/,
		                  'nickname'/*25*/,
		                  'role'/*26*/,
		                  'source'/*27*/,
		                  'title'/*28*/,
		                  'tz'/*29*/,
		                  'url'/*30*/);
		fputcsv($file, $fileLine);
		foreach ($contacts as $contact) {
			$jcard = json_decode($contact['jcard']);
			$fileLine = array(''/*0*/,
			                  ''/*1*/,
			                  ''/*2*/,
			                  ''/*3*/,
			                  ''/*4*/,
			                  ''/*5*/,
			                  ''/*6*/,
			                  ''/*7*/,
			                  ''/*8*/,
			                  ''/*9*/,
			                  ''/*10*/,
			                  ''/*11*/,
			                  ''/*12*/,
			                  ''/*13*/,
			                  ''/*14*/,
			                  ''/*15*/,
			                  ''/*16*/,
			                  ''/*17*/,
			                  ''/*18*/,
			                  ''/*19*/,
			                  ''/*20*/,
			                  ''/*21*/,
			                  ''/*22*/,
			                  ''/*23*/,
			                  ''/*24*/,
			                  ''/*25*/,
			                  ''/*26*/,
			                  ''/*27*/,
			                  ''/*28*/,
			                  ''/*29*/,
			                  ''/*30*/);

			$fileLine[0] = $contact['last_name'];
			$fileLine[1] = $contact['first_name'];
			$fileLine[2] = $contact['full_name'];
			$fileLine[3] = $contact['organisation'];
			if (isset($jcard->email)) {
				foreach ($jcard->email as $k => $mail) {
					$fileLine[4 + $k] = $mail->uri;
					if ($k == 3) {
						break;
					}
				}
			}
			if (isset($jcard->phone)) {
				foreach ($jcard->phone as $k => $phone) {
					$fileLine[8 + $k] = $phone->tel;
					if ($k == 3) {
						break;
					}
				}
			}

			if (isset($jcard->adr)) {
				foreach ($jcard->adr as $k => $phone) {
					if ($phone->type == "work") {
						$fileLine[16] = $phone->array[0];
						$fileLine[17] = $phone->array[1];
						$fileLine[18] = $phone->array[2];
						$fileLine[19] = $phone->array[3];
					} else {
						$fileLine[12] = $phone->array[0];
						$fileLine[13] = $phone->array[1];
						$fileLine[14] = $phone->array[2];
						$fileLine[15] = $phone->array[3];
					}
				}
			}
			if (isset($jcard->infos)) {
				$fileLine[20] = $jcard->infos;
			}

			if (isset($jcard->other) && !empty($jcard->other)) {
				foreach ($jcard->other as $other) {
					$index = array_search($other->type, $firstLine);
					$fileLine[$index] = $other->value;
				}
			}

			fputcsv($file, $fileLine);
		}

		fclose($file);
		return $fileName;

	}

	public static function buildVcard($contacts) {
		$crypt = mcrypt_create_iv(16);
		$rand = md5(JUser::getInstance()->id.$crypt.time());
		$path = JPATH_BASE.DS.'tmp';
		$fileName = $rand.'c'.time().'-contacts.vcf';
		$path .= DS.$fileName;
		$file = fopen($path, 'w');

		$vcard = "";
		foreach ($contacts as $contact) {
			$jcard = json_decode($contact['jcard']);

			$vcard .= "BEGIN:VCARD\r\nVERSION:3.0\r\n";
			$vcard .= "N:".$contact['last_name'].";".$contact['first_name'].";\r\n";
			$vcard .= "FN:".$contact['full_name']."\r\n";
			$vcard .= "ORG:".$contact['organisation']."\r\n";
			if (isset($jcard->phone) && !empty($jcard->phone)) {
				foreach ($jcard->phone as $phone) {
					$vcard .= "TEL;TYPE=".strtoupper($phone->type).":".$phone->tel."\r\n";
				}
			}

			if (isset($jcard->email) && !empty($jcard->email)) {
				foreach ($jcard->email as $email) {
					$vcard .= "EMAIL;TYPE=".strtoupper($email->type).";TYPE=INTERNET:".$email->uri."\r\n";
				}
			}
			if (isset($jcard->adr) && !empty($jcard->adr)) {
				foreach ($jcard->adr as $adr) {
					$vcard .= "ADR;TYPE=".strtoupper($adr->type).":;;".$adr->array[0].';'.$adr->array[2].';;'.$adr->array[1].';'.$adr->array[3].";\r\n";
				}
			}

			if (isset($jcard->infos) && !empty($jcard->infos)) {
				$vcard .= "NOTE:".addcslashes($jcard->infos, '\\:;')."\r\n";
			}

			if (isset($jcard->other) && !empty($jcard->other)) {
				foreach ($jcard->other as $other) {
					if ($other->type == 'geo') {
						$vcard .= "GEO:".$other->value."\r\n";
					} elseif ($other->type == 'bday') {
						$vcard .= "BDAY:".date('Y-m-d', strtotime($other->value))."\r\n";
					} else {
						$vcard .= strtoupper($other->type).":".$other->value."\r\n";
					}
				}
			}

			$vcard .= "REV:".date(DATE_ISO8601)."\r\n";
			$vcard .= "END:VCARD\r\n";
		}
		fwrite($file, $vcard);
		fclose($file);
		return $fileName;
	}


	/**
	 * @param $referent
	 * @param $index
	 *
	 * @return stdClass
	 */
	public static function buildContactFromReferent($referent, $index) {

		$newContact = new stdClass();
		$newContact->last_name = $referent['Last_Name_'.$index];
		$newContact->first_name = $referent['First_Name_'.$index];
		$newContact->organisation = $referent['Organisation_'.$index];
		$newContact->type = 0;
		
		if (!empty($referent['Group_'.$index])) {
			$newContact->formGroup = $referent['Group_'.$index];
		}

		foreach ($referent as $item => $value) {

			// Skip values used above or used in tandem with the address field (like City).
			if (in_array($item, ['City_'.$index, 'Country_'.$index, 'Last_Name_'.$index , 'First_Name_'.$index, 'Organisation_'.$index, 'Group_'.$index]) || empty($value)) {
				continue;
			}

			$newContact->email = array();
			if ($item === 'Email_'.$index) {
				$email = new stdClass();
				$email->type = 'work';
				$email->uri = $referent['Email_'.$index];
				$newContact->email[] = $email;
				continue;
			}

			$newContact->phone = array();
			if ($item === 'Telephone_'.$index) {
				$phone = new stdClass();
				$phone->type = 'work';
				$phone->tel = $value;
				$newContact->phone[] = $phone;
				continue;
			}
			if ($item === 'Fax_number'.$index) {
				$phone = new stdClass();
				$phone->type = 'fax';
				$phone->tel = $value;
				$newContact->phone[] = $phone;
				continue;
			}

			$newContact->adr = array();
			if ($item === 'Address_'.$index) {
				$adr = new stdClass();
				$adr->type = 'work';
				$adr->array = array($value, $referent['City_'.$index], '', $referent['Country_'.$index]);
				$newContact->adr[] = $adr;
				continue;
			}

			$newContact->other = array();
			if ($item === 'Position_'.$index) {
				$other = new stdClass();
				$other->type = 'title';
				$other->value = $value;
				$newContact->other[] = $other;
				continue;
			}

			$newContact->infos = "";
			if ($item === 'Website_'.$index) {
				$newContact->infos .= "website: ". $value. "\n";
				continue;
			}

			// This tricky if only gets fields that END in the index (for example ExtraInfo_1 would work but not SpecialField)
			if (substr($item, -strlen($index)) === $index) {
				$other = new StdClass();
				$other->type = 'title';
				$other->value = $value;
				$newContact->other[] = $other;
			}
		}
		return $newContact;
	}


	public static function buildOrgaFromReferent($referent, $index) {
		$newOrga = new stdClass();
		$newOrga->last_name = "";
		$newOrga->first_name = "";
		$newOrga->organisation = $referent['Organisation_'.$index];
		$newOrga->type = 1;
		$newOrga->email = array();
		$newOrga->phone = array();
		$newOrga->adr = array();
		$newOrga->other = array();
		$newOrga->infos = "";
		return $newOrga;
	}
}
