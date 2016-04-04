<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2016 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikashopVoteClass extends hikashopClass {
	var $tables = array('vote');
	var $pkeys = array('vote_id');
	var $toggle = array('vote_published'=>'vote_id');
	var $votePublished = array('vote_published'=>'vote_id');
	var $paginationStart = 0;
	var $paginationLimit = 50;
	var $error = array('code' => '', 'message' => '');
	var $values = array('average' => '', 'total' => '');

	function saveUseful(&$element){
		$config = hikashop_config();
		if($config->get('useful_rating',0) == 0){
			$this->error = array('code' => '505020', 'message' => JText::_('HIKA_VOTE_USEFUL_RATING_DISABLED'));
			return false;
		}

		$user_id = hikashop_loadUser();
		if($config->get('register_note_comment',1) == 1 && is_null($user_id)){
			$this->error = array('code' => '505021', 'message' => JText::_('HIKA_VOTE_MUST_BE_REGISTERED_FOR_USEFUL_RATING'));
			return false;
		}

		$voteClass = hikashop_get('class.vote');
		$vote = $voteClass->get($element->vote_id);
		if($element->value == '1')
			$vote->vote_useful++;
		else
			$vote->vote_useful--;
		$success = parent::save($vote);
		if(!$success){
			$this->error = array('code' => '505016', 'message' => JText::_('HIKA_VOTE_ERROR_SAVING_DATA'));
			return false;
		}

		if(is_null($user_id))
			$user_id = hikashop_getIP();
		$db = JFactory::getDBO();
		$db->setQuery('INSERT INTO '.hikashop_table('vote_user').' VALUES ('.(int)$element->vote_id.','.$db->quote($user_id).',1) ');
		$success = $db->query();
		if(!$success){
			$this->error = array('code' => '505016', 'message' => JText::_('HIKA_VOTE_ERROR_SAVING_DATA'));
			return false;
		}else{
			$this->error = array('code' => '200', 'message' => JText::_('THANK_FOR_VOTE'));
			return false;
		}
	}

	function checkVote(&$element){
		$safeHtmlFilter = JFilterInput::getInstance(null, null, 1, 1);

		if(empty($element->vote_ref_id) || (int)$element->vote_ref_id == 0){
			$this->error = array('code' => '505001', 'message' => JText::_('HIKA_VOTE_ITEM_ID_MISSING'));
			return false;
		}

		if((!isset($element->vote_type) || empty($element->vote_type)) && (!$this->app->isAdmin() || $this->app->isAdmin() && $element->vote_id == 0)){
			$this->error = array('code' => '505015', 'message' => JText::_('HIKA_VOTE_TYPE_MISSING'));
			return false;
		}

		$allowedVoteType = $this->config->get('enable_status_vote','nothing');
		$element->vote_rating = (isset($element->vote_rating))?$element->vote_rating:0;
		$element->vote_comment = trim($safeHtmlFilter->clean(strip_tags((isset($element->vote_comment))?$element->vote_comment:''), 'string'));
		$correctRating = 1;
		if((int)$element->vote_rating == 0 || (int)$element->vote_rating > $this->config->get('vote_star_number',5))
			$correctRating = 0;

		$user = hikashop_loadUser(true);
		if($this->config->get('access_vote','public') == 'registered' && is_null($user)){
			$this->error = array('code' => '505017', 'message' => JText::_('ONLY_REGISTERED_CAN_VOTE'));
			return false;
		}

		if($this->app->isAdmin()){
			$element->vote_user_id = hikashop_getIP();
		}else{
			if(!is_null($user)){
				$element->vote_user_id = (int)$user->user_id;
				$element->vote_pseudo = $user->username;
				$element->vote_email = $user->email;
			}else{
				$element->vote_user_id = hikashop_getIP();
				$element->vote_pseudo = trim($safeHtmlFilter->clean(strip_tags((isset($element->vote_pseudo))?$element->vote_pseudo:''), 'string'));

				if(!$correctRating && !empty($element->vote_comment)){
					if(!$element->vote_pseudo){
						$this->error = array('code' => '505011', 'message' => JText::_('HIKA_VOTE_PSEUDO_REQUIRED'));
						return false;
					}

					if($this->config->get('email_comment','1')){
						$element->vote_email = trim($safeHtmlFilter->clean(strip_tags((isset($element->vote_email))?$element->vote_email:''), 'string'));
						if(!$element->vote_email || empty($element->vote_email)){
							$this->error = array('code' => '505012', 'message' => JText::_('HIKA_VOTE_EMAIL_REQUIRED'));
							return false;
						}
					}
				}
			}

			if($this->config->get('access_vote','public') != 'public' && is_null($user)){
				$this->error = array('code' => '505002', 'message' => JText::_('HIKA_VOTE_REGISTRATION_REQUIRED'));
				return false;
			}

			if($element->vote_type == 'product' && $this->config->get('access_vote','public') == 'buyed'){
				$hasBought = $this->hasBought($element->vote_ref_id, $user->user_id);
				if(!$hasBought){
					$this->error = array('code' => '505003', 'message' => JText::_('HIKA_VOTE_ITEM_BOUGHT_REQUIRED'));
					return false;
				}
			}
		}

		if(in_array($allowedVoteType,array('vote','two')) && $element->vote_rating != 0)
			$element->vote_comment = '';

		if($allowedVoteType == 'nothing'){
			$this->error = array('code' => '505005', 'message' => JText::_('HIKA_VOTE_NOT_ALLOWED'));
			return false;
		}

		if($allowedVoteType == 'vote' && !$correctRating){
			$this->error = array('code' => '505006', 'message' => JText::_('HIKA_VOTE_WRONG_RATING_VALUE'));
			return false;
		}

		if($allowedVoteType == 'comment' && $element->vote_comment == ''){
			$this->error = array('code' => '505007', 'message' => JText::_('HIKA_VOTE_EMPTY_COMMENT'));
			return false;
		}

		if($allowedVoteType == 'two' && $element->vote_comment == '' && !$correctRating){
			$this->error = array('code' => '505008', 'message' => JText::_('HIKA_VOTE_WRONG_VOTE_COMMENT_VALUE'));
			return false;
		}

		if($allowedVoteType == 'both' && ($element->vote_comment == '' || !$correctRating)){
			$this->error = array('code' => '505009', 'message' => JText::_('HIKA_VOTE_MISSING_VOTE_COMMENT_VALUE'));
			return false;
		}

		if(!empty($element->vote_comment) && (!isset($element->vote_id) || $element->vote_id == 0)){
			$nbComment = $this->commentPassed($element->vote_type, $element->vote_ref_id, $element->vote_user_id);
			if(in_array($allowedVoteType,array('comment','two','both')) && !empty($element->vote_comment) && $nbComment >= $this->config->get('comment_by_person_by_product','30')){
				$this->error = array('code' => '505010', 'message' => JText::_('HIKA_VOTE_LIMIT_REACHED'));
				return false;
			}
		}
		return true;
	}

	function save(&$element){
		$this->app = Jfactory::getApplication();
		$this->config = hikashop_config();
		$dispatcher = JDispatcher::getInstance();
		$db = JFactory::getDBO();

		if(isset($element->vote_ref_id) || !$this->app->isAdmin())
			$this->checkVote($element);

		if(!empty($this->error['code']))
			return false;

		$element->vote_date = time();
		if(!$this->app->isAdmin()){
			$element->vote_ip = hikashop_getIP();
			if(!empty($element->vote_comment) && !$this->config->get('published_comment','1'))
				$element->vote_published = 0;
			else
				$element->vote_published = 1;
		}

		$oldElement = new stdClass();
		if($this->app->isAdmin()){
			if($element->vote_id != '0'){
				$query = 'SELECT * FROM '.hikashop_table('vote').' WHERE vote_id = '.(int)$element->vote_id;
				$db->setQuery($query);
				$result = $db->loadObject();
				if(!empty($result)){
					$oldElement = $result;
					if(!isset($element->vote_ref_id)){
						$published = $element->vote_published;
						$element = clone($result);
						$element->vote_published = $published;
					}
					$element->vote_type = $result->vote_type;
				}else{
					$this->error = array('code' => '505018', 'message' => JText::_('HIKA_VOTE_MISSING_ENTRY'));
					return false;
				}
			}
		}elseif($element->vote_rating != 0 && !in_array($this->config->get('enable_status_vote','nothing'), array('nothing','comment','both'))){ //If it is only a rating
			$result = $this->getUserRating($element->vote_type,$element->vote_ref_id,$element->vote_user_id);
			if(!empty($result)){
				$element->vote_id = $result->vote_id;
				$element->vote_published = $result->vote_published;
				$oldElement = $result;
			}else{
				$element->vote_id = 0;
			}
		}else{
			$element->vote_id = 0;
		}

		$new = false;
		if($element->vote_id == 0)
			$new = true;

		if($new)
			$dispatcher->trigger('onBeforeVoteCreate', array( &$oldElement, &$do, &$element ) );
		else
			$dispatcher->trigger('onBeforeVoteUpdate', array( &$oldElement, &$do, &$element ) );

		$success = parent::save($element);
		if(!$success){
			$this->error = array('code' => '505016', 'message' => JText::_('HIKA_VOTE_ERROR_SAVING_DATA'));
			return false;
		}

		$return_data = array('average' => 0, 'total' => 0);
		if($element->vote_type != 'product') {
			$db = JFactory::getDBO();
			$query = 'SELECT AVG(v.vote_rating) AS average, COUNT(v.vote_id) AS total FROM '.hikashop_table('vote').' AS v '.
				' WHERE vote_ref_id = ' . (int)$element->vote_ref_id .' AND vote_type = ' . $db->Quote($element->vote_type).' AND v.vote_rating != 0';
			$db->setQuery($query);
			$data = $db->loadObject();
			if($data->total == 0){
				$return_data['average'] = $element->vote_rating;
				$return_data['total'] = 1;
			}else{
				if(!$new){
					$return_data['average'] = (($data->total * $data->average) - $oldElement->vote_rating + $element->vote_rating) / $data->total;
					$return_data['total'] = $data->total;

				}else{
					$return_data['average'] = (($data->total * $data->average) + $element->vote_rating) / ($data->total + 1);
					$return_data['total'] = $data->total++;
				}
			}
		}

		if(!$new){
			$dispatcher->trigger('onAfterVoteCreate', array( &$element, &$return_data ) );
			$this->error = array('code' => '1', 'message' => JText::_('VOTE_UPDATED'));
		}else{
			$dispatcher->trigger('onAfterVoteUpdate', array( &$element, &$return_data ) );
			$this->error = array('code' => '2', 'message' => JText::_('THANK_FOR_VOTE'));
		}

		$itemClass = hikashop_get('class.'.$element->vote_type);
		if($itemClass === null)
			return true;

		if(is_object($itemClass) && !empty($itemClass)){
			$data = $itemClass->get($element->vote_ref_id);
			if(isset($data->alias))
				unset($data->alias);
		}else{
			$data = new stdClass();
		}

		if($element->vote_rating == 0)
			return false;

		if($element->vote_type == 'product') {
			$newValues = $this->updateAverage($element, $oldElement, $data);
			$return_data = array('average' => $newValues->product_average_score, 'total' => $newValues->product_total_vote);
		}

		$this->values = $return_data;

		$success = $itemClass->save($data);

		if(!$success){
			$this->error = array('code' => '505013', 'message' => JText::_('HIKA_VOTE_ERROR_SAVING_ITEM_DATA'));
			return false;
		}
		return true;
	}

	function updateAverage(&$element, $oldElement, &$data){
		$addVote = false;
		if(isset($oldElement->vote_published) && $oldElement->vote_published == '0' && $element->vote_published == '1'){
			if($element->vote_rating == 0)
				$element->vote_rating = $oldElement->vote_rating;
			if($element->vote_rating == 0)
				return $data;
			$addVote = true;
		}
		if($element->vote_id == '0' || $addVote){
			$data->product_average_score = (($data->product_total_vote * $data->product_average_score) + $element->vote_rating) / ($data->product_total_vote + 1);
			$data->product_total_vote = $data->product_total_vote + 1;
			return $data;
		}
		if(isset($oldElement->vote_published) && $oldElement->vote_published == '1' && $element->vote_published == '0'){
			if($oldElement->vote_rating == 0)
				return $data;
			if($data->product_total_vote - 1 == 0){
				$data->product_average_score = $data->product_total_vote = 0;
				return $data;
			}
			$data->product_average_score = (($data->product_total_vote * $data->product_average_score) - $oldElement->vote_rating) / ($data->product_total_vote - 1);
			$data->product_total_vote = $data->product_total_vote - 1;
			return $data;
		}
		if($element->vote_published == '1'){
			if($data->product_total_vote == 0){
				$data->product_average_score = $data->product_total_vote = 0;
				return $data;
			}
			$data->product_average_score = (($data->product_total_vote * $data->product_average_score) - $oldElement->vote_rating + $element->vote_rating) / $data->product_total_vote;
			return $data;
		}
	}

	function delete(&$elements){
		$db = JFactory::getDBO();
		JArrayHelper::toInteger($elements);
		JPluginHelper::importPlugin('hikashop');
		$dispatcher = JDispatcher::getInstance();
		$do = true;
		$currentElements = array();
		$dispatcher->trigger('onBeforeVoteDelete', array(&$elements, &$do, &$currentElements) );
		if(!$do)
			return false;

		$db->setQuery('SELECT vote_id, vote_rating, vote_ref_id, vote_published, vote_type FROM '.hikashop_table('vote').' WHERE vote_id IN ('.implode(',',$elements).')');
		$results = $db->loadObjectList();

		foreach($results as $result) {
			if($result->vote_type == 'product'){
				$dataClass = hikashop_get('class.'.$result->vote_type);
				$data = $dataClass->get($result->vote_ref_id);
			}

			$status = parent::delete($result->vote_id);
			if($status && isset($data) && $data !== null) {
				$query = 'DELETE FROM '.hikashop_table('vote_user').' WHERE vote_user_id = '.(int)$result->vote_id.' ';
				$db->setQuery($query);
				$db->query();

				if($result->vote_type == 'product'){
					$oldResult = clone($result);
					$result->vote_published = 0;
					if($result->vote_rating != '0')
						$this->updateAverage($result, $oldResult, $data);
					unset($data->alias);
					$dataClass->save($data,true);
				}
			}
		}
		$dispatcher->trigger('onAfterVoteDelete', array(&$elements) );
		return true;
	}

	function saveForm(){
		$element = new stdClass();
		$element->vote_id = hikashop_getCID('vote_id');
		$formData = JRequest::getVar( 'data', array(), '', 'array' );
		jimport('joomla.filter.filterinput');
		$safeHtmlFilter = & JFilterInput::getInstance(null, null, 1, 1);
		foreach($formData['vote'] as $column => $value){
			hikashop_secureField($column);
			$element->$column = $safeHtmlFilter->clean($value);
			if($column!='vote_comment'){
				$element->$column = strip_tags($element->$column);
			}
		}
		$result = $this->save($element);
		return $result;
	}

	function loadJS() {
		static $done = false;
		if($done)
			return true;
		$done = true;

		hikashop_loadJsLib('tooltip');

		$config = hikashop_config();
		$voteType = 0;
		if($config->get('enable_status_vote','0') == 'both')
			$voteType = 1;

		$js = '
hikaVote.setOptions({
	itemId : "'.hikashop_getCID().'",
	urls : {
		save : "'.hikashop_completelink('vote&task=save',true,true).'",
		show : "'.hikashop_completelink('vote&task=show',true,true).'"
	},ctrl : "'.JRequest::getVar('ctrl','product').'",
	both : "'.$voteType.'"
});

function hikashop_vote_useful(hikashop_vote_id, val) { return hikaVote.useful(hikashop_vote_id, val); }
function hikashop_send_comment(){ return hikaVote.vote(0,"hikashop_vote_rating_id"); }
function hikashop_send_vote(rating, from){ return hikaVote.vote(rating, from); }
		';
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration("\n<!--\n" . $js . "\n//-->\n");
	}

	function sendNotifComment($vote_id, $comment, $vote_ref_id, $user_id, $pseudo, $email, $vote_type){
		if($pseudo != '0'){
			$username = $pseudo;
			$email = $email;
			$config =& hikashop_config();
			$email_enabled = $config->get('email_comment');
			if($email_enabled == 0){
				$email = "Not required";
			}
		} else {
			$userClass = hikashop_get('class.user');
			$userInfos = $userClass->get($user_id);
			if(!empty($userInfos)){
				$username	= $userInfos->username;
				$email	= $userInfos->email;
			}
		}

		$result = new stdClass();
		$result->vote_id = $vote_id;
		$result->vote_type = $vote_type;
		$result->product_id = $vote_ref_id;
		$result->username_comment = $username;
		$result->email_comment = $email;
		$result->comment = $comment;

		$type = null;
		if($vote_type == 'product') {
			$productClass = hikashop_get('class.product');
			$type = $productClass->get($vote_ref_id);
		}

		$mailClass = hikashop_get('class.mail');
		$infos = new stdClass();
		$infos->type =& $type;
		$infos->result =& $result;
		$mail = $mailClass->get('new_comment',$infos);
		$mail->subject = JText::sprintf($mail->subject,HIKASHOP_LIVE);
		$config =& hikashop_config();

		$mail->dst_email = $config->get('email_each_comment');
		$mailClass->sendMail($mail);
		return ;
	}

	function get($id, $type = 'product', $content = 'main', $infos = array()){
		return parent::get($id);
	}

	function getList($id, $type = 'product', $content = 'main', $infos = array()){
		$votes = array();
		if((int)$id == 0)
			return $votes;

		$config = hikashop_config();
		$db = JFactory::getDBO();

		$select = 'SELECT a.*, b.*, c.*';
		$from = ' FROM '.hikashop_table('vote').' AS a';
		$leftJoin = ' LEFT JOIN '.hikashop_table('user').' AS b ON a.vote_user_id = b.user_id';
		$leftJoin .= ' LEFT JOIN '.hikashop_table('users',false).' AS c ON b.user_cms_id = c.id';
		$where = ' WHERE vote_type = '.$db->quote($type).' AND vote_ref_id = '.(int)$id;
		$where .= ' AND vote_published = 1';
		$sort = $config->get('vote_comment_sort');
		if($sort == 'date_desc'){
			$order = ' ORDER BY vote_date DESC';
		}elseif($sort == 'helpful'){
			$order = ' ORDER BY vote_useful ASC';
		}else{
			$order = ' ORDER BY vote_date ASC';
		}
		$limit = ' LIMIT '.$this->paginationStart.','.$this->paginationLimit;

		$query = $select.$from.$leftJoin.$where.$order.$limit;
		$db->setQuery($query);
		$votes = $db->loadObjectList('vote_id');

		$voteInfos = array('vote_id','vote_rating','vote_comment','vote_useful','vote_pseudo','vote_date');
		$userInfos = array('username');

		$allInfos = array_merge($voteInfos, $userInfos, $infos);

		$ids = array();
		foreach($votes as $k => $vote){
			$ids[] = $vote->vote_id;
			$userData = new stdClass();
			foreach($vote as $l => $data){
				if(!in_array($l,$allInfos)){
					unset($votes[$k]->$l);
				}
				if(in_array($l,$userInfos)){
					$userData->$l = $data;
					unset($votes[$k]->$l);
				}
			}

			if(isset($votes[$k]->vote_rating)){
				$votes[$k]->vote_value = $votes[$k]->vote_rating;
				unset($votes[$k]->vote_rating);
			}

			$votes[$k]->vote_username = '';
			if(!empty($userData->username)){
				$votes[$k]->vote_username = $userData->username;
			}elseif($vote->vote_pseudo != 0){
				$votes[$k]->vote_username = $vote->vote_pseudo;
			}
			unset($votes[$k]->vote_pseudo);
		}

		if($content == 'full' && !empty($ids)){
			$query = 'SELECT * FROM '.hikashop_table('vote').' WHERE vote_published = 1 AND vote_ref_id IN ('.implode(',',$ids).') AND vote_type LIKE '.$db->quote('criterion-%');
			$db->setQuery($query);
			$criterions = $db->loadObjectList('vote_id');

			$categoryIds = array();
			foreach($criterions as $k => $criterion){
				$categoryIds[] = str_replace('criterion-','',$criterion->vote_type);
			}

			$query = 'SELECT * FROM '.hikashop_table('category').' WHERE category_published = 1 AND category_id IN ('.implode(',',$categoryIds).')';
			$db->setQuery($query);
			$categories = $db->loadObjectList('category_id');

			$categoryInfos = array('category_name','category_description');
			$allInfos = array_merge($categoryInfos, $infos);
			foreach($categories as $k => $categorie){
				foreach($categorie as $l => $data){
					if(!in_array($l,$allInfos)){
						unset($categories[$k]->$l);
					}
				}
			}

			$criterionInfos = array('vote_id','vote_rating','vote_date');
			$allInfos = array_merge($criterionInfos, $infos);
			$categoryIds = array();
			foreach($criterions as $k => $criterion){
				$refId = $criterion->vote_ref_id;
				$categoryId = str_replace('criterion-','',$criterion->vote_type);
				$categoryIds[] = $categoryId;
				foreach($criterion as $l => $data){
					if(!in_array($l,$allInfos)){
						unset($criterion->$l);
					}
				}

				foreach($criterion as $l => $criterionInfo){
					$name = str_replace('vote','criterion',$l);
					$votes[$refId]->vote_criterions[$criterion->vote_id]->$name = $criterionInfo;
				}
				$votes[$refId]->vote_criterions[$criterion->vote_id]->criterion_name = $categories[$categoryId]->category_name;
				$votes[$refId]->vote_criterions[$criterion->vote_id]->criterion_description = $categories[$categoryId]->category_description;
			}
		}
		return $votes;
	}

	function hasBought($vote_ref_id, $user_id){
		$purchased = 0;
		$db = JFactory::getDBO();
		$query = 'SELECT order_id FROM '.hikashop_table('order').' WHERE order_user_id = '.$db->quote($user_id).'';
		$db->setQuery($query);
		if(!HIKASHOP_J25){
			$order_ids = $db->loadResultArray();
		} else {
			$order_ids = $db->loadColumn();
		}
		if(!empty($order_ids)) {
			$query = 'SELECT product_id FROM '.hikashop_table('product').' WHERE product_parent_id = '.(int)$vote_ref_id.'';
			$db->setQuery($query);
			if(!HIKASHOP_J25){
				$product_ids = $db->loadResultArray();
			} else {
				$product_ids = $db->loadColumn();
			}
			if(empty($product_ids)) {
				$product_ids =  array(0 => 0);
			}
			$query = 'SELECT order_product_id FROM '.hikashop_table('order_product').' WHERE order_id IN ('.implode(',',$order_ids).') AND product_id = '.(int)$vote_ref_id.' OR product_id IN ('.implode(',',$product_ids).')';
			$db->setQuery($query);
			$result = $db->loadObjectList();
			if(!empty($result))
				$purchased = 1;
		}
		return $purchased;
	}

	function commentPassed($vote_type, $vote_ref_id, $user_id){
		$nb_comment = 0;
		$db = JFactory::getDBO();
		$query = 'SELECT vote_comment FROM '.hikashop_table('vote').' WHERE vote_type = '.$db->quote($vote_type).' AND vote_ref_id = '.(int)$vote_ref_id.' AND vote_user_id = '.$db->quote($user_id).' AND vote_comment != \'\'';
		$db->setQuery($query);
		$results = $db->loadObjectList();
		foreach($results as $result) {
			$nb_comment++;
		}
		return $nb_comment;
	}

	function getUserRating($type, $ref_id, $user_id = ''){
		if(empty($user_id)){
			$user_id = hikashop_loadUser();
			if($user_id == null)
				$user_id = '';
		}
		$db = JFactory::getDBO();
		$filters = array('vote_type = '.$db->quote($type),'vote_ref_id = '.(int)$ref_id,'vote_rating != 0');
		if(empty($user_id) || $user_id == hikashop_getIp()){
			$filters[] = 'vote_ip = '.$db->quote(hikashop_getIp());
			$filters[] = 'vote_user_id = \'\'';
		}else{
			$filters[] = 'vote_user_id = '.$db->quote($user_id);
		}
		$query = 'SELECT * FROM '.hikashop_table('vote').' WHERE '.implode(' AND ',$filters);
		$db->setQuery($query);
		$result = $db->loadObject();
		return $result;
	}
}
