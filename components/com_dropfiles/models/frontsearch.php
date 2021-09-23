<?php
/**
 * Dropfiles
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 *
 * @package   Dropfiles
 * @copyright Copyright (C) 2013 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2013 Damien Barrère (http://www.crac-design.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */

// no direct access
defined('_JEXEC') || die;

jimport('joomla.access.access');
jimport('joomla.application.component.modellist');

/**
 * Class DropfilesModelFrontsearch
 */
class DropfilesModelFrontsearch extends JModelList
{

    /**
     * Check string exist
     *
     * @param string $str    String
     * @param string $substr String to search
     *
     * @return boolean
     */
    public function strExists($str, $substr)
    {
        if ($str === '' || $substr === '') {
            return false;
        }

        $substrEntity = htmlentities($substr, ENT_QUOTES, 'UTF-8');
        if ($str !== null && $substr !== null && (strpos(strtolower($str), strtolower($substr)) !== false || strpos(strtolower($str), strtolower($substrEntity)) !== false)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Method highlight text
     *
     * @param string $words Keyword
     * @param string $text  Text
     *
     * @return mixed
     */
    public function highlightText($words, $text)
    {
        if ($words !== null && $text !== null) {
            $substr = substr($text, strpos(strtolower($text), strtolower($words)), strlen($words));
        } else {
            $substr = $words;
        }
        $output = str_replace($substr, '<span style="background: #FFFFCC;">' . $substr . '</span>', $text);

        // Highlight text for description with htmlentity
        $words = htmlentities($words, ENT_QUOTES, 'UTF-8');
        if ($words !== null && $output !== null) {
            $substr = substr($output, strpos(strtolower($output), strtolower($words)), strlen($words));
        } else {
            $substr = $words;
        }
        $output = str_replace($substr, '<span style="background: #FFFFCC;">' . $substr . '</span>', $output);

        return $output;
    }

    /**
     * Method list files
     *
     * @param string  $keywords     Keywords
     * @param string  $cond         Search condition
     * @param boolean $read_content Search in content
     * @param array   $lstAllFile   RefFiles
     *
     * @return mixed
     */
    public function listFiles($keywords, $cond, $read_content = false, $lstAllFile = array())
    {
        $condition = '';
        $db = $this->getDbo();
        if ($cond !== '') {
            $condition = ' AND ' . $cond;
        }
        if ($read_content) {
            $query = str_replace('{CONDITIONS}', $condition, $this->buildQuery($keywords));
        } else {
            $query = 'SELECT a.*,c.title as cattitle FROM #__dropfiles_files AS a,#__categories as c';
            $query .= ' WHERE a.catid = c.id ' . $condition;
        }
        $refFileList = array();
        if (is_array($lstAllFile) && !empty($lstAllFile)) {
            $searchCate = $lstAllFile['searchCate'];
            $catequery = 'SELECT * FROM #__categories WHERE id=' . $searchCate;
            $cateresult = $db->setQuery($catequery);
            $refCate = null;
            $refCate = $cateresult->loadObject();
            foreach ($lstAllFile as $key => $item) {
                if ($key === 'searchCate') {
                    continue;
                }
                $refquery = 'SELECT * FROM #__dropfiles_files';
                $refquery .= ' WHERE id= ' . $item['id'] ;
                $result = $db->setQuery($refquery);
                $refFile = null;
                $refFile = $result->loadObject();
                $refFile->cattitle = $refCate->title;
                $refFileList[] = $refFile;
            }
        }
        $result = $db->setQuery($query);
        $files = null;
        $files = $result->loadObjectList();
        if (!empty($refFileList)) {
            $files = array_merge($refFileList, $files);
        }
        foreach ($files as $file) {
            $file->title = html_entity_decode($file->title, ENT_QUOTES, 'UTF-8');
        }

        return $files;
    }

    /**
     * Method to build full text search query
     *
     * @param string $keywords Keywords
     *
     * @return string
     */
    public function buildQuery($keywords)
    {
        $db = JFactory::getDbo();
        // Search setting
        $cw = array(
            'title'       => 0.8,
            'description' => 0.8,
            'content'     => 0.5
        );
        // Search keyword
        $sentence = true;

        // Table index
        $filesTable   = $db->quoteName('#__dropfiles_files');
        $indexTable   = $db->quoteName('#__dropfiles_fts_index');
        $wordsTable   = $db->quoteName('#__dropfiles_fts_words');
        $vectorsTable = $db->quoteName('#__dropfiles_fts_vectors');
        $docsTable    = $db->quoteName('#__dropfiles_fts_docs');
        $stopsTable   = $db->quoteName('#__dropfiles_fts_stops');

        $searchTermCount = 1;
        $search_terms    = array();
        $txnc            = '';
        $join            = '';
        $fields          = '';
        $matches         = array();

        if (!empty($keywords)) {
            $keywords = urldecode(stripslashes($keywords));
            $keywords = str_replace(array("\r", "\n"), '', $keywords);

            if (!$sentence) {
                $search_terms = array($keywords);
            } else {
                if (preg_match_all('/".*?("|$)|((?<=[\t ",+])|^)[^\t ",+]+/', $keywords, $matches)) {
                    $search_terms_count = count($matches[0]);
                    $search_terms = $this->parseSearchTerms($matches[0]);
                    // if the search string has only short terms or stopwords,
                    // or is 10+ terms long, match it as sentence
                    if (empty($search_terms) || count($search_terms) > 9) {
                        $search_terms = array($keywords);
                    }
                } else {
                    $search_terms = array($keywords);
                }
            }

            // Decode Terms
            $minChars = 0;
            $ts = array();
            foreach ($search_terms as $t) {
                //$f = !empty($q['exact']) ? 1 : 0;

                if (mb_substr($t, 0, 1, 'utf-8') === '"') {
                    $t2 = explode(' ', trim($t, '"'));
                    $f = 1;
                } else {
                    $t2 = explode(' ', trim($t));
                    $f = 0;
                }
                if (is_array($t2)) {
                    foreach ($t2 as $tt) {
                        if (mb_strlen(trim($tt), 'utf-8') >= $minChars) {
                            if ($f) {
                                $ts[] = array(1, trim($tt));
                            } else {
                                $ts[] = array(0, trim($tt));
                            }
                        }
                    }
                }
            }
            $search_terms = $ts;
            $search_terms_count = count($ts);

            // Build Query
            $j = '';
            if ($search_terms_count > 0) {
                $i = 1;
                foreach ($search_terms as $term) {
                    if ($i > 1) {
                        $j .= ' inner join ';
                    }
                    if (!$term[0]) {
                        // Like
                        $j .= '(
                                     select ' . $txnc . '
                                        ds1.id,
                                        ds1.index_id,
                                        ds1.token,
                                        v1.f
                                     from ' . $wordsTable . ' w1    
                                     left join ' . $vectorsTable . ' v1
                                        on v1.wid = w1.id
                                     left join ' . $docsTable . ' ds1
                                        on v1.did = ds1.id
                                     where
                                        (w1.`word` like "%' . $this->escLike($term[1]) . '%")
                                     ) t' . $i;
                    } else {
                        // Exact equality
                        $j .= '(
                                    select ' . $txnc . '
                                        ds1.id,
                                        ds1.index_id,
                                        ds1.token,
                                        v1.f
                                    from ' . $wordsTable . ' w1    
                                    left join ' . $vectorsTable . ' v1
                                        on v1.wid = w1.id
                                    left join ' . $docsTable . ' ds1
                                        on v1.did = ds1.id
                                        where
                                        (w1.`word` = "' . $this->escLike($term[1]) . '")
                                    ) t' . $i;
                    }
                    if ($i > 1) {
                        $j .= ' on t' . $i . '.id = t1.id';
                    }

                    $i++;
                }

                $j .= ' group by t1.index_id
                            ) t3
                                on t3.index_id = fi.id
                            ) df_t
                                on df_t.tid = a.id';

                $fields = ', df_t.relev ';
            }

            $i--;

