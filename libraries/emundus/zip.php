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

function zip_file($cids) {
	require_once(JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
	//clearstatcache();
	$current_user =& JFactory::getUser();
	//$allowed = array("Super Administrator", "Administrator", "Editor", "Author");
	$view = JFactory::getApplication()->input->get( 'view' );
	
	foreach($cids as $cid){
		$params=explode('|',$cid);
		$users[]=$params[0];
	}
	//JFactory::getApplication()->input->get('view', null, 'GET', 'none',0);
	if ((!EmundusHelperAccess::isAdministrator($current_user->id) && !EmundusHelperAccess::isCoordinator($current_user->id) && !EmundusHelperAccess::isPartner($current_user->id) && !EmundusHelperAccess::isEvaluator($current_user->id)) && $view != 'renew_application') 
		die( JText::_('RESTRICTED_ACCESS') );
	
	$form_pdf = "application.pdf";
	$zip = new ZipArchive();
	$db	= &JFactory::getDBO();
	if($view != 'renew_application'){
		$query = 'SELECT c.firstname, c.lastname, c.user_id AS id, d.id AS declared
			FROM #__emundus_users AS c
			LEFT JOIN #__emundus_declaration AS d ON d.user = c.user_id 
			WHERE c.user_id IN ('.implode(',', $users).')';
	}else{
		$query = 'SELECT c.firstname, c.lastname, c.user_id AS id, d.id AS declared, c.schoolyear
			FROM #__emundus_users AS c
			LEFT JOIN #__emundus_declaration AS d ON d.user = c.user_id 
			WHERE c.user_id IN ('.implode(',', $users).')';
	}
	$db->setQuery($query);
	$users = $db->loadObjectList();
	
	($view != 'renew_application')? $nom = rand().'.zip':$nom = $users[0]->schoolyear.'_'.$users[0]->id.'_'.$users[0]->lastname.'_'.$users[0]->firstname.'_'.date("Y-m-d").'_'.rand(1000,9999).'.zip';
	($view != 'renew_application')? $path = EMUNDUS_PATH_ABS.'tmp'.DS.$nom:$path = EMUNDUS_PATH_ABS.'archives'.DS.$nom;
	if(file_exists(EMUNDUS_PATH_ABS.'tmp'.DS.$nom)) unlink(EMUNDUS_PATH_ABS.'tmp'.DS.$nom);
	if($zip->open($path, ZipArchive::CREATE) == TRUE) {
		$todel = array();
		$i=0;
		$error=0;
		$file = '';
		foreach($users as $user) {
			$dossier = JPATH_BASE.DS.'images'.DS.'emundus'.DS.'files'.DS.$user->id.DS;
			$file = $_SERVER['HTTP_HOST'].'_'.date("Y").'_'.$user->id.'_'.$user->lastname.'_'.$user->firstname.'/';
			$app = false;
			if(!is_dir($dossier)) {
				mkdir($dossier, 0777, true);
			}
			$dir = @opendir($dossier);
			
			while (false !== ($f = @readdir($dir))) {    			
				if ($f != "index.html" && substr($f, 0, 3) != "tn_" && $f != '.' && $f != '..' && $f != 'Thumbs.db') {
					if($f==$form_pdf) $app = true;
					if(!$zip->addFile($dossier.$f, $file.$f)) continue;
				}
			}
			closedir($dir);
			
			require_once('pdf.php');
			application_form_pdf($user->id, false);
			if(!$zip->addFile($dossier.$form_pdf, $file.$form_pdf)) continue;
			if(!$user->declared) $todel[] = $dossier.$form_pdf;
			$i++;
			
			if($view == 'renew_application'){
				$query = 'INSERT INTO #__emundus_uploads (user_id,attachment_id,filename,description,can_be_deleted,can_be_viewed) 
					VALUES ('.$user->id.',(
										   SELECT id 
										   FROM #__emundus_setup_attachments 
										   WHERE lbl = "_archive"),"'.$nom.'","'.date('Y-m-d H:i:s').'",0,0)';
				$db->setQuery($query);
				$db->query();
			}
		}
		$zip->close();
		if($view != 'renew_application'){
			//////////////////////////////////////////////
			$realpath = ($view != 'renew_application')?EMUNDUS_PATH_ABS.'tmp'.DS.$nom:EMUNDUS_PATH_ABS.'archives'.DS.$nom;
			$mtime = ($mtime = @filemtime($realpath)) ? $mtime : microtime();
			$size = intval(sprintf("%u", @filesize($realpath)));
			if (!file_exists($realpath) && $view != 'renew_application') {
				die('Too much files have been selected to zip for server capability. Please select less applicants for zip extraction : <a href="'.JURI::base().'images/emundus/files/tmp/'.$nom.'">'.$nom.'<a> ('.$i.' compressed /'.count($users).' selected)<br /> PATH : '.$realpath);
			}
			
			// Maybe the problem is we are running into PHPs own memory limit, so:
			if (intval($size + 1) > return_bytes(ini_get('memory_limit')) && intval($size * 1.5) <= 1073741824) { //Not higher than 1GB
			  ini_set('memory_limit', intval($size * 1.5));
			}
			// Maybe the problem is Apache is trying to compress the output, so:
			@ini_set('zlib.output_compression', 0);
			// Maybe the client doesn't know what to do with the output so send a bunch of these headers:
			header("Content-type: application/force-download");
			header('Content-Type: application/zip');
			
			if($view != 'renew_application')
				header('Content-Disposition: attachment; filename="applicants-x'.$i.'_'.$_SERVER['HTTP_HOST'].'_'.date("Y.m.d").'.zip"; modification-date="' . date('r', $mtime) . '";');
			else
				header('Content-Disposition: attachment; filename="'.$nom.'"; modification-date="' . date('r', $mtime) . '";');
			// Set the length so the browser can set the download timers
			header("Content-Length: " . $size);
			// If it's a large file we don't want the script to timeout, so:
			@set_time_limit(300);
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
			
			unlink(EMUNDUS_PATH_ABS.'tmp'.DS.$nom);
			foreach($todel as $file) unlink($file);
			
			// Exit successfully. We could just let the script exit
			// normally at the bottom of the page, but then blank lines
			// after the close of the script code would potentially cause
			// problems after the file download.
			exit;
		}
	}
}
?>