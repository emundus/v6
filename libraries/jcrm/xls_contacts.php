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


/************************************************************************************************
 * 
 *  EXPORT RANKING
 *
 ************************************************************************************************/
function selected($users) {
	$current_user = JFactory::getUser();
	$allowed = array("Super Administrator", "Administrator", "Publisher", "Editor", "Author");
	if (!in_array($current_user->usertype, $allowed)) die( JText::_('RESTRICTED_ACCESS') );
	
	@set_time_limit(10800);
	global $mainframe;
	$baseurl = JURI::base();
	$db	= &JFactory::getDBO();
	jimport( 'joomla.user.user' );
	error_reporting(0);
	/** PHPExcel */
	ini_set('include_path', JPATH_BASE . '/libraries/');

	include 'PHPExcel.php'; 
	include 'PHPExcel/Writer/Excel5.php';
	
	$filename = 'jcrm_contacts_'.date('Y-m-d');
	$realpath = EMUNDUS_PATH_REL.'tmp/'.$filename;
	
	// Create new PHPExcel object
	$objPHPExcel = new PHPExcel();
	// Initiate cache
	$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
	$cacheSettings = array( 'memoryCacheSize' => '32MB');
	PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
	// Set properties
	$objPHPExcel->getProperties()->setCreator("Décision Publique : http://www.decisionpublique.fr/");
	$objPHPExcel->getProperties()->setLastModifiedBy("Décision Publique");
	$objPHPExcel->getProperties()->setTitle("eMmundus Report");
	$objPHPExcel->getProperties()->setSubject("eMmundus Report");
	$objPHPExcel->getProperties()->setDescription("Report from open source eMundus plateform : http://www.emundus.fr/");
// PAGE 1 : SELECTED
	$objPHPExcel->setActiveSheetIndex(0);
	$objPHPExcel->getActiveSheet()->setTitle('Contacts');
	$objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
	$objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_JUSTIFY);

	$colonne_by_id = array();
	for ($i=ord("A");$i<=ord("Z");$i++) {
		$colonne_by_id[]=chr($i);
	}
	for ($i=ord("A");$i<=ord("Z");$i++) {
		for ($j=ord("A");$j<=ord("Z");$j++) {
		$colonne_by_id[]=chr($i).chr($j);
		if(count($colonne_by_id) == count($elements_debut)+count($elements)) break;
		}
	}
	
	$elements = array('id', 'last_name','first_name','title','email', 'primary_address_street','phone_work','account_name','address_street','address_postalcode','address_city','address_country', 'account_speciality', 'cours_list', 'degrees_list', 'research_areas_list');
	$name = array('id','Last Name','First Name', 'Title','Email','Address','Phone Number', 'Organisation' ,'Address','Postal Code','City','Country','Speciality', 'Cours', 'Degrees', 'Research Areas');
	$ligne_de_base = 2;
	$i=0;
	foreach ($name as $n) {
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i++, 1, $n);
	}
	//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 1, 'TU SUM');
	$objPHPExcel->getActiveSheet()->getStyle('A1:'.$colonne_by_id[$i].'1')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('A1:'.$colonne_by_id[$i].'1')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
	
	$query = 'SELECT con.id, con.last_name,con.first_name, con.title,con.email, con.primary_address_street,con.phone_work,con.account_name,acc.address_street,acc.address_postalcode,acc.address_city,acc.address_country,acc.account_speciality,acc.cours_list, acc.degrees_list,acc.research_areas_list 
		FROM #__jcrm_contacts as con left join #__jcrm_accounts as acc on con.account_id=acc.id WHERE 1';
	$db->setQuery( $query );
	$profil = $db->loadRowList();
	$selectedLst = $db->loadObjectList('user_id');
	//die(str_replace('#_','jos',$query));
 	for($user=0;$user<count($profil);$user++) {
		$coord = $ligne_de_base+$user;
		 for($colonne=0;$colonne<count($elements);$colonne++) {
				if ($elements[$colonne]=='email')  {
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colonne, $coord, $profil[$user][$colonne]);
				$objPHPExcel->getActiveSheet()->getCell($colonne_by_id[$colonne].$coord)->getHyperlink()->setUrl('mailto:'.$profil[$user][$colonne]);
				$objPHPExcel->getActiveSheet()->getStyle($colonne_by_id[$colonne].$coord)->getFont()->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
			}elseif($elements[$colonne]=='id')  {
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colonne, $coord, $profil[$user][$colonne]);
				$user_id = $profil[$user][$colonne];
				$objPHPExcel->getActiveSheet()->getCell($colonne_by_id[$colonne].$coord)->getHyperlink()->setUrl($baseurl.'/index.php?option=com_emundus&view=application_form&sid='.$profil[$user][$colonne]);
				$objPHPExcel->getActiveSheet()->getStyle($colonne_by_id[$colonne].$coord)->getFont()->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
				$objPHPExcel->getActiveSheet()->getRowDimension($coord)->setRowHeight(15);
			}		 
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colonne, $coord,$profil[$user][$colonne]);	
			$objPHPExcel->getActiveSheet()->getColumnDimension($colonne_by_id[$colonne])->setAutoSize(true);
		} 
	}
	unset($elements);  
	 

	$objPHPExcel->setActiveSheetIndex(0);
	$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel); 
	//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007'); 
	$objWriter->save($realpath); 

//////////////////////////////////////////////
	
	$mtime = ($mtime = filemtime($realpath)) ? $mtime : gmtime();
	$size = intval(sprintf("%u", filesize($realpath)));
	// Maybe the problem is we are running into PHPs own memory limit, so:
	if (intval($size + 1) > return_bytes(ini_get('memory_limit')) && intval($size * 1.5) <= 1073741824) { //Not higher than 1GB
	  ini_set('memory_limit', intval($size * 1.5)); 
	}
	// Maybe the problem is Apache is trying to compress the output, so:
	//@apache_setenv('no-gzip', 1);
	@ini_set('zlib.output_compression', 0);
	// Maybe the client doesn't know what to do with the output so send a bunch of these headers:
	ob_clean();
	header("Content-type: application/force-download");
	header("Content-Type: application/vnd.ms-excel;charset=UTF-8");
	header('Content-Disposition: attachment; filename="'.$filename.'"; modification-date="' . date('r', $mtime) . '";');
	// Set the length so the browser can set the download timers
	header("Content-Length: " . $size);
	// If it's a large file we don't want the script to timeout, so:
	set_time_limit(480);
	// If it's a large file, readfile might not be able to do it in one go, so:
	$chunksize = 1 * (1024 * 1024); // how many bytes per chunk
	if ($size > $chunksize) {
	  $handle = fopen($realpath, 'rb');
	  $buffer = '';
	  while (!feof($handle)) {
		$buffer = fread($handle, $chunksize);
		echo $buffer;
		ob_flush();
		flush();
	  }
	  fclose($handle);
	} else {
	  readfile($realpath);
	}
	
	unlink($realpath);
//$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
// Echo done
	exit;
	
}
?>