            if ($i < 2) {
                $relev = 't1.f';
            } else {
                $sum = array();
                for ($ii = 1; $ii <= $i; $ii++) {
                    $sum[] = 't' . $ii . '.f';
                }
                $relev = '(' . implode('+', $sum) . ') / ' . count($sum);
            }

            if (count($cw) > 1) {
                $x = array();

                foreach ($cw as $k => $d) {
                    //if(t1.token = "post_title", 100, 50)
                    $x[] = ' when "' . $k . '" then ' . floatval($d);
                }

                $rcv = ' (case t1.token ' . implode('', $x) . ' else 1 end)';
            } else {
                $rcv = 1;
            }

            $jhdr = ' left join (
                            select ' . $txnc . '
                                fi.tid,
                                t3.relev
                            from ' . $indexTable . ' fi
                            inner join (
                                select
                                    t1.index_id, 
                                    sum(' . $relev . ' * ' . $rcv . ') relev
                                from ';
            $join .= $jhdr . $j;
        }

//        $parts = array(
//            'join' => $join,
//            'select' => ' and (not isnull(df_t.tid))',
//            'orderby' => ' (df_t.relev) desc',
//            'fields' => $fields,
//        );
        $query = 'SELECT a.*' . $fields . ', c.title as cattitle FROM ' . $filesTable . ' a';
        $query .= ' inner join #__categories as c on a.catid = c.id ';
        $query .= $join;
        $query .= ' WHERE 1=1 AND (NOT ISNULL(df_t.tid)) {CONDITIONS}
                    ORDER BY (df_t.relev) DESC, a.created_time DESC';
        return $query;
    }

    /**
     * Esc like
     *
     * @param string $text Input text
     *
     * @return string
     */
    private function escLike($text)
    {
        return addcslashes($text, '_%\\');
    }

    /**
     * Parse search terms
     *
     * @param array $keywords Keywords
     *
     * @return array
     */
    private function parseSearchTerms($keywords)
    {
        $keys = array();

        foreach ($keywords as $key) {
            $keys[] = mb_strtolower(trim($key), 'utf-8');
        }

        return $keys;
    }

    /**
     * Method get file search
     *
     * @param string $words      Words
     * @param array  $searchby   Search by
     * @param strong $condition  Condition
     * @param string $phrases    Phrases
     * @param array  $lstAllFile RefFiles
     *
     * @return array
     */
    public function getFilesSearch($words, $searchby, $condition, $phrases = null, $lstAllFile = array())
    {
        $results = array();

        $params = JComponentHelper::getParams('com_dropfiles');

        if ((int) $params->get('plain_text_search', 0) === 1) {
            $read_content = true;
        } else {
            unset($searchby['content']);
            $read_content = false;
        }
        $files = $this->listFiles($words, $condition, $read_content, $lstAllFile);
        if ($read_content) {
            return $files;
        }

        if ($words !== '' && isset($searchby)) {
            foreach ($files as $file) {
                foreach ($searchby as $v) {
                    //if ($this->countWords($words, $file->$v, $phrases)) {
                    if ($this->strExists(strtolower($file->$v), strtolower($words)) ||
                        $this->strExists(strtolower($words), strtolower($file->$v)) ||
                        strtolower($words) === strtolower($file->$v)) {
                        $results[] = $file;
                        if ($v !== 'title' && $phrases !== null) {
                            switch ($phrases) {
                                case 'exact':
                                    $tmp_str = $this->stringHighlight($file->$v, $words, 7);
                                    $file->highlight = '...&nbsp;' . $this->highlightText($words, $tmp_str);
                                    $file->highlight .= '...&nbsp;';
                                    break;
                                default:
                                    $file->highlight = '';
                                    if ($v === 'content') {
                                        foreach (explode(' ', $words) as $word) {
                                            $tmp_str1 = $this->stringHighlight($file->$v, $word, 7);
                                            $tmp_str = $this->highlightText($word, $tmp_str1);
                                            $file->highlight .= $tmp_str;
                                        }
                                    } else {
                                        $file->highlight = $file->$v;
                                    }
                                    break;
                            }
                        } else {
                            $file->highlight = $file->description;
                        }
                        break;
                    }
                }
            }
        }
        return $results;
    }

    /**
     * Method get property search
     *
     * @param string $words    Words
     * @param array  $searchby Search by
     * @param array  $items    Items
     *
     * @return array
     */
    public function getPropertySearch($words, $searchby, $items)
    {
        $results = array();

        foreach ($items as $file) {
            foreach ($searchby as $v) {
                if ($this->strExists(strtolower($file->$v), strtolower($words)) ||
                    $this->strExists(strtolower($words), strtolower($file->$v)) ||
                    strtolower($words) === strtolower($file->$v)) {
                    $results[] = $file;
                    break;
                }
            }
        }

        return $results;
    }

    /**
     * String high light
     *
     * @param string  $str            String to highlight
     * @param string  $query          Query string
     * @param integer $numOfWordToAdd Number of word to add
     *
     * @return string
     */
    private function stringHighlight($str, $query, $numOfWordToAdd)
    {
        list($before, $after) = explode($query, $str);
        $before = rtrim($before);
        $after = ltrim($after);

        //Return an array with elements in reverse order
        $beforeArray      = array_reverse(explode(' ', $before));
        $afterArray       = explode(' ', $after);
        $countBeforeArray = count($beforeArray);
        $countAfterArray  = count($afterArray);
        $beforeString     = '';
        if ($countBeforeArray < $numOfWordToAdd) {
            $beforeString = implode(' ', $beforeArray);
        } else {
            for ($i = 0; $i < $numOfWordToAdd; $i++) {
                $beforeString = $beforeArray[$i] . ' ' . $beforeString;
            }
        }
        $afterString = '';
        if ($countAfterArray < $numOfWordToAdd) {
            $afterString = implode(' ', $afterArray);
        } else {
            for ($i = 0; $i < $numOfWordToAdd; $i++) {
                $afterString = $afterString . $afterArray[$i] . ' ';
            }
        }
        $string = $beforeString . ' ' . $query . ' ' . $afterString;

        return $string;
    }

    /**
     * Method count words
     *
     * @param string $words   Words
     * @param string $content Content
     * @param string $phrases Phrases
     *
     * @return boolean
     */
    public function countWords($words, $content, $phrases)
    {
        $count = 0;
        $listWord = explode(' ', $words);

        foreach ($listWord as $word) {
            if ($this->strExists(strtolower($content), strtolower($word))) {
                $count++;
            }
        }
        if ($phrases === null) {
            return true;
        }
        switch ($phrases) {
            case 'all':
                if ($count === count($listWord)) {
                    $check = true;
                } else {
                    $check = false;
                }
                break;
            case 'any':
                if ($count >= 1) {
                    $check = true;
                } else {
                    $check = false;
                }
                break;
            default:
                if ($this->strExists($content, $words) || $this->strExists($words, $content)) {
                    $check = true;
                } else {
                    $check = false;
                }
                break;
        }

        return $check;
    }

    /**
     * Method get all category
     *
     * @return mixed
     */
    public function getAllCategories()
    {
        $dbo = $this->getDbo();
        $query = 'SELECT d.type,d.cloud_id,c.* FROM #__categories as c,#__dropfiles as d ';
        $query .= " WHERE c.id=d.id AND  c.extension = 'com_dropfiles'";

        // Implement View Level Access
        $user = JFactory::getUser();
        $dropfiles_params = JComponentHelper::getParams('com_dropfiles');
        if ($dropfiles_params->get('categoryrestriction', 'accesslevel') === 'accesslevel') {
            if (!$user->authorise('core.admin')) {
                $groups = implode(',', $user->getAuthorisedViewLevels());
                $query .= ' AND c.access IN (' . $groups . ')';
            }
        }

        $query .= ' Order by c.lft ASC';
        $dbo->setQuery($query);
        $dbo->execute();
        $listCat = $dbo->loadObjectList();
        JModelLegacy::addIncludePath(JPATH_ROOT . '/components/com_dropfiles/models/', 'DropfilesModelFrontconfig');
        $modelConfig = JModelLegacy::getInstance('Frontconfig', 'dropfilesModel');

        foreach ($listCat as $key => $value) {
            $params = $modelConfig->getParams($value->id);

            if ($dropfiles_params->get('categoryrestriction', 'accesslevel') === 'usergroup') {
                $usergroup = isset($params->params->usergroup) ? $params->params->usergroup : array();

                $result = array_intersect($user->getAuthorisedGroups(), $usergroup);

//                if (count($result)) {
//                } else {
//                    unset($listCat[$key]);
//                    continue;
//                }
                if (!count($result)) {
                    unset($listCat[$key]);
                    continue;
                }
            }

            if (isset($listCat[$key])) {
                if ($dropfiles_params->get('restrictfile', 0)) {
                    $user = JFactory::getUser();
                    $user_id = (int) $user->id;

                    $canViewCategory = isset($params->params->canview) ? (int) $params->params->canview : 0;
                    if ($user_id) {
                        if (!($canViewCategory === $user_id || $canViewCategory === 0)) {
                            unset($listCat[$key]);
                            continue;
                        }
                    } else {
                        if ($canViewCategory !== 0) {
                            unset($listCat[$key]);
                            continue;
                        }
                    }
                }
            }

            if (!empty($value->cloud_id)) {
                $value->id = $value->cloud_id;
            }
            if ($value->type === 'googledrive' && $dropfiles_params->get('google_credentials', '') === '') {
                unset($listCat[$key]);
            }
        }

        return $listCat;
    }

    /**
     * Method get one category local
     *
     * @param string $cloudID Cloud id
     *
     * @return mixed
     */
    public function getOneCatLocalByCloudID($cloudID)
    {
        $dbo = $this->getDbo();
        $query = 'SELECT id FROM #__dropfiles WHERE cloud_id =' . $dbo->quote($cloudID);
        $dbo->setQuery($query);
        $dbo->execute();
        $listCat = $dbo->loadObject();
        if ($listCat) {
            return $listCat->id;
        } else {
            return null;
        }
    }

    /**
     * Method get type category
     *
     * @param string $cloudID Cloud id
     *
     * @return mixed
     */
    public function getOneCatTypeByCloudID($cloudID)
    {
        $dbo = $this->getDbo();
        $query = 'SELECT type FROM #__dropfiles WHERE cloud_id =' . $dbo->quote($cloudID);
        $dbo->setQuery($query);
        $dbo->execute();
        $listCat = $dbo->loadObject();

        return $listCat->type;
    }

    /**
     * Method get category child
     *
     * @param integer $catId Category id
     *
     * @return mixed
     */
    public function getCatChilds($catId)
    {
        $dbo = $this->getDbo();

        $dropfiles_params = JComponentHelper::getParams('com_dropfiles');
        $query = 'Select * from #__categories WHERE id=' . $dbo->quote($catId);
        $dbo->setQuery($query);
        $catObj = $dbo->loadObject();

        if (!isset($catObj->lft) || !isset($catObj->rgt)) {
            return array();
        }

        JModelLegacy::addIncludePath(JPATH_ROOT . '/components/com_dropfiles/models/', 'DropfilesModelFrontconfig');
        $modelConfig = JModelLegacy::getInstance('Frontconfig', 'dropfilesModel');
        $query = "Select id from #__categories WHERE extension='com_dropfiles' ";
        $query .= ' AND lft>=' . $catObj->lft . ' AND rgt <=' . $catObj->rgt;
        $dbo->setQuery($query);
        $results = $dbo->loadColumn();
        $user = JFactory::getUser();
        $user_id = (int) $user->id;

        if (count($results)) {
            foreach ($results as $key2 => $result) {
                if ($dropfiles_params->get('restrictfile', 0)) {
                    $params = $modelConfig->getParams($result);
                    $canViewCategory = isset($params->params->canview) ? (int) $params->params->canview : 0;
                    if ($user_id) {
                        if ($canViewCategory !== $user_id || $canViewCategory !== 0) {
                            unset($results[$key2]);
                            continue;
                        }
                    } else {
                        if ($canViewCategory !== 0) {
                            unset($results[$key2]);
                            continue;
                        }
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Method get cat by id
     *
     * @param integer $catId Category id
     *
     * @return mixed
     */
    public function getCat($catId)
    {
        $dbo = $this->getDbo();

        $query = 'Select * from #__categories WHERE id=' . $dbo->quote($catId);
        $dbo->setQuery($query);
        $catObj = $dbo->loadObject();

        return $catObj;
    }

    /**
     * Method search all files in dropfile
     *
     * @param array $data Data
     *
     * @return array
     */
    public function searchFile($data)
    {
        $results = array();
        $array1 = array();
        $array2 = array();
        $count = '';
        $dbo = $this->getDbo();
        foreach ($data as $k => $value) {
            if ($value !== '') {
                $count .= $value;
            }
        }

        if (!isset($data['adminsearch']) && strlen($count) === 0) {
            return $results;
        }

        $cloud_cond = array();
        $params = JComponentHelper::getParams('com_dropfiles');
        $condition = 'a.catid = c.id ';
        $conditionGoogle = '1=1';
        $conditionDropbox = '1=1';
        $cloud_cond[] = "mimeType != 'application/vnd.google-apps.folder' and trashed =false";
        $modelConfig  = JModelLegacy::getInstance('Frontconfig', 'DropfilesModel');
        $modelFiles   = JModelLegacy::getInstance('Frontfiles', 'DropfilesModel');
        $excludeCategory = $params->get('ref_exclude_category_id');
        $excludeCategory = (isset($excludeCategory)) ? $excludeCategory : array();
        if (isset($data['q']) && $data['q'] !== '') {
            $cloud_cond[] = "title contains '" . $data['q'] . "'";
        }
        if (isset($data['catid']) && $data['catid'] !== '' && is_numeric($data['catid'])) {
            $catChilds = $this->getCatChilds($data['catid']);
            array_push($catChilds, $data['catid']);
            $condition .= ' AND a.catid IN (' . implode(',', $catChilds) . ')';// $data['catid'];
        }

        if (isset($data['ftags']) && $data['ftags'] !== '') {
            $tags_tmp = explode(',', $data['ftags']);
            $condition .= ' AND (';
            $condition1 = array();
            foreach ($tags_tmp as $k => $value) {
                if ($value !== ' ') {
                    $condition1[] = ' a.file_tags like ' . $dbo->quote('%' . $value . '%');
                }
            }
            $condition .= implode(' AND ', $condition1) . ' )';
        }
        if (isset($data['cfrom']) && isset($data['cto']) && $data['cfrom'] !== '' && $data['cto'] !== '') {
            $data['cfrom'] = date('Y-m-d', strtotime($data['cfrom']));
            $data['cto'] = date('Y-m-d', strtotime($data['cto']));

            $condition .= ' AND (DATE(a.created_time) BETWEEN ' . $dbo->quote($data['cfrom']);
            $condition .= ' AND ' . $dbo->quote($data['cto']) . ')';
            $conditionGoogle .= ' AND (DATE(a.created_time) BETWEEN ' . $dbo->quote($data['cfrom']);
            $conditionGoogle .= ' AND ' . $dbo->quote($data['cto']) . ')';
            $conditionDropbox .= ' AND (DATE(a.created_time) BETWEEN ' . $dbo->quote($data['cfrom']);
            $conditionDropbox .= ' AND ' . $dbo->quote($data['cto']) . ')';
        } else {
            if (isset($data['cfrom']) && $data['cfrom'] !== '') {
                $data['cfrom'] = date('Y-m-d', strtotime($data['cfrom']));

                $condition .= ' AND DATE(a.created_time) >=' . $dbo->quote($data['cfrom']);
                $conditionGoogle .= ' AND DATE(a.created_time) >=' . $dbo->quote($data['cfrom']);
                $conditionDropbox .= ' AND DATE(a.created_time) >=' . $dbo->quote($data['cfrom']);
            }
            if (isset($data['cto']) && $data['cto'] !== '') {
                $data['cto'] = date('Y-m-d', strtotime($data['cto']));
                $condition .= ' AND DATE(a.created_time) <=' . $dbo->quote($data['cto']);
                $conditionGoogle .= ' AND DATE(a.created_time) <=' . $dbo->quote($data['cto']);
                $conditionDropbox .= ' AND DATE(a.created_time) <=' . $dbo->quote($data['cto']);
            }
        }
        if (isset($data['ufrom']) && isset($data['uto']) && $data['ufrom'] !== '' && $data['uto'] !== '') {
            $data['ufrom'] = date('Y-m-d', strtotime($data['ufrom']));
            $data['uto'] = date('Y-m-d', strtotime($data['uto']));

            $condition .= ' AND ( DATE(a.modified_time) BETWEEN ' . $dbo->quote($data['ufrom']);
            $condition .= ' AND ' . $dbo->quote($data['uto']) . ')';
            $conditionGoogle .= ' AND ( DATE(a.modified_time) BETWEEN ' . $dbo->quote($data['ufrom']);
            $conditionGoogle .= ' AND ' . $dbo->quote($data['uto']) . ')';
            $conditionDropbox .= ' AND ( DATE(a.modified_time) BETWEEN ' . $dbo->quote($data['ufrom']);
            $conditionDropbox .= ' AND ' . $dbo->quote($data['uto']) . ')';
            $cloud_cond[] = " modifiedDate >= '" . $data['ufrom'] . "' and modifiedDate <= '" . $data['uto'] . "'";
        } else {
            if (isset($data['ufrom']) && $data['ufrom'] !== '') {
                $data['ufrom'] = date('Y-m-d', strtotime($data['ufrom']));

                $condition .= ' AND DATE(a.modified_time) >=' . $dbo->quote($data['ufrom']);
                $conditionGoogle .= ' AND DATE(a.modified_time) >=' . $dbo->quote($data['ufrom']);
                $conditionDropbox .= ' AND DATE(a.modified_time) >=' . $dbo->quote($data['ufrom']);
                $cloud_cond[] = " modifiedDate >= '" . $data['ufrom'] . "' ";
            }
            if (isset($data['uto']) && $data['uto'] !== '') {
                $data['uto'] = date('Y-m-d', strtotime($data['uto']));

                $condition .= ' AND DATE(a.modified_time) <=' . $dbo->quote($data['uto']);
                $conditionGoogle .= ' AND DATE(a.modified_time) <=' . $dbo->quote($data['uto']);
                $conditionDropbox .= ' AND DATE(a.modified_time) <=' . $dbo->quote($data['uto']);
                $cloud_cond[] = " modifiedDate <= '" . $data['uto'] . "' ";
            }
        }

        $nullDate = $dbo->quote($dbo->getNullDate());
        $date = JFactory::getDate();
        $nowDate = $dbo->quote($date->toSql());
        $condition .= ' AND (a.publish = ' . $nullDate . ' OR a.publish <= ' . $nowDate . ')';
        $condition .= ' AND (a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')';
        $condition .= ' AND a.state = 1';

        $conditionGoogle .= ' AND (a.publish = ' . $nullDate . ' OR a.publish <= ' . $nowDate . ')';
        $conditionGoogle .= ' AND (a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')';
        $conditionGoogle .= ' AND a.state = 1';

        $conditionDropbox .= ' AND (a.publish = ' . $nullDate . ' OR a.publish <= ' . $nowDate . ')';
        $conditionDropbox .= ' AND (a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')';
        $conditionDropbox .= ' AND a.state = 1';


        $query = 'SELECT a.*,c.title as cattitle FROM #__dropfiles_files as a,#__categories as c ';
        $query .= ' WHERE ' . $condition . ' ORDER BY created_time';
        if (isset($data['catid']) && is_numeric($data['catid'])) {
            $modelConfig    = JModelLegacy::getInstance('Frontconfig', 'DropfilesModel');
            $modelFile      = JModelLegacy::getInstance('Frontfile', 'dropfilesModel');
            $cateParams     = $modelConfig->getParams($data['catid']);
            $subparams      = (array) $cateParams->params;
            $refToFile      = (!empty($subparams)) ? $subparams['refToFile'] : array();
            $lstAllFile = array();
            if (isset($refToFile)) {
                foreach ($refToFile as $key => $value) {
                    if (is_array($value) && !empty($value)) {
                        foreach ($value as $item) {
                            $lstFile = (array) $modelFile->getFile($item);
                            $lstAllFile[] = $lstFile;
                        }
                    }
                }
            }
            $lstAllFile['searchCate'] = $data['catid'];
            if (isset($data['q']) && $data['q'] !== '') {
                $searchby = array('title' => 'title', 'description' => 'description', 'content' => 'content');
                $results = $this->getFilesSearch($data['q'], $searchby, $condition, $phrases = null, $lstAllFile);
                //$results =$datas;
            } else {
                $results = $this->searchFileLocal($query);
            }
        } elseif (isset($data['cattype']) && $data['cattype'] === 'dropbox') {
            $results = array();


            //is dropbox category
            if (isset($data['catid']) && strpos($data['catid'], 'id:') === 0) {
                $conditionDropbox .= ' AND a.catid = ' . $dbo->quote($data['catid']) . '';

                $queryDropbox = 'SELECT a.* FROM #__dropfiles_dropbox_files as a ';
                $queryDropbox .= ' WHERE ' . $conditionDropbox . ' ORDER BY created_time';
                $results = $this->searchFileLocal($queryDropbox);
                if (!empty($results)) {
                    foreach ($results as $item) {
                        $item->id = $item->file_id;
                        $item->catid = $this->getOneCatLocalByCloudID($item->catid);
                        if (!$item->catid) {
                            continue;
                        }
                        $cattegory = $this->getCat($item->catid);
                        $item->cattitle = $cattegory->title;
                    }
                }

                if (isset($data['q']) && $data['q'] !== '') {
                    $results = $this->getPropertySearch($data['q'], array('title', 'description'), $results);
                }
            }
        } elseif (isset($data['cattype']) && $data['cattype'] === 'googledrive') {
            if ($params->get('indexgoogle', 1)) {
                $conditionGoogle .= ' AND a.catid = ' . $dbo->quote($data['catid']) . '';

                $queryGoogle = 'SELECT a.* FROM #__dropfiles_google_files as a ';
                $queryGoogle .= ' WHERE ' . $conditionGoogle . ' ORDER BY created_time';
                $results = $this->searchFileLocal($queryGoogle);
                if (!empty($results)) {
                    foreach ($results as $item) {
                        $item->id = $item->file_id;
                        $item->catid = $this->getOneCatLocalByCloudID($item->catid);
                        if (!$item->catid) {
                            continue;
                        }
                        $cattegory = $this->getCat($item->catid);
                        $item->cattitle = $cattegory->title;
                    }
                }

                if (isset($data['q']) && $data['q'] !== '') {
                    $results = $this->getPropertySearch($data['q'], array('title', 'description'), $results);
                }
            } else {
                $results = $this->searchFileCloud($cloud_cond, $data, $params);
            }


            if (isset($data['ftags']) && $data['ftags'] !== '') {
                $results2 = array();
                $tags_tmp = explode(',', $data['ftags']);
                foreach ($results as $k => $file) {
                    $file_tags = explode(',', $file->file_tags);
                    if (count(array_intersect($file_tags, $tags_tmp)) === count($tags_tmp)) {
                        $results2[] = $file;
                    }
                }
                $results = $results2;
            }
        } elseif (isset($data['cattype']) && $data['cattype'] === 'onedrive') {
            //is onedrive category
            $conditionOnedrive = $conditionDropbox . ' AND a.catid = ' . $dbo->quote($data['catid']) . '';

            $queryOnedrive = 'SELECT a.* FROM #__dropfiles_onedrive_files as a ';
            $queryOnedrive .= ' WHERE ' . $conditionOnedrive . ' ORDER BY created_time';
            $results = $this->searchFileLocal($queryOnedrive);
            if (!empty($results)) {
                foreach ($results as $item) {
                    $item->id = $item->file_id;
                    $item->catid = $this->getOneCatLocalByCloudID($item->catid);
                    if (!$item->catid) {
                        continue;
                    }
                    $cattegory = $this->getCat($item->catid);
                    $item->cattitle = $cattegory->title;
                }
            }

            if (isset($data['q']) && $data['q'] !== '') {
                $results = $this->getPropertySearch($data['q'], array('title', 'description'), $results);
            }
        } elseif (isset($data['cattype']) && $data['cattype'] === 'onedrivebusiness') {
            //is onedrive business category
            $conditionOnedriveBusiness = $conditionDropbox . ' AND a.catid = ' . $dbo->quote($data['catid']) . '';

            $queryOnedriveBusiness = 'SELECT a.* FROM #__dropfiles_onedrive_business_files as a ';
            $queryOnedriveBusiness .= ' WHERE ' . $conditionOnedriveBusiness . ' ORDER BY created_time';
            $results = $this->searchFileLocal($queryOnedriveBusiness);
            if (!empty($results)) {
                foreach ($results as $item) {
                    $item->id = $item->file_id;
                    $item->catid = $this->getOneCatLocalByCloudID($item->catid);
                    if (!$item->catid) {
                        continue;
                    }
                    $cattegory = $this->getCat($item->catid);
                    $item->cattitle = $cattegory->title;
                }
            }

            if (isset($data['q']) && $data['q'] !== '') {
                $results = $this->getPropertySearch($data['q'], array('title', 'description'), $results);
            }
        } else {
            $array1 = array();
            if ($params->get('google_credentials', '')) {
                if ($params->get('indexgoogle', 1)) {
                    $queryGoogle = 'SELECT a.* FROM #__dropfiles_google_files as a ';
                    $queryGoogle .= ' WHERE ' . $conditionGoogle . ' ORDER BY created_time';
                    $array1 = $this->searchFileLocal($queryGoogle);

                    if (!empty($array1)) {
                        foreach ($array1 as $key => $item) {
                            if (!empty($excludeCategory) && in_array($item->catid, $excludeCategory)) {
                                unset($array1[$key]);
                                continue;
                            }
                            $item->id = $item->file_id;
                            $item->catid = $this->getOneCatLocalByCloudID($item->catid);
                            if (!$item->catid) {
                                continue;
                            }
                            $cattegory = $this->getCat($item->catid);
                            $item->cattitle = $cattegory->title;
                        }
                    }

                    if (isset($data['q']) && $data['q'] !== '') {
                        $array1 = $this->getPropertySearch($data['q'], array('title', 'description'), $array1);
                    }
                } else {
                    $array1 = $this->searchFileCloud($cloud_cond, $data, $params);
                }
            }

            if ($params->get('dropbox_token', '')) {
                $queryDropbox = 'SELECT a.* FROM #__dropfiles_dropbox_files as a ';
                $queryDropbox .= ' WHERE ' . $conditionDropbox . ' ORDER BY created_time';
                $resultsDropbox = $this->searchFileLocal($queryDropbox);
                if (!empty($resultsDropbox)) {
                    foreach ($resultsDropbox as $key => $item) {
                        if (!empty($excludeCategory) && in_array($item->catid, $excludeCategory)) {
                            unset($resultsDropbox[$key]);
                            continue;
                        }
                        $item->id = $item->file_id;
                        $item->catid = $this->getOneCatLocalByCloudID($item->catid);
                        if (!$item->catid) {
                            continue;
                        }
                        $cattegory = $this->getCat($item->catid);
                        $item->cattitle = $cattegory->title;
                    }
                }

                if (isset($data['q']) && $data['q'] !== '') {
                    $resultsDropbox = $this->getPropertySearch($data['q'], array(
                        'title',
                        'description'
                    ), $resultsDropbox);
                }
                $array1 = array_merge($array1, $resultsDropbox);
            }

            if ($params->get('onedriveCredentials', '')) {
                $queryOnedrive = 'SELECT a.* FROM #__dropfiles_onedrive_files as a ';
                $queryOnedrive .= ' WHERE ' . $conditionDropbox . ' ORDER BY created_time';
                $resultsOnedrive = $this->searchFileLocal($queryOnedrive);
                if (!empty($resultsOnedrive)) {
                    foreach ($resultsOnedrive as $key => $item) {
                        if (!empty($excludeCategory) && in_array($item->catid, $excludeCategory)) {
                            unset($resultsOnedrive[$key]);
                            continue;
                        }
                        $item->id = $item->file_id;
                        $item->catid = $this->getOneCatLocalByCloudID($item->catid);
                        if (!$item->catid) {
                            continue;
                        }
                        $cattegory = $this->getCat($item->catid);
                        $item->cattitle = $cattegory->title;
                    }
                }

                if (isset($data['q']) && $data['q'] !== '') {
                    $resultsOnedrive = $this->getPropertySearch($data['q'], array('title', 'description'), $resultsOnedrive);
                }
                $array1 = array_merge($array1, $resultsOnedrive);
            }

            if (!is_null($params->get('onedriveBusinessConnected')) && (int) $params->get('onedriveBusinessConnected') === 1) {
                $queryOnedriveBusiness  = 'SELECT a.* FROM #__dropfiles_onedrive_business_files as a ';
                $queryOnedriveBusiness .= ' WHERE ' . $conditionDropbox . ' ORDER BY created_time';
                $resultsOnedriveBusiness = $this->searchFileLocal($queryOnedriveBusiness);
                if (!empty($resultsOnedriveBusiness)) {
                    foreach ($resultsOnedriveBusiness as $key => $item) {
                        if (!empty($excludeCategory) && in_array($item->catid, $excludeCategory)) {
                            unset($resultsOnedriveBusiness[$key]);
                            continue;
                        }
                        $item->id = $item->file_id;
                        $item->catid = $this->getOneCatLocalByCloudID($item->catid);
                        if (!$item->catid) {
                            continue;
                        }
                        $cattegory = $this->getCat($item->catid);
                        $item->cattitle = $cattegory->title;
                    }
                }

                if (isset($data['q']) && $data['q'] !== '') {
                    $resultsOnedriveBusiness = $this->getPropertySearch($data['q'], array('title', 'description'), $resultsOnedriveBusiness);
                }
                $array1 = array_merge($array1, $resultsOnedriveBusiness);
            }


            if (isset($data['ftags']) && $data['ftags'] !== '') {
                $results2 = array();
                $tags_tmp = explode(',', $data['ftags']);
                foreach ($array1 as $k => $file) {
                    $file_tags = explode(',', $file->file_tags);
                    if (count(array_intersect($file_tags, $tags_tmp)) === count($tags_tmp)) {
                        $results2[] = $file;
                    }
                }
                $array1 = $results2;
            }

            if (isset($data['q']) && $data['q'] !== '') {
                $searchby = array('title' => 'title', 'description' => 'description', 'content' => 'content');
                $array2 = $this->getFilesSearch($data['q'], $searchby, $condition, $phrases = null);
                //$results =$datas;
            } else {
                $array2 = $this->searchFileLocal($query);
            }

            if (!empty($array2)) {
                foreach ($array2 as $key => $item) {
                    if (!empty($excludeCategory) && in_array($item->catid, $excludeCategory)) {
                        unset($array2[$key]);
                    }
                }
            }

            if (is_array($array1) && is_array($array2)) {
                $results = array_merge($array1, $array2);
            } elseif (count($array1) > 0 && !is_array($array2)) {
                $results = $array1;
            } elseif (!is_array($array1) && count($array2) > 0) {
                $results = $array2;
            } else {
                $results = null;
            }
        }

        //add link download and linkviewer for file
        if ($results !== null) {
            JLoader::import('joomla.application.component.model');
            $path_site_models = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR;
            $path_site_models .= 'com_dropfiles' . DIRECTORY_SEPARATOR . 'models';
            JModelLegacy::addIncludePath($path_site_models);

            $catModel = JModelLegacy::getInstance('Frontcategory', 'DropfilesModel');

            $user = JFactory::getUser();
            $groups = $user->getAuthorisedViewLevels();
            $modelConfig = JModelLegacy::getInstance('Frontconfig', 'DropfilesModel');

            foreach ($results as $k => $file) {
                $fileCat = $catModel->getCategory($file->catid);

                if ($params->get('categoryrestriction', 'accesslevel') === 'accesslevel') {
                    if (isset($fileCat->access)) {
                        if (!in_array($fileCat->access, $groups)) {
                            unset($results[$k]);
                            continue;
                        }
                    }
                } else {
                    $catparams = $modelConfig->getParams($file->catid);
                    $usergroup = isset($catparams->params->usergroup) ? $catparams->params->usergroup : array();
                    $user = JFactory::getUser();
                    $result = array_intersect($user->getAuthorisedGroups(), $usergroup);
                    if (!count($result)) {
                        unset($results[$k]);
                        continue;
                    }
                }

                if (!DropfilesFilesHelper::isUserCanViewFile($file) && JFactory::getApplication()->isClient('site')) {
                    unset($results[$k]);
                    continue;
                }

                $category = new stdClass();
                $category->id = $file->catid;
                $category->title = (isset($file->cattitle)) ? $file->cattitle : '';
                DropfilesFilesHelper::addInfosToFile($file, $category);
            }
        } else {
            return null;
        }

        //order results
        $app = JFactory::getApplication();
        $ordering = $app->input->getString('ordering', 'title');
        $dir = $app->input->getString('dir', 'asc');
        if (in_array($ordering, array('type', 'title', 'created', 'updated', 'cat'))) {
            switch ($ordering) {
                case 'type':
                    if ($dir === 'desc') {
                        usort($results, array('DropfilesModelFrontsearch', 'cmpTypeDesc'));
                    } else {
                        usort($results, array('DropfilesModelFrontsearch', 'cmpType'));
                    }
                    break;
                case 'created':
                case 'updated':
                    if ($dir === 'desc') {
                        usort($results, array('DropfilesModelFrontsearch', 'cmpCreatedDesc'));
                    } else {
                        usort($results, array('DropfilesModelFrontsearch', 'cmpCreated'));
                    }
                    break;

                case 'cat':
                    if ($dir === 'desc') {
                        usort($results, array('DropfilesModelFrontsearch', 'cmpCatDesc'));
                    } else {
                        usort($results, array('DropfilesModelFrontsearch', 'cmpCat'));
                    }
                    break;
                case 'title':
                default:
                    if ($dir === 'desc') {
                        usort($results, array('DropfilesModelFrontsearch', 'cmpTitleDesc'));
                    } else {
                        usort($results, array('DropfilesModelFrontsearch', 'cmpTitle'));
                    }
                    break;
            }
        }

        return $results;
    }


    /**
     * Method get latest files
     *
     * @param array $data_cat Data category
     * @param array $data     Data
     *
     * @return array
     */
    public function getLatestFiles($data_cat, $data)
    {
        $dbo         = $this->getDbo();
        $results_all = array();
        $nullDate    = $dbo->quote($dbo->getNullDate());
        $date        = JFactory::getDate();
        $nowDate     = $dbo->quote($date->toSql());
        $params      = JComponentHelper::getParams('com_dropfiles');
        $user        = JFactory::getUser();
        $groups      = $user->getAuthorisedViewLevels();
        JModelLegacy::addIncludePath(JPATH_ROOT . '/components/com_dropfiles/models/', 'DropfilesModelFrontconfig');
        $modelConfig = JModelLegacy::getInstance('Frontconfig', 'DropfilesModel');

        foreach ($data_cat as $key => $catid) {
            $cloud_cond = array();

            $condition = 'a.catid = c.id ';
            $conditionGoogle = '1=1';
            $conditionDropbox = '1=1';
            $cloud_cond[] = "mimeType != 'application/vnd.google-apps.folder' and trashed =false";
            $limit = ' LIMIT ' . $data['fCount'] . ' ';

            if ($catid !== '' && is_numeric($catid)) {
                $catChilds = $this->getCatChilds($catid);
                if (!$catChilds) {
                    $catChilds = array($catid);
                }
                $condition .= ' AND a.catid IN (' . implode(',', $catChilds) . ')';// $catid;
            }

            $condition .= ' AND (a.publish = ' . $nullDate . ' OR a.publish <= ' . $nowDate . ')';
            $condition .= ' AND (a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')';
            $condition .= ' AND a.state = 1';

            $conditionGoogle .= ' AND (a.publish = ' . $nullDate . ' OR a.publish <= ' . $nowDate . ')';
            $conditionGoogle .= ' AND (a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')';
            $conditionGoogle .= ' AND a.state = 1';

            $conditionDropbox .= ' AND (a.publish = ' . $nullDate . ' OR a.publish <= ' . $nowDate . ')';
            $conditionDropbox .= ' AND (a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')';
            $conditionDropbox .= ' AND a.state = 1';

            $query = 'SELECT a.*,c.title as cattitle FROM #__dropfiles_files as a JOIN #__categories as c ';
            $query .= ' ON a.catid = c.id WHERE ' . $condition . ' ORDER BY ' . $data['fOrderType'] . ' DESC ' . $limit;

            if (is_numeric($catid)) {
                $results = $this->searchFileLocal($query);
                // mutile category
                $catParams = $modelConfig->getParams($catid);
                $subparams   = (array) $catParams->params;
                if (!empty($subparams) && isset($subparams['refToFile'])) {
                    $listCatRef = $subparams['refToFile'];
                    $modelFiles = JModelLegacy::getInstance('Frontfiles', 'DropfilesModel');
                    $lstAllFile = array();
                    foreach ($listCatRef as $key => $value) {
                        if (is_array($value) && !empty($value)) {
                            $lstFile = $modelFiles->getFilesRef($key, $value, '', '');
                            $lstAllFile = array_merge($lstFile, $lstAllFile);
                        }
                    }
                    foreach ($lstAllFile as $item) {
                        $item->cattitle = $item->category_title;
                    }

                    $results = array_merge($results, $lstAllFile);
                    if ($data['fOrderType'] && $data['fDer']) {
                        JLoader::register('DropfilesHelper', JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/dropfiles.php');
                        $results = DropfilesHelper::orderingMultiCategoryFiles($results, $data['fOrderType'], $data['fDer']);
                    }
                }
            } elseif (is_string($catid) && $catid !== '') {
                $cat_typle = $this->getOneCatTypeByCloudID($catid);
                $results = array();
                //is dropbox category
                if ($cat_typle === 'dropbox') {
                    $conditionDropbox .= ' AND a.catid = ' . $dbo->quote($catid) . '';

                    $queryDropbox = 'SELECT a.* FROM #__dropfiles_dropbox_files as a ';
                    $queryDropbox .= '  WHERE ' . $conditionDropbox . ' ORDER BY ' . $data['fOrderType'];
                    $queryDropbox .= ' DESC ' . $limit;
                    $results = $this->searchFileLocal($queryDropbox);
                    if (!empty($results)) {
                        foreach ($results as $item) {
                            $item->id = $item->file_id;
                            $item->catid = $this->getOneCatLocalByCloudID($item->catid);
                            if (!$item->catid) {
                                continue;
                            }
                            $cattegory = $this->getCat($item->catid);
                            $item->cattitle = $cattegory->title;
                        }
                    }
                } elseif ($cat_typle === 'googledrive') {
                    $conditionGoogle .= ' AND a.catid = ' . $dbo->quote($catid) . '';

                    $queryGoogle = 'SELECT a.* FROM #__dropfiles_google_files as a ';
                    $queryGoogle .= ' WHERE ' . $conditionGoogle . ' ORDER BY ' . $data['fOrderType'];
                    $queryGoogle .= ' DESC ' . $limit;
                    $results = $this->searchFileLocal($queryGoogle);
                    if (!empty($results)) {
                        foreach ($results as $item) {
                            $item->id = $item->file_id;
                            $item->catid = $this->getOneCatLocalByCloudID($item->catid);
                            if (!$item->catid) {
                                continue;
                            }
                            $cattegory = $this->getCat($item->catid);
                            $item->cattitle = $cattegory->title;
                        }
                    }
                } elseif ($cat_typle === 'onedrive') {
                    $conditionDropbox .= ' AND a.catid = ' . $dbo->quote($catid) . '';

                    $queryOnedrive = 'SELECT a.* FROM #__dropfiles_onedrive_files as a';
                    $queryOnedrive .= ' WHERE ' . $conditionDropbox . ' ORDER BY ' . $data['fOrderType'];
                    $queryOnedrive .= ' DESC ' . $limit;

                    $result = $this->searchFileLocal($queryOnedrive);

                    if (!empty($result)) {
                        foreach ($result as $item) {
                            $item->id = $item->file_id;
                            $item->catid = $this->getOneCatLocalByCloudID($item->catid);
                            if (!$item->catid) {
                                continue;
                            }
                            $cattegory = $this->getCat($item->catid);
                            $item->cattitle = $cattegory->title;
                        }
                    }
                }
            } else {
                //all categories
                $array1 = array();
                if ($params->get('google_credentials', '')) {
                    $queryGoogle = 'SELECT a.* FROM #__dropfiles_google_files as a ';
                    $queryGoogle .= ' WHERE ' . $conditionGoogle . ' ORDER BY ' . $data['fOrderType'];
                    $queryGoogle .= ' DESC ' . $limit;

                    $array1 = $this->searchFileLocal($queryGoogle);

                    if (!empty($array1)) {
                        foreach ($array1 as $item) {
                            $item->id = $item->file_id;
                            $item->catid = $this->getOneCatLocalByCloudID($item->catid);
                            if (!$item->catid) {
                                continue;
                            }
                            $cattegory = $this->getCat($item->catid);
                            $item->cattitle = $cattegory->title;
                        }
                    }
                }

                $queryDropbox = 'SELECT a.* FROM #__dropfiles_dropbox_files as a ';
                $queryDropbox .= ' WHERE ' . $conditionDropbox . ' ORDER BY ' . $data['fOrderType'] . ' DESC ' . $limit;
                $resultsDropbox = $this->searchFileLocal($queryDropbox);
                if (!empty($resultsDropbox)) {
                    foreach ($resultsDropbox as $item) {
                        $item->id = $item->file_id;
                        $item->catid = $this->getOneCatLocalByCloudID($item->catid);
                        if (!$item->catid) {
                            continue;
                        }
                        $cattegory = $this->getCat($item->catid);
                        $item->cattitle = $cattegory->title;
                    }
                }

                $resultOnedrive = array();
                if ($params->get('onedriveCredentials', '')) {
                    $queryOnedrive = 'SELECT a.* FROM #__dropfiles_onedrive_files as a ';
                    $queryOnedrive .= '  WHERE ' . $conditionDropbox . ' ORDER BY ' . $data['fOrderType'];
                    $queryOnedrive .= ' DESC ' . $limit;

                    $resultOnedrive = $this->searchFileLocal($queryOnedrive);

                    if (!empty($resultOnedrive)) {
                        foreach ($resultOnedrive as $item) {
                            $item->id = $item->file_id;
                            $item->catid = $this->getOneCatLocalByCloudID($item->catid);
                            if (!$item->catid) {
                                continue;
                            }
                            $cattegory = $this->getCat($item->catid);
                            $item->cattitle = $cattegory->title;
                        }
                    }
                }

                $array1 = array_merge($array1, $resultsDropbox);
                $array1 = array_merge($array1, $resultOnedrive);

                $array2 = $this->searchFileLocal($query);

                if (is_array($array1) && is_array($array2)) {
                    $results = array_merge($array1, $array2);
                } elseif (count($array1) > 0 && !is_array($array2)) {
                    $results = $array1;
                } elseif (!is_array($array1) && count($array2) > 0) {
                    $results = $array2;
                } else {
                    $results = array();
                }
            }

            //add link download and linkviewer for file
            if (!empty($results)) {
                JLoader::import('joomla.application.component.model');
                $path_site_models = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR;
                $path_site_models .= 'com_dropfiles' . DIRECTORY_SEPARATOR . 'models';
                JModelLegacy::addIncludePath($path_site_models);

                $catModel = JModelLegacy::getInstance('Frontcategory', 'DropfilesModel');
                $modelConfig = JModelLegacy::getInstance('Frontconfig', 'DropfilesModel');

                foreach ($results as $k => $file) {
                    $fileCat = $catModel->getCategory($file->catid);

                    if ($params->get('categoryrestriction', 'accesslevel') === 'accesslevel') {
                        if (!in_array($fileCat->access, $groups)) {
                            unset($results[$k]);
                            continue;
                        }
                    } else {
                        $catparams = $modelConfig->getParams($file->catid);
                        $usergroup = isset($catparams->params->usergroup) ? $catparams->params->usergroup : array();

                        $result = array_intersect($user->getAuthorisedGroups(), $usergroup);
                        if (!count($result)) {
                            unset($results[$k]);
                            continue;
                        }
                    }


                    $category = new stdClass();
                    $category->id = $file->catid;
                    $category->title = $file->cattitle;
                    DropfilesFilesHelper::addInfosToFile($file, $category);
                }
            }

            $results_all = array_merge($results_all, $results);
        }

        $results_all = array_slice($results_all, 0, (int)$data['fCount']);
        //order results
        $ordering = $data['fOrdering'];
        $dir = $data['fDer'];
        if (in_array($ordering, array('uploaded', 'updated', 'downloaded', 'size'))) {
            switch ($ordering) {
                case 'uploaded':
                    if ($dir === 'desc') {
                        usort($results_all, array('DropfilesModelFrontsearch', 'cmpCreatedDesc'));
                    } else {
                        usort($results_all, array('DropfilesModelFrontsearch', 'cmpCreated'));
                    }
                    break;

                case 'updated':
                    if ($dir === 'desc') {
                        usort($results_all, array('DropfilesModelFrontsearch', 'cmpUpdatedDesc'));
                    } else {
                        usort($results_all, array('DropfilesModelFrontsearch', 'cmpUpdated'));
                    }
                    break;

                case 'downloaded':
                    if ($dir === 'desc') {
                        usort($results_all, array('DropfilesModelFrontsearch', 'cmpHitDesc'));
                    } else {
                        usort($results_all, array('DropfilesModelFrontsearch', 'cmpHit'));
                    }
                    break;

                case 'size':
                    if ($dir === 'desc') {
                        usort($results_all, array('DropfilesModelFrontsearch', 'cmpSizeDesc'));
                    } else {
                        usort($results_all, array('DropfilesModelFrontsearch', 'cmpSize'));
                    }
                    break;

                default:
                    if ($dir === 'desc') {
                        usort($results_all, array('DropfilesModelFrontsearch', 'cmpTitleDesc'));
                    } else {
                        usort($results_all, array('DropfilesModelFrontsearch', 'cmpTitle'));
                    }
                    break;
            }
        }

        return $results_all;
    }


    /**
     * Method compare type
     *
     * @param object $a Object a
     * @param object $b Object b
     *
     * @return integer
     */
    private function cmpType($a, $b)
    {
        if ($a->ext === $b->ext) {
            return strcmp($a->title, $b->title);
        }

        return strcmp($a->ext, $b->ext);
    }

    /**
     * Method compare type Desc
     *
     * @param object $a Object a
     * @param object $b Object b
     *
     * @return integer
     */
    private function cmpTypeDesc($a, $b)
    {
        if ($a->ext === $b->ext) {
            return strcmp($a->title, $b->title);
        }

        return strcmp($b->ext, $a->ext);
    }

    /**
     * Method compare created
     *
     * @param object $a Object a
     * @param object $b Object b
     *
     * @return integer
     */
    private function cmpCreated($a, $b)
    {
        return (strtotime($a->created_time) < strtotime($b->created_time)) ? -1 : 1;
    }

    /**
     * Method compare created desc
     *
     * @param object $a Object a
     * @param object $b Object b
     *
     * @return integer
     */
    private function cmpCreatedDesc($a, $b)
    {
        return (strtotime($a->created_time) > strtotime($b->created_time)) ? -1 : 1;
    }

    /**
     * Method compare updated
     *
     * @param object $a Object a
     * @param object $b Object b
     *
     * @return integer
     */
    private function cmpUpdated($a, $b)
    {
        return (strtotime($a->modified_time) < strtotime($b->modified_time)) ? -1 : 1;
    }

    /**
     * Method compare update
     *
     * @param object $a Object a
     * @param object $b Object b
     *
     * @return integer
     */
    private function cmpUpdatedDesc($a, $b)
    {
        return (strtotime($a->modified_time) > strtotime($b->modified_time)) ? -1 : 1;
    }


    /**
     * Method compare cat
     *
     * @param object $a Object a
     * @param object $b Object b
     *
     * @return integer
     */
    private function cmpCat($a, $b)
    {
        return strcmp($b->cattitle, $a->cattitle);
    }

    /**
     * Method compare cat desc
     *
     * @param object $a Object a
     * @param object $b Object b
     *
     * @return integer
     */
    private function cmpCatDesc($a, $b)
    {
        return strcmp($a->cattitle, $b->cattitle);
    }

    /**
     * Method compare title
     *
     * @param object $a Object a
     * @param object $b Object b
     *
     * @return integer
     */
    private function cmpTitle($a, $b)
    {
        return strcmp($a->title, $b->title);
    }

    /**
     * Method compare title desc
     *
     * @param object $a Object a
     * @param object $b Object b
     *
     * @return integer
     */
    private function cmpTitleDesc($a, $b)
    {
        return strcmp($b->title, $a->title);
    }

    /**
     * Method compare size
     *
     * @param object $a Object a
     * @param object $b Object b
     *
     * @return integer
     */
    private function cmpSize($a, $b)
    {
        return strcmp($a->size, $b->size);
    }

    /**
     * Method compare size desc
     *
     * @param object $a Object a
     * @param object $b Object b
     *
     * @return integer
     */
    private function cmpSizeDesc($a, $b)
    {
        return strcmp($b->size, $a->size);
    }

    /**
     * Method compare hits
     *
     * @param object $a Object a
     * @param object $b Object b
     *
     * @return boolean
     */
    private function cmpHit($a, $b)
    {
        return ($a->hits > $b->hits);
    }

    /**
     * Method compare hits desc
     *
     * @param object $a Object a
     * @param object $b Object b
     *
     * @return boolean
     */
    private function cmpHitDesc($a, $b)
    {
        return ($b->hits < $a->hits);
    }

    /**
     * Method search file local
     *
     * @param string $query Query string
     *
     * @return mixed
     */
    public function searchFileLocal($query)
    {
        //search file on local
        $dbo = $this->getDbo();
        $dbo->setQuery($query);
        $dbo->execute();
        $results = $dbo->loadObjectList();

        return $results;
    }

    /**
     *  Method search file cloud
     *
     * @param array $cloud_cond Cloud condition
     * @param array $data       Data
     * @param array $params     Params
     *
     * @return array
     */
    public function searchFileCloud($cloud_cond, $data, $params)
    {
        //search file on cloud
        $path_dropfilesgoogle = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesGoogle.php';
        JLoader::register('DropfilesGoogle', $path_dropfilesgoogle);
        $google = new DropfilesGoogle();
        $q_tmp = '';
        if ($data['catid'] !== '' && is_string($data['catid'])) {
            $q_tmp = " '" . $data['catid'] . "' in parents";
            $folders = $google->getListFolder($data['catid']);
            if (count($folders)) {
                $q_tmp = '(' . $q_tmp;
                foreach ($folders as $id => $folder) {
                    $q_tmp .= " or '" . $id . "' in parents ";
                }
                $q_tmp .= ')';
            }
        } else {
            $q1 = '(';
            $q_tmp .= rtrim($google->searchCondition($params, $q1), 'or') . ')';
        }
        $cloud_cond[] = $q_tmp;
        $q = implode(' and ', $cloud_cond);
        $results = $google->getAllFilesInAppFolder($q);
        if (is_array($results)) {
            foreach ($results as $key => $value) {
                $parentInfo = $google->getParentInfo($value->id);
                $value->cattitle = $parentInfo['title'];
                $value->catid = $this->getOneCatLocalByCloudID($parentInfo['id']);
                if (!$value->catid) {
                    continue;
                }
            }
        }

        return $results;
    }

    /**
     * Method view file
     *
     * @param array $match Match category
     *
     * @return string
     * @since  version
     */
    public function viewFile($match)
    {
        jimport('joomla.application.component.model');
        JLoader::register('DropfilesBase', JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesBase.php');
        $path_dropfilesgoogle = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesGoogle.php';
        JLoader::register('DropfilesGoogle', $path_dropfilesgoogle);
        JModelLegacy::addIncludePath(JPATH_ROOT . '/components/com_dropfiles/models/', 'DropfilesModelFrontfile');
        JModelLegacy::addIncludePath(JPATH_ROOT . '/components/com_dropfiles/models/', 'DropfilesModelFrontconfig');
        DropfilesBase::loadLanguage();

        $modelFile     = JModelLegacy::getInstance('Frontfile', 'dropfilesModel');
        $modelConfig   = JModelLegacy::getInstance('Frontconfig', 'dropfilesModel');
        $modelCategory = JModelLegacy::getInstance('Frontcategory', 'dropfilesModel');

        preg_match('@.*data\-dropfilesfilecategory="([0-9]+)".*@', $match[0], $matchCat);

        if (!empty($matchCat)) {
            $category = $modelCategory->getCategory((int)$matchCat[1]);
            if (!$category) {
                return '';
            }
        } else {
            $file = $modelFile->getFile((int)$match[1]);
            if ($file === null) {
                return '';
            }
            $category = $modelCategory->getCategory($file->catid);
            if (!$category) {
                return '';
            }
        }

        if ($category->type === 'googledrive') {
            $google = new DropfilesGoogle();
            $file = $google->getFileInfos($match[1], $category->cloud_id);
        } else {
            $file = $modelFile->getFile((int)$match[1]);
        }
        $file = DropfilesFilesHelper::addInfosToFile(json_decode(json_encode($file), false), $category);

        // Access check already done in category model
        $catmod = JCategories::getInstance('Dropfiles');
        $jcategory = $catmod->get($category->id);
        if (!$jcategory) {
            return '';
        }

        $params = $modelConfig->getParams($jcategory->id);

        if ($this->context === 'com_finder.indexer') {
            $theme = 'indexer';
        } else {
            if (!empty($params)) {
                $theme = $params->theme;
            } else {
                $theme = 'default';
            }
        }

        JPluginHelper::importPlugin('dropfilesthemes');
        $app = JFactory::getApplication();
        $result = $app->triggerEvent('onShowFrontFile', array(
            array(
                'file'     => $file,
                'category' => $category,
                'params'   => $params->params,
                'theme'    => $theme
            )
        ));

        if (!empty($result[0])) {
            $componentParams = JComponentHelper::getParams('com_dropfiles');
            if ((int) $componentParams->get('usegoogleviewer', 1) === 1) {
                $doc = JFactory::getDocument();
                $path_dropfilesbase = JPATH_ADMINISTRATOR . '/components/com_droppics/classes/dropfilesBase.php';
                JLoader::register('DropfilesBase', $path_dropfilesbase);

                JHtml::_('jquery.framework');

                $doc->addScript(JURI::base('true') . '/components/com_dropfiles/assets/js/jquery.colorbox-min.js');
                $doc->addScript(JURI::base('true') . '/components/com_dropfiles/assets/js/colorbox.init.js');
                $doc->addStyleSheet(JURI::base('true') . '/components/com_dropfiles/assets/css/colorbox.css');
            }

            return $result[0];
        }
        return '';
    }
}
