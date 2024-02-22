<?php


use Joomla\CMS\Factory;
use PHPUnit\Framework\TestCase;

ini_set('display_errors', false);
error_reporting(E_ALL);
define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', dirname(__DIR__) . '/../../');

include_once(JPATH_BASE . 'includes/defines.php');
include_once(JPATH_BASE . 'includes/framework.php');
include_once(JPATH_SITE . '/components/com_emundus/unittest/helpers/samples.php');
include_once(JPATH_SITE . '/components/com_emundus/helpers/events.php');

jimport('joomla.user.helper');
jimport('joomla.application.application');
jimport('joomla.plugin.helper');

// set global config --> initialize Joomla Application with default param 'site'
JFactory::getApplication('site');

// set false ini_get('session.use_cookies') and set false headers_sent
!ini_get('session.use_cookies') && !headers_sent($file, $line);

// activate session
session_start();

class EmundusHelperEventsTest extends TestCase
{
    private $h_events;
    private $h_sample;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->h_events = new EmundusHelperEvents();
        $this->h_sample = new EmundusUnittestHelperSamples();
    }

    public function testFoo()
    {
        $foo = true;
        $this->assertSame(true, $foo);
    }

	protected static function callMethod($obj, $name, array $args) {
		$class = new \ReflectionClass($obj);
		$method = $class->getMethod($name);
		if (version_compare(PHP_VERSION, '7.4.0', '<'))
		{
			$method->setAccessible(true);
		}

		return $method->invokeArgs($obj, $args);
	}

    public function testCheckQcmCompleted()
    {
		// First we check if QCM module is installed
	    $this->installQCM();

	    $db = JFactory::getDbo();

		require_once JPATH_SITE . '/components/com_emundus/models/qcm.php';
		$m_qcm = new EmundusModelQcm();

	    $user_id = $this->h_sample->createSampleUser(9, 'userunittest' . rand(0, 1000) . '@emundus.test.fr');
	    $program = $this->h_sample->createSampleProgram();
	    $campaign_id = $this->h_sample->createSampleCampaign($program);
	    $fnum = $this->h_sample->createSampleFile($campaign_id, $user_id);

	    $forms    = EmundusHelperMenu::getUserApplicationMenu(9);
	    $forms_ids = array_column($forms, 'form_id');
	    $items_ids = [];
	    foreach($forms as $form) {
		    $items_ids[$form->form_id] = $form->id;
	    }

		// No QCM
		$qcm_completed = self::callMethod(
			$this->h_events,
			'checkQcmCompleted',
			array($fnum, $forms_ids, $items_ids)
		);
		$this->assertSame(true, $qcm_completed['status'] , 'No QCM is defined for the campaign so it should return true');

		$qcm_id = $this->createQCM(324,686);

		// QCM defined but not started
	    $qcm_completed = self::callMethod(
		    $this->h_events,
		    'checkQcmCompleted',
		    array($fnum, $forms_ids, $items_ids)
	    );
	    $this->assertSame(false, $qcm_completed['status'] , 'QCM is defined but applicant has not completed it yet so it should return false');

		$m_qcm->initQcmApplicant($fnum, $qcm_id);

	    $qcm_completed = self::callMethod(
		    $this->h_events,
		    'checkQcmCompleted',
		    array($fnum, $forms_ids, $items_ids)
	    );
	    $this->assertSame(false, $qcm_completed['status'] , 'QCM is defined, applicant started it but not complete so it should return false');

		// Answer first question
		$insert = [
			'date_time' => date('Y-m-d H:i:s'),
			'user' => $user_id,
			'fnum' => $fnum,
		];
		$insert = (object) $insert;
		$db->insertObject('#__emundus_qualifications', $insert);
		$qualification_id = $db->insertid();

		$insert = [
			'parent_id' => $qualification_id,
		];
		$insert = (object) $insert;
		$db->insertObject('#__emundus_qualifications_686_repeat', $insert);

	    $qcm_completed = self::callMethod(
		    $this->h_events,
		    'checkQcmCompleted',
		    array($fnum, $forms_ids, $items_ids)
	    );
	    $this->assertSame(false, $qcm_completed['status'] , 'QCM is defined, applicant answer only first question so it should return false');

		// Answer second question
	    $insert = [
		    'parent_id' => $qualification_id,
	    ];
	    $insert = (object) $insert;
	    $db->insertObject('#__emundus_qualifications_686_repeat', $insert);

	    $qcm_completed = self::callMethod(
		    $this->h_events,
		    'checkQcmCompleted',
		    array($fnum, $forms_ids, $items_ids)
	    );
	    $this->assertSame(true, $qcm_completed['status'] , 'QCM is defined, applicant completed it so it should return true');


		$query = $db->getQuery(true);
		$query->clear()
			->delete($db->quoteName('#__emundus_qualifications'))
			->where('id = ' . $qualification_id);
		$db->setQuery($query);
		$db->execute();

		$query->clear()
			->delete($db->quoteName('#__emundus_qualifications_686_repeat'))
			->where('parent_id = ' . $qualification_id);
		$db->setQuery($query);
		$db->execute();

	    $this->dropQCM();
    }

	private function createQCM($form_id,$group_id) {
		$db = JFactory::getDbo();

		// QCM
		$insert = [
			'date_time' => date('Y-m-d H:i:s'),
			'name' => 'QCM Test',
			'form_id' => $form_id,
			'count' => 2,
			'group_id' => $group_id,
			'type_choices' => '1'
		];
		$insert = (object) $insert;
		$db->insertObject('#__emundus_setup_qcm', $insert);
		$qcm_id = $db->insertid();

		// Sections
		$insert = [
			'date_time' => date('Y-m-d H:i:s'),
			'name' => 'Section Test'
		];
		$insert = (object) $insert;
		$db->insertObject('#__emundus_qcm_section', $insert);
		$qcm_section_id = $db->insertid();

		// Question 1
		$insert = [
			'date_time' => date('Y-m-d H:i:s'),
			'section' => $qcm_section_id,
			'code' => 'question_1',
			'question' => 'Question Test',
			'time' => 30,
			'answers' => 1
		];
		$insert = (object) $insert;
		$db->insertObject('#__emundus_qcm_questions', $insert);
		$question_id_1 = $db->insertid();

		// Proposals
		$insert = [
			'parent_id' => $question_id_1,
			'proposals' => 'Proposition 1'
		];
		$insert = (object) $insert;
		$db->insertObject('#__emundus_qcm_questions_765_repeat', $insert);
		$insert = [
			'parent_id' => $question_id_1,
			'proposals' => 'Proposition 2'
		];
		$insert = (object) $insert;
		$db->insertObject('#__emundus_qcm_questions_765_repeat', $insert);

		// Question 2
		$insert = [
			'date_time' => date('Y-m-d H:i:s'),
			'section' => $qcm_section_id,
			'code' => 'question_2',
			'question' => 'Question Test 2',
			'time' => 30,
			'answers' => 1
		];
		$insert = (object) $insert;
		$db->insertObject('#__emundus_qcm_questions', $insert);
		$question_id_2 = $db->insertid();

		// Proposals
		$insert = [
			'parent_id' => $question_id_2,
			'proposals' => 'Proposition 1'
		];
		$insert = (object) $insert;
		$db->insertObject('#__emundus_qcm_questions_765_repeat', $insert);
		$insert = [
			'parent_id' => $question_id_2,
			'proposals' => 'Proposition 2'
		];
		$insert = (object) $insert;
		$db->insertObject('#__emundus_qcm_questions_765_repeat', $insert);

		// QCM Repeat Questionid
		$insert = [
			'parent_id' => $qcm_id,
			'questionid' => $question_id_1
		];
		$insert = (object) $insert;
		$db->insertObject('#__emundus_setup_qcm_repeat_questionid', $insert);
		$insert = [
			'parent_id' => $qcm_id,
			'questionid' => $question_id_2
		];
		$insert = (object) $insert;
		$db->insertObject('#__emundus_setup_qcm_repeat_questionid', $insert);

		return $qcm_id;
	}

	private function dropQCM() {
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);

		$query->clear()
			->delete($db->quoteName('#__emundus_qcm_applicants'));
		$db->setQuery($query);
		$db->execute();

		$query->clear()
			->delete($db->quoteName('#__emundus_setup_qcm'));
		$db->setQuery($query);
		$db->execute();

		$query->clear()
			->delete($db->quoteName('#__emundus_qcm_section'));
		$db->setQuery($query);
		$db->execute();
	}

	private function installQCM() {
		try {
			$db = JFactory::getDbo();
			$tables = $db->setQuery('SHOW TABLES')->loadColumn();

			if(!in_array('jos_emundus_setup_qcm',$tables)) {
				$db->setQuery("create table jos_emundus_setup_qcm
                    (
                        id int auto_increment
                            primary key,
                        date_time datetime null,
                        name varchar(255) null,
                        form_id int(4) null,
                        count int null,
                        group_id int(4) null,
                        questionid int null,
                        sectionid int null,
                        type_choices varchar(255) null
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;")->execute();
				$db->setQuery("create index jos_emundus_setup_qcm_jos_fabrik_forms_id_fk on jos_emundus_setup_qcm (form_id);")->execute();
				$db->setQuery("create index jos_emundus_setup_qcm_jos_fabrik_groups_id_fk on jos_emundus_setup_qcm (group_id);")->execute();
				$db->setQuery("ALTER TABLE `jos_emundus_setup_qcm`
                    ADD CONSTRAINT jos_emundus_setup_qcm_ibfk_1 FOREIGN KEY (`form_id`) REFERENCES `jos_fabrik_forms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                    ADD CONSTRAINT jos_emundus_setup_qcm_ibfk_2 FOREIGN KEY (`group_id`) REFERENCES `jos_fabrik_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;")->execute();
			}

			if(!in_array('jos_emundus_qcm_section',$tables)) {
				$db->setQuery("create table jos_emundus_qcm_section
                    (
                        id int auto_increment
                            primary key,
                        date_time datetime null,
                        name varchar(255) null
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;")->execute();
			}

			if(!in_array('jos_emundus_qcm_questions',$tables)) {
				$db->setQuery("create table jos_emundus_qcm_questions
                    (
                        id int auto_increment
                            primary key,
                        date_time datetime null,
                        section int null,
                        code varchar(255) collate utf8mb4_unicode_ci null,
                        question mediumtext collate utf8mb4_unicode_ci null,
                        proposals varchar(255) collate utf8mb4_unicode_ci null,
                        time int null,
                        answers varchar(255) collate utf8mb4_unicode_ci null
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;")->execute();
				$db->setQuery("create index jos_emundus_qcm_questions_jos_emundus_qcm_section_id_fk
                    on jos_emundus_qcm_questions (section);")->execute();
				$db->setQuery("ALTER TABLE `jos_emundus_qcm_questions`
                    ADD CONSTRAINT jos_emundus_qcm_questions_ibfk_1 FOREIGN KEY (`section`) REFERENCES `jos_emundus_qcm_section` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;")->execute();
			}

			if(!in_array('jos_emundus_qcm_questions_765_repeat',$tables)) {
				$db->setQuery("create table jos_emundus_qcm_questions_765_repeat
                    (
                        id int auto_increment
                            primary key,
                        parent_id int null,
                        proposals varchar(255) null
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;")->execute();
				$db->setQuery("create index fb_parent_fk_parent_id_INDEX
                    on jos_emundus_qcm_questions_765_repeat (parent_id);")->execute();
				$db->setQuery("ALTER TABLE `jos_emundus_qcm_questions_765_repeat`
                    ADD CONSTRAINT jos_emundus_qcm_questions_765_repeat_ibfk_1 FOREIGN KEY (`parent_id`) REFERENCES `jos_emundus_qcm_questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;")->execute();
			}

			if(!in_array('jos_emundus_setup_qcm_repeat_questionid',$tables)) {
				$db->setQuery("create table jos_emundus_setup_qcm_repeat_questionid
                    (
                        id int auto_increment
                            primary key,
                        parent_id int null,
                        questionid int null,
                        params text null
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;")->execute();
				$db->setQuery("create index fb_parent_fk_parent_id_INDEX
                    on jos_emundus_setup_qcm_repeat_questionid (parent_id);")->execute();
				$db->setQuery("create index fb_repeat_el_questionid_INDEX
                    on jos_emundus_setup_qcm_repeat_questionid (questionid);")->execute();
				$db->setQuery("ALTER TABLE `jos_emundus_setup_qcm_repeat_questionid`
                    ADD CONSTRAINT jos_emundus_setup_qcm_repeat_questionid_ibfk_1 FOREIGN KEY (`parent_id`) REFERENCES `jos_emundus_setup_qcm` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                    ADD CONSTRAINT jos_emundus_setup_qcm_repeat_questionid_ibfk_2 FOREIGN KEY (`questionid`) REFERENCES `jos_emundus_qcm_questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;")->execute();
			}

			if(!in_array('jos_emundus_setup_qcm_repeat_sectionid',$tables)) {
				$db->setQuery("create table jos_emundus_setup_qcm_repeat_sectionid
                    (
                        id int auto_increment
                            primary key,
                        parent_id int null,
                        sectionid int null,
                        params text null
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;")->execute();
				$db->setQuery("create index fb_parent_fk_parent_id_INDEX
                    on jos_emundus_setup_qcm_repeat_sectionid (parent_id);")->execute();
				$db->setQuery("create index fb_repeat_el_sectionid_INDEX
                    on jos_emundus_setup_qcm_repeat_sectionid (sectionid);")->execute();
				$db->setQuery("ALTER TABLE `jos_emundus_setup_qcm_repeat_sectionid`
                    ADD CONSTRAINT jos_emundus_setup_qcm_repeat_sectionid_ibfk_1 FOREIGN KEY (`parent_id`) REFERENCES `jos_emundus_setup_qcm` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                    ADD CONSTRAINT jos_emundus_setup_qcm_repeat_sectionid_ibfk_2 FOREIGN KEY (`sectionid`) REFERENCES `jos_emundus_qcm_section` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;")->execute();
			}

			if(!in_array('jos_emundus_qcm_applicants',$tables)) {
				$db->setQuery("create table jos_emundus_qcm_applicants
                    (
                        id int auto_increment
                            primary key,
                        date_time datetime default current_timestamp null,
                        fnum varchar(255) null,
                        user int null,
                        questions varchar(255) null,
                        qcmid int null,
                        step int null,
                        pending varchar(255) null,
                        constraint fnum unique (fnum)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;")->execute();
				$db->setQuery("create index jos_emundus_qcm_applicants_jos_emundus_setup_qcm_id_fk
                    on jos_emundus_qcm_applicants (qcmid);")->execute();
				$db->setQuery("create index jos_emundus_qcm_applicants_jos_emundus_users_id_fk
                    on jos_emundus_qcm_applicants (user);")->execute();
				$db->setQuery("create index jos_emundus_qcm_applicants_jos_emundus_cc_id_fk
                    on jos_emundus_qcm_applicants (fnum);")->execute();
			}

			if(!in_array('jos_emundus_setup_qcm_campaign', $tables)) {
				$db->setQuery("create table jos_emundus_setup_qcm_campaign
                    (
                        id        int auto_increment
                            primary key,
                        date_time datetime null,
                        campaign  int      null,
                        label     text     null,
                        status    int(2)   null,
                        template  text     null,
                        profile   int      null
                    ) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;")->execute();

				$db->setQuery("create table jos_emundus_setup_qcm_campaign_1052_repeat
                    (
                        id        int auto_increment
                            primary key,
                        parent_id int null,
                        category  int null
                    ) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;")->execute();

				$db->setQuery("create index fb_parent_fk_parent_id_INDEX
                        on jos_emundus_setup_qcm_campaign_1052_repeat (parent_id);")->execute();
			}
		} catch (Exception $e) {
			return false;
		}

		return true;
	}
}