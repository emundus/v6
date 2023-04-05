<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

use Joomla\CMS\Access\Access;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserHelper;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Console command to remove a user from a group
 *
 * @since  4.0.0
 */
class RemoveUserFromGroupCommand extends AbstractCommand
{
    use DatabaseAwareTrait;

    /**
     * The default command name
     *
     * @var    string
     * @since  4.0.0
     */
    protected static $defaultName = 'user:removefromgroup';

    /**
     * SymfonyStyle Object
     * @var   object
     * @since 4.0.0
     */
    private $ioStyle;

    /**
     * Stores the Input Object
     * @var   object
     * @since 4.0.0
     */
    private $cliInput;

    /**
     * The username
     *
     * @var    string
     *
     * @since  4.0.0
     */
    private $username;

    /**
     * The usergroups
     *
     * @var    array
     *
     * @since  4.0.0
     */
    private $userGroups = [];

    /**
     * Command constructor.
     *
     * @param   DatabaseInterface  $db  The database
     *
     * @since   4.2.0
     */
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct();

        $this->setDatabase($db);
    }

    /**
     * Internal function to execute the command.
     *
     * @param   InputInterface   $input   The input to inject into the command.
     * @param   OutputInterface  $output  The output to inject into the command.
     *
     * @return  integer  The command exit code
     *
     * @since   4.0.0
     */
    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $this->configureIO($input, $output);
        $this->ioStyle->title('Remove User From Group');
        $this->username = $this->getStringFromOption('username', 'Please enter a username');

        $userId = UserHelper::getUserId($this->username);

        if (empty($userId)) {
            $this->ioStyle->error("The user " . $this->username . " does not exist!");

            return 1;
        }

        $user = User::getInstance($userId);

        $this->userGroups = $this->getGroups($user);

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('title'))
            ->from($db->quoteName('#__usergroups'))
            ->where($db->quoteName('id') . ' = :userGroup');

        foreach ($this->userGroups as $userGroup) {
            $query->bind(':userGroup', $userGroup);
            $db->setQuery($query);

            $result = $db->loadResult();

            if (Access::checkGroup($userGroup, 'core.admin')) {
                $queryUser = $db->getQuery(true);
                $queryUser->select('COUNT(*)')
                    ->from($db->quoteName('#__users', 'u'))
                    ->leftJoin(
                        $db->quoteName('#__user_usergroup_map', 'g'),
                        '(' . $db->quoteName('u.id') . ' = ' . $db->quoteName('g.user_id') . ')'
                    )
                    ->where($db->quoteName('g.group_id') . " = :groupId")
                    ->where($db->quoteName('u.block') . " = 0")
                    ->bind(':groupId', $userGroup);

                $db->setQuery($queryUser);
                $activeSuperUser = $db->loadResult();

                if ($activeSuperUser < 2) {
                    $this->ioStyle->error("Can't remove user '" . $user->username . "' from group '" . $result . "'! "
                        . $result . " needs at least one active user!");

                    return Command::FAILURE;
                }
            }

            if (\count(Access::getGroupsByUser($user->id, false)) < 2) {
                $this->ioStyle->error("Can't remove '" . $user->username . "' from group '" . $result
                    . "'! Every user needs to be a member of at least one group");

                return Command::FAILURE;
            }

            if (!UserHelper::removeUserFromGroup($user->id, $userGroup)) {
                $this->ioStyle->error("Can't remove '" . $user->username . "' from group '" . $result . "'!");

                return Command::FAILURE;
            }

            $this->ioStyle->success("Removed '" . $user->username . "' from group '" . $result . "'!");
        }

        return Command::SUCCESS;
    }

    /**
     * Method to get a value from option
     *
     * @param   object  $user  user object
     *
     * @return  array
     *
     * @since   4.0.0
     */
    protected function getGroups($user): array
    {
        $option     = $this->getApplication()->getConsoleInput()->getOption('group');
        $db         = $this->getDatabase();
        $userGroups = Access::getGroupsByUser($user->id, false);

        if (!$option) {
            $query = $db->getQuery(true)
                ->select($db->quoteName('title'))
                ->from($db->quoteName('#__usergroups'))
                ->whereIn($db->quoteName('id'), $userGroups);
            $db->setQuery($query);

            $result = $db->loadColumn();

            $choice = new ChoiceQuestion(
                'Please select a usergroup (separate multiple groups with a comma)',
                $result
            );
            $choice->setMultiselect(true);

            $answer = (array) $this->ioStyle->askQuestion($choice);

            $groupList = [];

            foreach ($answer as $group) {
                $groupList[] = $this->getGroupId($group);
            }

            return $groupList;
        }

        $groupList = [];
        $option = explode(',', $option);

        foreach ($option as $group) {
            $groupId = $this->getGroupId($group);

            if (empty($groupId)) {
                $this->ioStyle->error("Invalid group name '" . $group . "'");
                throw new InvalidOptionException("Invalid group name " . $group);
            }

            $groupList[] = $this->getGroupId($group);
        }

        return $groupList;
    }

    /**
     * Method to get groupId by groupName
     *
     * @param   string  $groupName  name of group
     *
     * @return  integer
     *
     * @since   4.0.0
     */
    protected function getGroupId($groupName)
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__usergroups'))
            ->where($db->quoteName('title') . '= :groupName')
            ->bind(':groupName', $groupName);
        $db->setQuery($query);

        return $db->loadResult();
    }

    /**
     * Method to get a value from option
     *
     * @param   string  $option    set the option name
     *
     * @param   string  $question  set the question if user enters no value to option
     *
     * @return  string
     *
     * @since   4.0.0
     */
    protected function getStringFromOption($option, $question): string
    {
        $answer = (string) $this->getApplication()->getConsoleInput()->getOption($option);

        while (!$answer) {
            $answer = (string) $this->ioStyle->ask($question);
        }

        return $answer;
    }

    /**
     * Configure the IO.
     *
     * @param   InputInterface   $input   The input to inject into the command.
     * @param   OutputInterface  $output  The output to inject into the command.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    private function configureIO(InputInterface $input, OutputInterface $output)
    {
        $this->cliInput = $input;
        $this->ioStyle = new SymfonyStyle($input, $output);
    }

    /**
     * Configure the command.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function configure(): void
    {
        $help = "<info>%command.name%</info> removes a user from a group
		\nUsage: <info>php %command.full_name%</info>";

        $this->setDescription('Remove a user from a group');
        $this->addOption('username', null, InputOption::VALUE_OPTIONAL, 'username');
        $this->addOption('group', null, InputOption::VALUE_OPTIONAL, 'group');
        $this->setHelp($help);
    }
}
