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

defined('_JEXEC') || die;


/**
 * Class DropfilesModelFts
 */
class DropfilesModelFts extends JModelLegacy
{
    /**
     * Max word length
     *
     * @var integer
     */
    public $maxWordLength = 32;

    /**
     * Table lock time
     *
     * @var integer
     */
    public $lockTime = 300; // 5min

    /**
     * Index error
     *
     * @var string
     */
    public $indexError = '';

    /**
     * Stop words
     *
     * @var array
     */
    protected $stopWords = array();

    /**
     * Log
     *
     * @var array
     */
    protected $log = array();

    /**
     * Is table lock
     *
     * @var boolean
     */
    protected $isLock = true;

    /**
     * Create tables
     *
     * @return boolean
     * @since  5.0.5
     */
    public function ftsCreateTables()
    {
        $db = JFactory::getDbo();
        $success = true;
        $schemes = $this->getDbScheme();
        foreach ($schemes as $table => $query) {
            $q = 'DROP TABLE IF EXISTS ' . $db->quoteName('#__dropfiles_fts_' . $table) . ';';
            $db->setQuery($q);
            $db->execute();

            $db->setQuery($query['create']);
            $db->execute();
        }

        return $success;
    }

    /**
     * Get database scheme
     *
     * @return array
     * @since  5.0.5
     */
    private function getDbScheme()
    {
        $db = JFactory::getDbo();
        $collate = "COLLATE='utf8_general_ci' ENGINE=MyISAM AUTO_INCREMENT=1;";
        $dbScheme = array(
            'docs' => array(
                'cols' => array(
                    // name => type, isnull, keys, default, extra
                    'id' => array('int(10) unsigned', 'NO', 'PRI', null, 'auto_increment'),
                    'index_id' => array('int(10) unsigned', 'NO', 'MUL'),
                    'token' => array('varchar(255)', 'NO', 'MUL'),
                    'n' => array('int(10) unsigned', 'NO'),
                ),
                'index' => array(
                    'PRIMARY' => array(0, 'id'),
                    'token' => array(1, 'token'),
                    'index_id' => array(1, 'index_id'),
                ),
                'create' => 'CREATE TABLE `#__dropfiles_fts_docs` (
                                `id` int(10) unsigned NOT NULL auto_increment,
                                `index_id` int(10) unsigned NOT NULL,
                                `token` varchar(255) NOT NULL,
                                `n` int(10) unsigned NOT NULL,
                                PRIMARY KEY  (`id`),
                                KEY `token` (`token`),
                                KEY `index_id` USING BTREE (`index_id`)
                            ) ' . $collate,
            ),
            'index' => array(
                'cols' => array(
                    'id' => array('int(10) unsigned', 'NO', 'PRI', null, 'auto_increment'),
                    'tid' => array('bigint(10) unsigned', 'NO', 'MUL'),
                    'tsrc' => array('varchar(255)', 'NO', 'MUL'),
                    'tdt' => array('datetime', 'NO', '', '0000-00-00 00:00:00'),
                    'build_time' => array('int(11)', 'NO', 'MUL', '0'),
                    'update_dt' => array('datetime', 'NO', '', '0000-00-00 00:00:00'),
                    'force_rebuild' => array('tinyint(4)', 'NO', 'MUL', '0'),
                    'locked_dt' => array('datetime', 'NO', 'MUL', '0000-00-00 00:00:00')
                ),
                'index' => array(
                    'PRIMARY' => array(0, 'id'),
                    'tid_tsrc_unique' => array(0, 'tid,tsrc'),
                    'tid' => array(1, 'tid'),
                    'build_time' => array(1, 'build_time'),
                    'force_rebuild' => array(1, 'force_rebuild'),
                    'locked_dt' => array(1, 'locked_dt'),
                    'tsrc' => array(1, 'tsrc')
                ),
                'create' => 'CREATE TABLE `#__dropfiles_fts_index` (
                                `id` int(10) unsigned NOT NULL auto_increment,
                                `tid` bigint(10) unsigned NOT NULL,
                                `tsrc` varchar(255) NOT NULL,
                                `tdt` datetime NOT NULL default \'0000-00-00 00:00:00\',
                                `build_time` int(11) NOT NULL default \'0\',
                                `update_dt` datetime NOT NULL default \'0000-00-00 00:00:00\',
                                `force_rebuild` tinyint(4) NOT NULL default \'0\',
                                `locked_dt` datetime NOT NULL default \'0000-00-00 00:00:00\',
                                PRIMARY KEY  (`id`),
                                UNIQUE KEY `tid_tsrc_unique` USING BTREE (`tid`,`tsrc`),
                                KEY `tid` (`tid`),
                                KEY `build_time` (`build_time`),
                                KEY `force_rebuild` (`force_rebuild`),
                                KEY `locked_dt` (`locked_dt`),
                                KEY `tsrc` USING HASH (`tsrc`)
                            ) ' . $collate,
            ),
            'stops' => array(
                'cols' => array(
                    'id' => array('int(10) unsigned', 'NO', 'PRI', null, 'auto_increment'),
                    'word' => array('varchar(32)', 'NO', 'UNI'),
                ),
                'index' => array(
                    'PRIMARY' => array(0, 'id'),
                    'word' => array(0, 'word'),
                ),
                'create' => 'CREATE TABLE `#__dropfiles_fts_stops` (
                                `id` int(10) unsigned NOT NULL auto_increment,
                                `word` varchar(32) character set utf8 collate utf8_bin NOT NULL,
                                PRIMARY KEY  (`id`),
                                UNIQUE KEY `word` (`word`)
                            ) ' . $collate,
            ),
            'vectors' => array(
                'cols' => array(
                    'wid' => array('int(10) unsigned', 'NO', 'PRI'),
                    'did' => array('int(10) unsigned', 'NO', 'PRI'),
                    'f' => array('float(10,4)', 'NO', ''),
                ),
                'index' => array(
                    'wid_did' => array(0, 'wid,did'),
                    'wid' => array(1, 'wid'),
                    'did' => array(1, 'did'),
                ),
                'create' => 'CREATE TABLE `#__dropfiles_fts_vectors` (
                                `wid` int(10) unsigned NOT NULL,
                                `did` int(10) unsigned NOT NULL,
                                `f` float(10,4) NOT NULL,
                                UNIQUE KEY `wid` (`wid`,`did`),
                                KEY `wid_2` (`wid`),
                                KEY `did` (`did`)
                            ) ' . $collate,
            ),
            'words' => array(
                'cols' => array(
                    'id' => array('int(10) unsigned', 'NO', 'PRI', null, 'auto_increment'),
                    'word' => array('varchar(32)', 'NO', 'UNI'),
                ),
                'index' => array(
                    'PRIMARY' => array(0, 'id'),
                    'word' => array(0, 'word'),
                ),
                'create' => 'CREATE TABLE `#__dropfiles_fts_words` (
                                `id` int(10) unsigned NOT NULL auto_increment,
                                `word` varchar(32) character set utf8 collate utf8_bin NOT NULL,
                                PRIMARY KEY  (`id`),
                                UNIQUE KEY `word` (`word`)
                            ) ' . $collate,
            ),
        );

        // Make Mysql Db creation queries
        foreach ($dbScheme as $k => $d) {
            $s = 'CREATE TABLE ' . $db->quoteName('#__dropfiles_fts_' . $k) . ' (' . "\n";

            $cs = array();
            $ai = false;
            foreach ($d['cols'] as $kk => $dd) {
                $ss = '`' . $kk . '` ' . $dd[0] . ' ' . ($dd[1] === 'NO' ? 'NOT NULL' : 'NULL');
                if (isset($dd[3])) {
                    $ss .= ' default \'' . $dd[3] . '\'';
                }
                if ((isset($dd[4])) && ($dd[4] === 'auto_increment')) {
                    $ss .= ' auto_increment';
                    $ai = true;
                }
                $cs[] = $ss;
            }
            $iz = array();
            foreach ($d['index'] as $kk => $dd) {
                $ss = '';
                if ($kk === 'PRIMARY') {
                    $ss = 'PRIMARY KEY';
                } else {
                    if ($dd[0] === 0) {
                        $ss = 'UNIQUE KEY `' . $kk . '`';
                    } else {
                        $ss = 'KEY `' . $kk . '`';
                    }
                }
                $ws = explode(',', $dd[1]);
                $zz = array();
                foreach ($ws as $z) {
                    $zz[] = '`' . $z . '`';
                }
                $ss .= ' (' . implode(',', $zz) . ')';

                $iz[] = $ss;
            }
            $s .= implode(",\n", $cs);

            if (count($iz) > 0) {
                $s .= ",\n" . implode(",\n", $iz);
            }

            $s .= "\n" . ') ENGINE=MyISAM' . ($ai ? ' AUTO_INCREMENT=1' : '') . ' DEFAULT CHARSET=utf8';

            $dbScheme[$k]['create2'] = $s;
        }
        return $dbScheme;
    }

    /**
     * Load stop words
     *
     * @return void
     * @since  5.0.5
     */
    protected function loadStops()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select($db->quoteName('word'));
        $query->from($db->quotename('#__dropfiles_fts_stops'));
        $db->setQuery($query);
        $words = $db->loadColumn();

        $stops = array();

        foreach ($words as $word) {
            $stops[mb_strtolower($word, 'UTF-8')] = 1;
        }
        $this->stops = $stops;
    }

    /**
     * Log message
     *
     * @param string $message Message
     *
     * @return void
     * @since  5.0.5
     */
    protected function log($message)
    {
        $this->log[] = $message;
    }

    /**
     * Clear log
     *
     * @return void
     * @since  5.0.5
     */
    public function clearLog()
    {
        $this->log = array();
    }

    /**
     * Get log
     *
     * @return string
     * @since  5.0.5
     */
    public function getLog()
    {
        return implode("\n", $this->log);
    }

    /**
     * Split document content to array
     *
     * @param string $str File content
     *
     * @return mixed
     * @since  5.0.5
     */
    protected function splitToWords($str)
    {
        $str         = str_replace('-', ' ', $str);
        $pattern_str = "~([\x{00C0}-\x{1FFF}\x{2C00}-\x{D7FF}\w][\x{00C0}-\x{1FFF}\x{2C00}-\x{D7FF}\w'\-]*[\x{00C0}" .
            "-\x{1FFF}\x{2C00}-\x{D7FF}\w]+|[\x{00C0}-\x{1FFF}\x{2C00}-\x{D7FF}\w]+)~u";

        preg_match_all($pattern_str, $str, $matches);

        return $matches[1];
    }

    /**
     * Reindex file
     *
     * @param integer $indexId Index id
     * @param array   $chunks  Chunks to index
     *
     * @return boolean
     * @since  5.0.5
     */
    public function reIndex($indexId, $chunks)
    {
        $db = JFactory::getDbo();

        // Check chunks format
        if (!is_array($chunks)) {
            $this->log('Fts Models: Wrong chunks format!');
            return false;
        }

        foreach ($chunks as $key => $doc) {
            $query = $db->getQuery(true);
            $query->select($db->quoteName('id'))
                ->from($db->quoteName('#__dropfiles_fts_docs'))
                ->where(
                    $db->quoteName('index_id') . ' = "' . addslashes($indexId) .
                    '" AND ' . $db->quoteName('token') . ' = "' . addslashes($key) . '"'
                );
            $db->setQuery($query);
            $results = $db->loadObject();
            if (!isset($results->id)) {
                // Insert token record
                $docx           = new stdClass();
                $docx->id       = null;
                $docx->index_id = $indexId;
                $docx->token    = $key;
                $docx->n        = 0;
                $db->insertObject('#__dropfiles_fts_docs', $docx, 'id');
                $docId = $db->insertid();
            } else {
                $docId = $results->id;
            }

            $result2 = $this->add(array($docId => $doc));

            if (!$result2) {
                return false;
            }
        }

        return true;
    }

    /**
     * Add document to database
     *
     * @param array $docs Documents
     *
     * @return boolean
     * @since  5.0.5
     */
    public function add($docs = array())
    {
        $db = JFactory::getDbo();

        if (!is_array($docs)) {
            $this->log('Add doc line 101: Parameter should be an array');
            return false;
        }

        if (count($docs) < 1) {
            return false;
        }
        $aIds = array();
        foreach ($docs as $id => $doc) {
            if (!is_numeric($id)) {
                $this->log('Add document: bad index "' . $id . '" given.');
                return false;
            } else {
                $aIds[] = $id;
            }
        }

        $wordLists = array();
        $docLists  = array();

        foreach ($docs as $id => $doc) {
            if (!isset($doc) || (mb_strlen($doc, 'UTF-8') < 1)) {
                continue;
            }

            $words         = $this->splitToWords($doc);
            $numOfWords    = count($words);
            $docLists[$id] = $numOfWords;

            // Clean words, remove stop words, one char word and too long
            $word2 = array();

            foreach ($words as $word) {
                $len = mb_strlen($word, 'UTF-8');
                $lv = mb_strtolower($word, 'UTF-8');

                if (($len > 1) && ($len <= $this->maxWordLength) && (!isset($this->stops[$lv]))) {
                    if (!isset($word2[$lv])) {
                        $word2[$lv] = 1;
                    } else {
                        $word2[$lv]++;
                    }
                }
            }

            foreach ($word2 as $key => $value) {
                $wordLists[] = array($key, $id, $value / $numOfWords);
            }

            // Update record
            $word     = new stdClass();
            $word->id = $id;
            $word->n  = $numOfWords;
            $db->updateObject('#__dropfiles_fts_docs', $word, 'id');
        }

        // Insert words into words table
        $wordListChunks = array_chunk($wordLists, 1000);

        foreach ($wordListChunks as $doc) {
            $z = array();

            foreach ($doc as $dd) {
                $z[] = '("' . addslashes($dd[0]) . '")';
            }
            $query = 'INSERT IGNORE INTO `#__dropfiles_fts_words` (`word`) VALUE ' . implode(',', $z);
            $db->setQuery($query);

            if (!$db->execute()) {
                $this->log('Add document: can not add words to index data.');
//                $db->setQuery('FLUSH TABLES WITH READ LOCK');
//                $db->execute();
                return false;
            }
        }

        // lock the tables in case some other process remove a certain word
        // between step 0 and 1 and 2 and 3
//        if ($this->isLock) {
//            $query = 'lock tables `#__dropfiles_fts_vectors` write, `#__dropfiles_fts_words` write';
//
//            $db->setQuery($query);
//            if (!$db->execute($query)) {
//                // Disable locking
//                $this->isLock = false;
//                $db->setQuery('FLUSH TABLES WITH READ LOCK');
//                $db->execute();
//               // $this->log('Add document: Error when locking tables: '.$wpdb->last_error);
//                return false;
//            }
//        }

        // Remove old vectors
        $query = 'delete from `#__dropfiles_fts_vectors` where `did` in (' . implode(',', $aIds) . ')';
        $db->setQuery($query);
        if (!$db->execute()) {
            $this->log('Add document: Error when removing old vectors.');
            return false;
        }
        // Insert new vectors
        foreach ($wordLists as $d) {
            $query = 'INSERT IGNORE INTO `#__dropfiles_fts_vectors` (`wid`,`did`,`f`)
                    select 
                        id,
                        ' . $d[1] . ',
                        ' . $d[2] . '
                    from `#__dropfiles_fts_words`
                    where `word` = "' . addslashes($d[0]) . '"
                ';

            $db->setQuery($query);
            if (!$db->execute()) {
                $this->log('Add vectors: can not add vector.');
                return false;
            }
        }

        return true;
    }

    /**
     * Check and sync files
     *
     * @param string $currentBuildTime Current build time
     *
     * @return void
     * @since  5.0.5
     */
    public function checkAndSyncFiles($currentBuildTime)
    {
        $db = JFactory::getDbo();

        // Step 1. Mark index rows contains old posts and posts with wrong date of post or build time.
        $query = 'update `#__dropfiles_fts_index` wi
                left join `#__dropfiles_files` p
                    on p.id = wi.tid
                set 
                    wi.force_rebuild = if(p.id is null, 2, if ((wi.build_time = "';
        $query .= addslashes($currentBuildTime) . '") and (wi.tdt = p.modified_time), 0, 1))
                where 
                    (wi.tsrc = "df_files") and (wi.force_rebuild = 0)';
        $db->setQuery($query);
        $db->execute();

        // Step 2. Find and add new posts // @todo need to be optimized!
        $query = 'insert ignore into ' . $db->quoteName('#__dropfiles_fts_index') . " 
                (`tid`, `tsrc`, `tdt`, `build_time`, `update_dt`, `force_rebuild`, `locked_dt`) 
                select 
                    p.id tid,
                    'df_files' tsrc,
                    '0000-00-00 00:00:00' tdt,
                    0 build_time,
                    '0000-00-00 00:00:00' update_dt,
                    1 force_rebuild,
                    '0000-00-00 00:00:00' locked_dt
                    from " . $db->quoteName('#__dropfiles_files') . ' p
                ';
        $db->setQuery($query);
        $db->execute();
        // Step 3. What else?
    }

    /**
     * Get status to display
     *
     * @return array
     * @since  5.0.5
     */
    public function getStatus()
    {
        $db = JFactory::getDbo();

        $prefix = '#__dropfiles_fts_';
        $tables = $db->getTableList();
        if (!in_array($db->getPrefix() . 'dropfiles_fts_index', $tables)) {
            return array(
                'n_inindex' => 0,
                'n_actual' => 0,
                'n_pending' => 0,
                'message' => 'Index not ready! Please rebuild it!'
            );
        }

        $query = 'select 
                sum(if (build_time != 0, 1, 0)) n_inindex, 
                sum(if ((force_rebuild = 0) and (build_time != 0), 1, 0)) n_actual,
                sum(if ((force_rebuild != 0) or (build_time = 0), 1, 0)) n_pending
            from `' . $prefix . 'index` 
            where tsrc = "df_files"';
        $db->setQuery($query);
        $res = $db->loadObjectList();

        if (isset($res[0]->n_inindex)) {
            $ret = array(
                'n_inindex' => intval($res[0]->n_inindex),
                'n_actual' => intval($res[0]->n_actual),
                'n_pending' => intval($res[0]->n_pending),
            );
        } else {
            $ret = array(
                'n_inindex' => 0,
                'n_actual' => 0,
                'n_pending' => 0,
            );
        }

        return $ret;
    }

    /**
     * Update index data
     *
     * @param integer $id   Index id
     * @param array   $data Data
     *
     * @return mixed
     * @since  5.0.5
     */
    public function updateRecordData($id, $data = array())
    {
        $db = JFactory::getDbo();
        $prefix = '#__dropfiles_fts_';
        $a = new stdClass();
        $a->id = $id;
        foreach ($data as $key => $value) {
            if (in_array($key, array('tdt', 'build_time', 'update_dt', 'force_rebuild', 'locked_dt'))) {
                $a->{$key} = $value;
            }
        }
        $db->updateObject($prefix . 'index', $a, 'id');
        return $db->insertid();
    }

    /**
     * Unlock row
     *
     * @param integer $id Row id
     *
     * @return void
     * @since  5.0.5
     */
    public function unlockRecord($id)
    {
        $db           = JFactory::getDbo();
        $a            = new stdClass();
        $a->id        = $id;
        $a->locked_dt = '0000-00-00 00:00:00';
        $db->updateObject('#__dropfiles_fts_index', $a, 'id');
    }

    /**
     * Insert index record
     *
     * @param array $data Data
     *
     * @return mixed
     * @since  5.0.5
     */
    public function insertRecordData($data = array())
    {
        $db = JFactory::getDbo();

        $prefix = '#__dropfiles_fts_';

        $a = new stdClass();
        $a->id = null;
        foreach ($data as $key => $value) {
            if (in_array($key, array('tdt', 'build_time', 'update_dt', 'force_rebuild', 'locked_dt', 'tid', 'tsrc'))) {
                $a->{$key} = $value;
            }
        }

        $db->insertObject($prefix . 'index', $a);

        return $db->insertid();
    }

    /**
     * Update index record for file
     *
     * @param integer $fileId       File id
     * @param string  $modt         Modified time
     * @param string  $buildTime    Build time
     * @param boolean $time         Time
     * @param integer $forceRebuild Force rebuild
     *
     * @return mixed
     * @since  5.0.5
     */
    public function updateIndexRecordForPost($fileId, $modt, $buildTime, $time = false, $forceRebuild = 0)
    {

        $db = JFactory::getDbo();

        if ($time === false) {
            $time = time();
        }

        $q = 'select * from ' . $db->quoteName('#__dropfiles_fts_index') . ' where (`tid` = ' . $db->quote($fileId);
        $q .= ') and (`tsrc` = "df_files")';
        $db->setQuery($q);
        $res = $db->loadObjectList();

        if (isset($res[0])) {
            // Update existing record
            $this->updateRecordData(
                $res[0]->id,
                array(
                    'tdt' => $modt,
                    'build_time' => $buildTime,
                    'update_dt' => date('Y-m-d H:i:s', $time),
                    'force_rebuild' => $forceRebuild,
                    'locked_dt' => '0000-00-00 00:00:00',
                )
            );

            return $res[0]->id;
        } else {
            // Insert new record
            $insertedId = $this->insertRecordData(
                array(
                    'tid' => $fileId,
                    'tsrc' => 'df_files',
                    'tdt' => $modt,
                    'build_time' => $buildTime,
                    'update_dt' => date('Y-m-d H:i:s', $time),
                    'force_rebuild' => $forceRebuild,
                    'locked_dt' => '0000-00-00 00:00:00',
                )
            );

            return $insertedId;
        }
    }

    /**
     * Get record to rebuild
     *
     * @param integer $nMax Max record per request
     *
     * @return mixed
     * @since  5.0.5
     */
    public function getRecordsToRebuild($nMax = 1)
    {

        $db = JFactory::getDbo();
        $prefix = '#__dropfiles_fts_';

        $time = time();
        $time2 = date('Y-m-d H:i:s', $time - $this->lockTime);

        $q = 'select 
                    id, tid, tsrc 
            from `' . $prefix . 'index` 
            where 
                ((force_rebuild != 0) or (build_time = 0)) and 
                ((locked_dt = "0000-00-00 00:00:00") or (locked_dt < "' . $time2 . '"))
            order by build_time asc, id asc 
            limit ' . intval($nMax) . '';
        $db->setQuery($q);
        $results = $db->loadObjectList();

        return $results;
    }

    /**
     * Lock and unlock record
     *
     * @param integer $id Record id
     *
     * @return boolean
     * @since  5.0.5
     */
    public function lockUnlockedRecord($id)
    {
        $db = JFactory::getDbo();
        $prefix = '#__dropfiles_fts_';

        $time = time();
        $time2 = date('Y-m-d H:i:s', $time - $this->lockTime);
        $new_time = date('Y-m-d H:i:s', $time);

        $q = 'select id, if((locked_dt = "0000-00-00 00:00:00") or (locked_dt < "' . $time2;
        $q .= '"), 0, 1) islocked from `' . $prefix . 'index` where id = "' . addslashes($id) . '"';
        $db->setQuery($q);
        $res = $db->loadObjectList();

        if (isset($res[0])) {
            if ($res[0]->islocked) {
                // Already locked
                return false;
            } else {
                // Lock it
                $a = new stdClass();
                $a->id = $id;
                $a->locked_dt = $new_time;
                $db->updateObject($prefix . 'index', $a, 'id');
                return true;
            }
        } else {
            // Record not found
            return false;
        }
    }

    /**
     * Get column
     *
     * @param array  $a   Index table columns
     * @param string $col Column
     *
     * @return array
     * @since  5.0.5
     */
    public function getColumn($a, $col)
    {
        $r = array();
        foreach ($a as $d) {
            if (isset($d->{$col})) {
                $r[] = $d->{$col};
            }
        }
        return $r;
    }

    /**
     * Remove index record for file
     *
     * @param integer $fileId File id
     *
     * @return boolean
     * @since  5.0.5
     */
    public function removeIndexRecordForPost($fileId)
    {
        $db = JFactory::getDbo();
        $tables = $db->getTableList();
        if (!in_array($db->getPrefix() . 'dropfiles_fts_index', $tables)) {
            return false;
        }
        $prefix = '#__dropfiles_fts_';
        $q = 'select `id` from `' . $prefix . 'index` where (`tid` = "';
        $q .= addslashes($fileId) . '") and (`tsrc` = "df_files")';
        $db->setQuery($q);
        $indexResults = $db->loadObjectList();
        if (isset($indexResults[0])) {
            $q = 'select `id` from `' . $prefix;
            $q .= 'docs` where `index_id` in (' . implode(',', $this->getColumn($indexResults, 'id')) . ')';
            $db->setQuery($q);
            $docResults = $db->loadObjectList();
            if (isset($docResults[0])) {
                $q = 'delete from `' . $prefix . 'vectors` where `did` in (';
                $q .= implode(',', $this->getColumn($docResults, 'id')) . ')';
                $db->setQuery($q);
                $db->execute();

                $q = 'delete from `' . $prefix . 'docs` where `index_id` in (';
                $q .= implode(',', $this->getColumn($indexResults, 'id')) . ')';
                $db->setQuery($q);
                $db->execute();
            }

            $q = 'delete from `' . $prefix;
            $q .= 'index` where (`tid` = "' . addslashes($fileId) . '") and (`tsrc` = "df_files")';
            $db->setQuery($q);
            $db->execute();
        }

        return true;
    }

    /**
     * Reindex file
     *
     * @param integer $fileId        File id
     * @param boolean $isForceRemove Force remove index
     *
     * @return boolean
     * @since  5.0.5
     */
    public function dfPostReindex($fileId, $isForceRemove = false)
    {
        $res = $this->reIndexFile($fileId, $isForceRemove);
        if (!$res) {
            // trigger_error('Error reindex file ID=' . $fileId . ': ' . $this->indexError, E_USER_NOTICE);
            return false;
        }

        return true;
    }

    /**
     * Reindex file
     *
     * @param integer $fileId        File id
     * @param boolean $isForceRemove Force remove index
     *
     * @return boolean
     * @since  5.0.5
     */
    public function reIndexFile($fileId, $isForceRemove = false)
    {
        $db = JFactory::getDbo();
        $tables = $db->getTableList();
        if (!in_array($db->getPrefix() . 'dropfiles_fts_index', $tables)) {
            return false;
        }

        $query = $db->getQuery(true);
        $query->select($db->quoteName(array('id', 'title', 'description', 'ext', 'catid', 'file', 'modified_time')))
            ->from($db->quoteName('#__dropfiles_files'))
            ->where($db->quoteName('id') . ' = ' . $db->quote($fileId));
        $db->setQuery($query);
        $file = $db->loadObject();

        if ($file && (!$isForceRemove)) {
            // Insert or update index record
            $chunks = array(
                'title' => $file->title,
                'description' => $file->description
            );

            $chunks2 = $this->getChunksWithContent($chunks, $file);
            $modt = $file->modified_time;
            $time = time();
            $buildTime = $this->getParam('rebuild_time', 0);
            $insertedId = $this->updateIndexRecordForPost($fileId, $modt, $buildTime, $time, 0);
            $this->clearLog();
            $res = $this->reIndex($insertedId, $chunks2);
            $this->indexError = (!$res) ? 'Indexing error: ' . $this->getLog() : '';

            return $res;
        } else {
            // Check if index record exists and delete it
            $this->removeIndexRecordForPost($fileId);
            return true;
        }
    }

    /**
     * Get chunks with content
     *
     * @param array  $chunks Chunks array
     * @param object $file   File
     *
     * @return mixed
     * @since  5.0.5
     */
    public function getChunksWithContent($chunks, $file)
    {
        // Get file contents
        JLoader::register(
            'DropfilesDocumentsHelper',
            JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/documents.php'
        );
        $allowIndex = array('doc', 'docx', 'xls', 'xlsx', 'pdf', 'ppt', 'pptx', 'rtf', 'txt');

        if (in_array($file->ext, $allowIndex)) {
            $document = new DropfilesDocumentsHelper($file);
            try {
                $content = $document->getDocumentContent();
            } catch (Exception $e) {
                return $chunks;
            }
        }

        if (isset($content) && $content !== '') {
            $chunks['content'] = $content;
        }

        return $chunks;
    }

    /**
     * Get param
     *
     * @param string $key     Key
     * @param string $default Default
     *
     * @return mixed
     * @since  5.0.5
     */
    protected function getParam($key, $default)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('params')->from('#__extensions')->where('element = "com_dropfiles"');
        $db->setQuery((string) $query);
        $params  = new JRegistry;
        $results = $db->loadObject();
        if ($results) {
            $params->loadString($results->params, 'JSON');
        }

        //$params = JComponentHelper::getParams('com_dropfiles');
        return $params->get($key, $default);
    }
}
