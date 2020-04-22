<?php

// Team JTable
class TimewindersTableTeam extends JTable
{

	/**
	 * Constructor
	 *
	 * @param   object  &$db  database object
	 */

	public function __construct(&$db)
	{
		parent::__construct('tw_teams', 'id', $db);
	}

}

class TimewindersTableGame extends JTable
{

	/**
	 * Constructor
	 *
	 * @param   object  &$db  database object
	 */

	public function __construct(&$db)
	{
		parent::__construct('tw_games', 'id', $db);
	}

}

//slingshoteffect.co.uk/timewinders/index.php?option=com_fabrik&task=form.process&formid=4&tw_game_states___game_id=1&tw_game_states___state=%23GAME&format=raw&fabrik_ajax=1&rfid=456789

// Set up
$app = JFactory::getApplication();
$input = $app->input;
$db = JFactory::getDbo();
$query = $db->getQuery(true);
$now = JFactory::getDate();
$data = array();

// Get request state
$rfid = $input->get('rfid');
$gameid = $input->getInt('tw_game_states___game_id');
$state = $_REQUEST['tw_game_states___state'];

// A game start found
if ($state === '#GAME')
{

	// Game gone into start mode - get the tss id
	$query->select('*')->from('tw_tss_objects')->where('rfid = ' . $db->quote($rfid));
	$db->setQuery($query);
	$tss = $db->loadObject();

	$team = JTable::getInstance('Team', 'TimewindersTable');
	$game = JTable::getInstance('Game', 'TimewindersTable');
	$game->load($gameid);

	// Logic for creating new team on first game
	if ($gameid === 1)
	{

		// How many previous teams have used the TSS objec
		$query->clear();
		$query->select('COUNT(*)')->from('tw_teams')->where('tss_object_id = ' . (int) $tss->id);
		$db->setQuery($query);
		$teamCount = $db->loadResult();

		// Create a team
		$data['label'] = $tss->label . ': ' . ($teamCount + 1);
		$data['start_time'] = $now->toSql();
		$data['tss_object_id'] = $tss->id;

	}
	else
	{
		//Load the team from the rfid
		$team->load(array('tss_object_id' => $tss->id));
	}

	// Also when a team starts a game update its location
	$data['location'] = $game->location;

	// Save the team
	$team->bind($data);

	$team->store();
}

