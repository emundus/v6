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

	function export_xls($uids, $element_id) {
			$current_user = JFactory::getUser();
			//$allowed = array("Super Administrator", "Administrator", "Publisher", "Editor", "Author");
			if(!EmundusHelperAccess::isAdministrator($current_user->id) && !EmundusHelperAccess::isCoordinator($current_user->id) && !EmundusHelperAccess::isEvaluator($current_user->id) && !EmundusHelperAccess::isPartner($current_user->id)) die( JText::_('RESTRICTED_ACCESS') );

			@set_time_limit(10800);
			global $mainframe;
			$baseurl = JURI::base();
			$db	= &JFactory::getDBO();
			jimport( 'joomla.user.user' );
			error_reporting(0);
			/** PHPExcel */
			ini_set('include_path', JPATH_BASE.DS.'libraries'.DS);

			include 'PHPExcel.php'; 
			include 'PHPExcel/Writer/Excel5.php'; 
			
			$filename = 'emundus_applicants_'.date('Y.m.d').'.xls';
			$realpath = EMUNDUS_PATH_REL.'tmp'.DS.$filename;
			
			$query = 'SELECT params FROM #__fabrik_elements WHERE name like "final_grade" LIMIT 1';
			$db->setQuery( $query );
			//die(str_replace('#_','jos',$query));
			$params = $db->loadResult();
			$params=json_decode($params);
			$sub_options=$params->sub_options;
			$sub_values=$sub_options->sub_values;
		
			foreach($sub_values as $sv)
				$patterns[]="/".$sv."/";
			
			// Create new PHPExcel object
			$objPHPExcel = new PHPExcel();
			// Initiate cache
			$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
			$cacheSettings = array( 'memoryCacheSize' => '32MB');
			PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
			// Set properties
			$objPHPExcel->getProperties()->setCreator("Décision Publique : http://www.decisionpublique.fr/");
			$objPHPExcel->getProperties()->setLastModifiedBy("Décision Publique");
			$objPHPExcel->getProperties()->setTitle("eMmundus® Report");
			$objPHPExcel->getProperties()->setSubject("eMmundus® Report");
			$objPHPExcel->getProperties()->setDescription("Report from open source eMundus® plateform : http://www.emundus.fr/");
	
			
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()->setTitle('Administrative validation');
			$objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php');
			
			$mod = new EmundusModelUsers;
			$model = $mod->_buildQuery();
			$db->setQuery( $model );
			$users = $db->loadObjectList();
			
			$profile = $mod->getProfiles();
			
			$groupEval = $mod->getGroupEval();
			
			$campaigns = $mod->getCampaigns();
			
			$university = $mod->getUniversities();
			
			$schoolyears = $mod->getSchoolyears();
			
			/// ****************************** ///
			// Elements selected by administrator
			/// ****************************** ///
			$query = 'SELECT distinct(concat_ws("_",tab.db_table_name,element.name)), element.name AS element_name, element.label AS element_label, INSTR(groupe.params,"repeat_group_button=1") AS group_repeated, tab.db_table_name AS table_name
						FROM #__fabrik_elements element	
						INNER JOIN #__fabrik_groups AS groupe ON element.group_id = groupe.id
						INNER JOIN #__fabrik_formgroup AS formgroup ON groupe.id = formgroup.group_id
						INNER JOIN #__fabrik_lists AS tab ON tab.form_id = formgroup.form_id
						INNER JOIN #__menu AS menu ON tab.form_id = SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("formid=",menu.link)+7, 3), "&", 1)
						WHERE tab.published = 1 
						AND (tab.created_by_alias = "form" OR tab.created_by_alias = "comment")
						AND element.published=1 
						AND element.hidden=0 
						AND element.label!=" " 
						AND element.label!="" 
						AND element.id IN ("'.implode('","', $element_id).'") 
						ORDER BY menu.ordering, formgroup.ordering, groupe.id, element.ordering'; 
			$db->setQuery( $query );
			//die(str_replace("#_","jos",$query));
			$elements = $db->loadObjectList();		
			
			// @TODO : générer une chaine de caractère avec tous les user_id
			
			// Starting a session.
			$session =& JFactory::getSession();
			if($uids != ''){
				foreach($users as $key=>$value){
					if(in_array($value->id,$uids)){
						$us[] = $users[$key];
					}
				}
				$user_id = $uids;
				$users = $us;
				$session->clear( 'uid' );
			}else{
				foreach($users as $user){
					$user_id[] = $user->id;
				}
			}
			$session->clear( 'profile' );
			$session->clear( 'quick_search' );
			unset($us);
			
			$select = '';
			$table = '';
			$joined_table = array();
			foreach($elements as $element) {
				if(!array_key_exists($element->element_name, $users[0])) {

					if($element->table_name == 'jos_emundus_comments')
						$select_comment .= '`'.$element->table_name.'`.`'.$element->element_name.'`,';
					else
						if ($element->group_repeated==0 && $element->group_repeated_1==0) 
							$select .= '`'.$element->table_name.'`.`'.$element->element_name.'`,';
					
					if($table != $element->table_name && !in_array($element->table_name, $joined_table)){
						if($element->table_name == 'jos_emundus_comments') 
							$join_comment .= ' LEFT JOIN `'.$element->table_name.'` ON `'.$element->table_name.'`.`applicant_id`=`#__users`.`id`';
						else 
							if ($element->group_repeated==0 && $element->group_repeated_1==0) 
								$join .= ' LEFT JOIN `'.$element->table_name.'` ON `'.$element->table_name.'`.`user`=`#__users`.`id`';
					}
				}
				$joined_table[] = $element->table_name;
				$table = $element->table_name;
			}
			$query = 'SELECT ';
			$query .= $select;
			$query .= ' `#__users`.`id` AS user
						FROM `#__users` 
						LEFT JOIN `#__emundus_users` ON `#__emundus_users`.`user_id`=`#__users`.`id`';
			$query .= $join;
			$query .= 'WHERE `#__users`.`usertype`="Registered" and `#__users`.`id` IN ('.implode(',', $user_id).') 
						ORDER BY `#__emundus_users`.`user_id`,`#__emundus_users`.`lastname`,`#__emundus_users`.`firstname`';
			//die(str_replace('#_','jos',$query));
			$db->setQuery( $query );
			$valeurs = $db->loadObjectList('user');			
			
			$query='';
			$query = 'SELECT ';
			$query .= $select_comment;
			$query .= ' `#__users`.`id` AS user
						FROM `#__users` 
						LEFT JOIN `#__emundus_users` ON `#__emundus_users`.`user_id`=`#__users`.`id`';
			$query .= $join_comment;
			$query .= 'WHERE `#__users`.`usertype`="Registered" and `#__users`.`id` IN ('.implode(',', $user_id).') 
						ORDER BY `#__emundus_users`.`user_id`,`#__emundus_users`.`lastname`,`#__emundus_users`.`firstname`';
			$db->setQuery( $query );
			$comments = $db->loadObjectList();
			
			$colonne_by_id = array();
			for ($i=ord("A");$i<=ord("Z");$i++) {
				$colonne_by_id[]=chr($i);
			}
			for ($i=ord("A");$i<=ord("Z");$i++) {
				for ($j=ord("A");$j<=ord("Z");$j++) {
					$colonne_by_id[]=chr($i).chr($j);
					if(count($colonne_by_id) == count($users)) break;
				}
			}
			
		// ********************************************
		//				En-tete de colonnes
		// ********************************************
		
			$colonne=0;
			foreach($users[0] as $key=>$value){
				if($key!='nationality' && $key!='name' && $key!='username' && $key!='gender' && $key!='registred_for'  && $key != 'block' && $key != 'usertype'){
					$column_title=JText::_(strtoupper($key));
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colonne, 1, $column_title);
					$colonne++;
				}
			}
			
			// add group eval column
			$column_title=JText::_('GROUP_EVAL');
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colonne, 1, $column_title);
			$colonne++;
			
			// add campaign column
			$column_title=JText::_('CAMPAIGNS');
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colonne, 1, $column_title);
			$colonne++;
			
			$tab_com = '';
			$count = 0;
			foreach($elements as $element) {
				//Only one header of comment
				if($element->table_name == 'jos_emundus_comments'){ 
					if($tab_com != $element->table_name){
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colonne, 1, 'Comments');
						$colonne++;
						$tab_com = $element->table_name;
					}
					$count++;
				}elseif(!array_key_exists($element->element_name,$users[0]))	{
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colonne, 1, $element->element_label);
					$colonne++;
				}
			}
			$objPHPExcel->getActiveSheet()->freezePane('B2');
			$objPHPExcel->getActiveSheet()->getStyle('A1:'.$colonne_by_id[$colonne].'1')->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()->getStyle('A3:'.$colonne_by_id[$colonne].'2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
			$i=2;
			
			// ********************************************
			//		Colonnes correspondants au model
			// ********************************************
			
			// If export selected users
			// die(var_dump($users));
			foreach ($users as $user){
					$colonne = 0;
					foreach($user as $key=>$value) {
						if(is_array($user)){
							if($key == 'id'){
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colonne,$i,$value);
								$objPHPExcel->getActiveSheet()->getColumnDimension($colonne_by_id[$colonne])->setAutoSize(true);
								$objPHPExcel->getActiveSheet()->getCell($colonne_by_id[$colonne].$i)->getHyperlink()->setUrl($baseurl.'/index.php?option=com_emundus&view=application_form&sid='.$value);
								$objPHPExcel->getActiveSheet()->getStyle($colonne_by_id[$colonne].$i)->getFont()->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
								$colonne++;
							}elseif($key == 'profile'){
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colonne,$i,$profile[$user->profile]->label);
								$objPHPExcel->getActiveSheet()->getColumnDimension($colonne_by_id[$colonne])->setAutoSize(true);
								$colonne++;
							}elseif ($key == 'email'){
								$objPHPExcel->getActiveSheet()->getCell($colonne_by_id[$colonne].$i)->getHyperlink()->setUrl('mailto:'.$value);
								$objPHPExcel->getActiveSheet()->getStyle($colonne_by_id[$colonne].$i)->getFont()->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colonne,$i,$value);
								$objPHPExcel->getActiveSheet()->getColumnDimension($colonne_by_id[$colonne])->setAutoSize(true);
								$colonne++;
							}elseif($key== 'newsletter'){
								if($value=='"1"'){
									$newsletter=JText::_('JYES');
								}else{
									$newsletter=JText::_('JNO');
								}
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colonne,$i,$newsletter);
								$objPHPExcel->getActiveSheet()->getColumnDimension($colonne_by_id[$colonne])->setAutoSize(true);
								$colonne++;
								
							}elseif($key=='university_id'){
								if($value!='0'){
									$university_label = $university[$value]->institution_name;
								}else{
									$university_label = '';
								}
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colonne,$i,$university_label);
								$objPHPExcel->getActiveSheet()->getColumnDimension($colonne_by_id[$colonne])->setAutoSize(true);
								$colonne++;
							}elseif($key!='nationality' && $key!='gender' && $key!='registred_for'  && $key != 'block' && $key != 'usertype'){
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colonne,$i,$value);
								$objPHPExcel->getActiveSheet()->getColumnDimension($colonne_by_id[$colonne])->setAutoSize(true);
								$colonne++;
							}
						}else{
							if($key == 'avatar') {
								$colonne_photo = $colonne;
								$photo = isset($value)?$value:'';
								if(empty($photo) or !file_exists(EMUNDUS_PATH_ABS.$user->user.DS.'tn_'.$photo)){
									$colonne++;
									continue;
								}
								$objDrawing[$user->user] = new PHPExcel_Worksheet_Drawing();
								$objDrawing[$user->user]->setWorksheet($objPHPExcel->getActiveSheet());
								$objDrawing[$user->user]->setName("Photo");
								$objDrawing[$user->user]->setDescription("Photo");
								$objDrawing[$user->user]->setPath(EMUNDUS_PATH_ABS.$user->user.DS.'tn_'.$photo);
								$objDrawing[$user->user]->setWidth(60);
								$objDrawing[$user->user]->setCoordinates($colonne_by_id[$colonne].$i);
								$objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight($objDrawing[$user->user]->getHeight());
								$colonne++;
							}elseif($key == 'id'){
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colonne,$i,$value);
								$objPHPExcel->getActiveSheet()->getColumnDimension($colonne_by_id[$colonne])->setAutoSize(true);
								$objPHPExcel->getActiveSheet()->getCell($colonne_by_id[$colonne].$i)->getHyperlink()->setUrl($baseurl.'/index.php?option=com_emundus&view=application_form&sid='.$value);
								$objPHPExcel->getActiveSheet()->getStyle($colonne_by_id[$colonne].$i)->getFont()->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
								$colonne++;
							}elseif($key == 'profile'){
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colonne,$i,$profile[$user->profile]->label);
								$objPHPExcel->getActiveSheet()->getColumnDimension($colonne_by_id[$colonne])->setAutoSize(true);
								$colonne++;
							}elseif ($key == 'email')  {
								$objPHPExcel->getActiveSheet()->getCell($colonne_by_id[$colonne].$i)->getHyperlink()->setUrl('mailto:'.$value);
								$objPHPExcel->getActiveSheet()->getStyle($colonne_by_id[$colonne].$i)->getFont()->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colonne,$i,$value);
								$objPHPExcel->getActiveSheet()->getColumnDimension($colonne_by_id[$colonne])->setAutoSize(true);
								$colonne++;
							}elseif($key== 'newsletter'){
								if($value=='"1"'){
									$newsletter=JText::_('JYES');
								}else{
									$newsletter=JText::_('JNO');
								}
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colonne,$i,$newsletter);
								$objPHPExcel->getActiveSheet()->getColumnDimension($colonne_by_id[$colonne])->setAutoSize(true);
								$colonne++;
							}elseif($key=='university_id'){
								if($value!='0'){
									$university_label = $university[$value]->title;
								}else{
									$university_label = '';
								}
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colonne,$i,$university_label);
								$objPHPExcel->getActiveSheet()->getColumnDimension($colonne_by_id[$colonne])->setAutoSize(true);
								$colonne++;
							}elseif($key!='nationality' && $key!='name' && $key!='username' && $key!='username' && $key!='gender' && $key!='registred_for'  && $key != 'block' && $key != 'usertype'){
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colonne,$i,$value);
								$objPHPExcel->getActiveSheet()->getColumnDimension($colonne_by_id[$colonne])->setAutoSize(true);
								$colonne++;
							}
						}
					}
					
				
					$group_eval = $groupEval[$user->id];
					// die(var_dump($group_eval->label));
					if($group_eval){
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colonne, $i, $group_eval->label);
						$objPHPExcel->getActiveSheet()->getColumnDimension($colonne_by_id[$colonne])->setAutoSize(true);
						$colonne++;
					}else{
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colonne, $i, null);
						$objPHPExcel->getActiveSheet()->getColumnDimension($colonne_by_id[$colonne])->setAutoSize(true);
						$colonne++;
					}
					
					$campaign_list="";
					foreach($campaigns as $campaign){
						if($campaign->applicant_id == $user->id){
							$lfcr = chr(10);
							$campaign_list .= JText::_('CAMPAIGN').' : '.$campaign->label.' - '.JText::_('SCHOOLYEARS').' : '.$campaign->year.' '.$lfcr.' ';
						}
					}
					
					if(!empty($campaign_list)){
						// $objPHPExcel->getActiveSheet()->getStyle($colonne.$i)->getAlignment()->setWrapText(true);
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colonne, $i, $campaign_list);
						$objPHPExcel->getActiveSheet()->getColumnDimension($colonne_by_id[$colonne])->setAutoSize(true);
						$cell='"'.$colonne_by_id[$colonne].$i.'"';
						$colonne++;
					}else{
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colonne, $i, null);
						$objPHPExcel->getActiveSheet()->getColumnDimension($colonne_by_id[$colonne])->setAutoSize(true);
						$colonne++;
					}
				
				// ********************************************
				//				Application form
				// ********************************************
	
					
				$tab_com = '';
				$c = 0;
				foreach($elements as $element){
					if(!array_key_exists($element->element_name,$users[0]))	{
						$el = $element->element_name;
						if ($element->group_repeated>0 || $element->group_repeated_1>0) {
							//$value = str_replace("//..*..//", "\n ----- \n", $valeurs[$user_id]->$el);
							$query = 'SELECT `'.$element->table_name.'_'.$element->group_id.'_repeat`.`'.$element->element_name.'` 
										FROM `'.$element->table_name.'` 
										LEFT JOIN `'.$element->table_name.'_'.$element->group_id.'_repeat` ON `'.$element->table_name.'_'.$element->group_id.'_repeat`.`parent_id` = '.$element->table_name.'.id 
										WHERE '.$element->table_name.'.user = '.$user_id;
							$db->setQuery( $query );
							$value = implode(' |\n ', $db->loadColumn()); 
						}
						else {
							if($element->table_name != 'jos_emundus_comments')
								$value = $valeurs[$user_id]->$el; 
						}
						if($element->element_label == "Telephone" || $element->element_label == "Zip code" || $element->element_label == "Fax number") 
							$value =" ".$value;
						//have comment, date and reason in the same square (many rows of comments)
						if($element->table_name == 'jos_emundus_comments'){
							$c++;
							foreach($comments as $comment){
								if($comment->user == $user->user) {
									if($element->element_name == 'user_id'){
										//die(print_r($comment->$el));
										$query = 'SELECT name FROM #__users WHERE id ='.$comment->$el;
										$db->setQuery( $query );
										$tab_value[] = $db->loadResult();
									}else{
										$tab_value[] = $comment->$el;
									}
								}
							}
						
							if($c == $count){
								//have comment, date and reason in the same case (many rows of comments)
								$nb_com = count($tab_value)/$count;
								for($j = 0; $j < $nb_com; $j++){
									$value .= $tab_value[$j];
									if($count ==1 && !empty($value)) $value .= "\n";
									if($count ==2 && !empty($value)) $value .= ' || '.$tab_value[$j+$nb_com]."\n";
									if($count ==3 && !empty($value)) $value .= ' || '.$tab_value[$j+$nb_com].' || '.$tab_value[$j+($nb_com*2)]."\n";
									if($count ==4 && !empty($value)) $value .= ' || '.$tab_value[$j+$nb_com].' || '.$tab_value[$j+($nb_com*2)].' || '.$tab_value[$j+($nb_com*3)]."\n";
								}
							}
						}
						if($element->table_name != 'jos_emundus_comments' || $c == $count){
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colonne, $i, str_replace('=', ' ', $value));
							$objPHPExcel->getActiveSheet()->getColumnDimension($colonne_by_id[$colonne])->setAutoSize(true);
							$colonne++;
							//clean comment square for follow applicant
							$tab_value ='';
							$value = '';
						}
					}
				}				
				$i++;	
			}
			// debug file
			/*ob_start(); 
			var_export(var_dump($objPHPExcel)); 

			$tab_debug=ob_get_contents(); 
			ob_end_clean(); 

			$fichier=fopen('test2.log','w'); 
			fwrite($fichier,$tab_debug); 
			fclose($fichier);*/
			// end debug file
			
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
			header("Content-type: application/force-download");
			header('Content-Type: application/vnd.ms-excel');
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