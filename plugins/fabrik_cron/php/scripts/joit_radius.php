<?php
$input = JFactory::getApplication()->input;

//$db = FabrikWorker::getDbo(false, 4);
$db = JFactory::getDbo();

// Search distance match
$v = $input->getInt('distance', 200);

// Date range to search over (# of days)
$interval = $input->getInt('interval', 1);

$db->setQuery("SELECT haeufigkeit,

	SUBSTRING_INDEX(TRIM(LEADING '(' FROM gmapstartstadt), ',', 1) AS start_lat,
	SUBSTRING_INDEX(SUBSTRING_INDEX(gmapstartstadt, ',', -1), ')', 1) AS start_lon,

	SUBSTRING_INDEX(TRIM(LEADING '(' FROM gmapzielstadt), ',', 1) AS end_lat,
	SUBSTRING_INDEX(SUBSTRING_INDEX(gmapzielstadt, ',', -1), ')', 1) AS end_lon,

	DATE(startdatum) AS start_date,
	DATE_ADD(DATE(startdatum), INTERVAL $interval DAY) AS end_date,

	startdatum AS angebot___startdatum,
	Start AS angebot___Start,
	Ziel AS angebot___Ziel,
	haeufigkeit AS angebot___haeufigkeit,
	u.username AS angebot___benutzer,
	u.email AS angebot___benutzer_email

	FROM `angebot`
	INNER JOIN #__users AS u ON u.id = angebot.Benutzer
	WHERE Benutzer != ''
	AND `match` = '[\"match\"]'
	AND startdatum > NOW()

"
);

$rows = $db->loadObjectList();

$article = getArticle();

// Find matches:
$start_latfield = "SUBSTRING_INDEX(TRIM(LEADING '(' FROM gmapstartstadt), ',', 1)";
$start_lonfield = "SUBSTRING_INDEX(SUBSTRING_INDEX(gmapstartstadt, ',', -1), ')', 1)";

$end_latfield = "SUBSTRING_INDEX(TRIM(LEADING '(' FROM gmapzielstadt), ',', 1)";
$end_lonfield = "SUBSTRING_INDEX(SUBSTRING_INDEX(gmapzielstadt, ',', -1), ')', 1)";

foreach ($rows as $row)
{
	$query = "
		SELECT *, u.username AS cargo___benutzer, u.email AS cargo___benutzer_email
		FROM cargo INNER JOIN #__users AS u ON u.id = cargo.Benutzer WHERE ";

	// Radius search:
	$query .= "(((acos(sin((" . $row->start_lat . "*pi()/180)) * sin(($start_latfield *pi()/180))+cos((" . $row->start_lat
	. "*pi()/180)) * cos(($start_latfield *pi()/180)) * cos(((" . $row->start_lon . "- $start_lonfield)*pi()/180))))*180/pi())*60*1.1515*1.609344) <= "
	. $v;

	$query .= " AND (((acos(sin((" . $row->end_lat . "*pi()/180)) * sin(($end_latfield *pi()/180))+cos((" . $row->end_lat
	. "*pi()/180)) * cos(($end_latfield *pi()/180)) * cos(((" . $row->end_lon . "- $end_lonfield)*pi()/180))))*180/pi())*60*1.1515*1.609344) <= "
	. $v;

	// Date range filter
	$query .= " AND DATE(startdatum) BETWEEN " . $db->quote($row->start_date) . " AND " . $db->quote($row->end_date);

	// No point matching records belonging to the same user
	$query .= " AND Benutzer <> " . $db->quote($row->Benutzer);

	$query .= " AND `match` = '[\"match\"]'";
	$db->setQuery($query);
	$found = $db->loadObjectList();

	domail($article, $row, $found);
}

/*
 * Get article for email
 */
function getArticle()
{
	$db = JFactory::getDbo();

	$db->setQuery("SELECT introtext, title FROM #__content WHERE id = 20");
	$article = $db->loadObject();
	return $article;
}

function domail($article, $row, $matches)
{
	$config = JFactory::getConfig();

	// Mail out
	$email_from = $config->get('mailfrom');
	$email_from_name = $config->get('fromname');
	$subject = $article->title;

	foreach ($matches as $match)
	{

		$msg = $article->introtext;

		foreach ($row as $k => $v)
		{
			$msg = str_replace('{' . $k . '}', $v, $msg);
		}

		foreach ($match as $k => $v)
		{
			$msg = str_replace('{' . $k . '}', $v, $msg);
		}

		$mail = JFactory::getMailer();
		echo
		$email = $match->cargo___benutzer_email;
		$res = $mail->sendMail($email_from, $email_from_name, $email, $subject, $msg, true);

		$mail = JFactory::getMailer();
		$email = $row->angebot___benutzer_email;
		$res = $mail->sendMail($email_from, $email_from_name, $email, $subject, $msg, true);
	}
}






