<?php
/**
 * @package     Joomla\CMS\Console
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

namespace Joomla\CMS\Console;

use Joomla\Console\Command\AbstractCommand;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TchoozDeploymentRunCommand extends AbstractCommand
{
	use DatabaseAwareTrait;

	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected static $defaultName = 'tchooz:deployment:run';

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
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

		$symfonyStyle = new SymfonyStyle($input, $output);

		$symfonyStyle->title('Tchooz deployment running...');

        $symfonyStyle->section('Enabling Fabrik extensions');
        try {
            $query->update($db->quoteName('#__extensions'))
                ->set($db->quoteName('enabled') . ' = 1')
                ->where($db->quoteName('element') . ' LIKE ' . $db->quote('%fabrik%'))
                ->orWhere($db->quoteName('folder') . ' LIKE ' . $db->quote('fabrik_%'))
                ->andWhere($db->quoteName('enabled') . ' = 0');
            $db->setQuery($query);
            if($db->execute()){
                $symfonyStyle->success('Fabrik extensions enabled');
            } else {
                $symfonyStyle->error('Error enabling Fabrik extensions');
            }
        } catch (\Exception $e) {
            $symfonyStyle->error('Error enabling Fabrik extensions: ' . $e->getMessage());
            return 1;
        }

        $symfonyStyle->section('Enabling Gantry extensions');
        try {
            $query->clear()
                ->update($db->quoteName('#__extensions'))
                ->set($db->quoteName('enabled') . ' = 1')
                ->where($db->quoteName('element') . ' LIKE ' . $db->quote('%fabrik%'))
                ->orWhere($db->quoteName('folder') . ' LIKE ' . $db->quote('fabrik_'))
                ->andWhere($db->quoteName('enabled') . ' = 0');
            $db->setQuery($query);
            if($db->execute()){
                $symfonyStyle->success('Gantry extensions enabled');
            } else {
                $symfonyStyle->error('Error enabling Gantry extensions');
            }
        } catch (\Exception $e) {
            $symfonyStyle->error('Error enabling Fabrik extensions: ' . $e->getMessage());
            return 1;
        }

		$symfonyStyle->success('Tchooz is ready to use!');

		return 0;
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
		$this->setDescription('Run a Tchooz deployment by installing a vanilla database and running the migrations');
		$this->setHelp(
			<<<EOF
The <info>%command.name%</info> command run a fresh deployment by installing a vanilla database and running the migrations
<info>php %command.full_name%</info>
EOF
		);
	}
}