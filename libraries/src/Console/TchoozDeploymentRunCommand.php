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
		$symfonyStyle = new SymfonyStyle($input, $output);

		$symfonyStyle->title('Hello World Command Title');

		// You might want to do some stuff here in Joomla

		$symfonyStyle->success('Hello World!');

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