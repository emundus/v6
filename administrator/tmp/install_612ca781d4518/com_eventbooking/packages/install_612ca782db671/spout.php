<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2021 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Filesystem\File;

class plgEventbookingSpout extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var    JDatabaseDriver
	 */
	protected $db;

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array    $config
	 */
	public function __construct(&$subject, $config = array())
	{
		// Add a check to make sure the plugin will only be run on PHP 7.2.0+
		if (version_compare(PHP_VERSION, '7.2.0', '<'))
		{
			return;
		}

		parent::__construct($subject, $config);
	}

	/**
	 * Get
	 *
	 * @param $file
	 * @param $filename
	 */
	public function onBeforeGettingDataFromFile($file, $filename)
	{
		require_once __DIR__ . '/spout/vendor/autoload.php';

		// If $filename is not provided, do not process because the plugin needs filename to initialize the right reader
		if (!$filename)
		{
			return false;
		}

		try
		{
			$reader = ReaderEntityFactory::createReaderFromFile($filename);

			if ($reader instanceof Box\Spout\Reader\CSV\Reader)
			{
				$config = EventbookingHelper::getConfig();
				$reader->setFieldDelimiter($config->get('csv_delimiter', ','));
			}

			$reader->open($file);
			$headers = [];
			$rows    = [];
			$count   = 0;

			foreach ($reader->getSheetIterator() as $sheet)
			{
				foreach ($sheet->getRowIterator() as $row)
				{
					$cells = $row->getCells();

					if ($count === 0)
					{
						foreach ($cells as $cell)
						{
							$headers[] = $cell->getValue();
						}

						$count++;
					}
					else
					{
						$cellIndex = 0;
						$row       = [];

						foreach ($cells as $cell)
						{
							$row[$headers[$cellIndex++]] = $cell->getValue();
						}

						$rows[] = $row;
					}
				}
			}

			$reader->close();

			return $rows;
		}
		catch (Exception $e)
		{
			return false;
		}
	}

	/**
	 * Handle generic CSV/Excel data export
	 *
	 * @param   array   $rows
	 * @param   array   $fields
	 * @param   array   $headers
	 * @param   string  $filename
	 */
	public function onBeforeExportDataToXLSX($rows, $fields, $headers, $filename)
	{
		require_once __DIR__ . '/spout/vendor/autoload.php';

		$filePath = __DIR__ . '/data/' . $filename;

		//Delete the file if exist
		if (File::exists($filePath))
		{
			File::delete($filePath);
		}

		$config = EventbookingHelper::getConfig();

		if ($config->get('export_data_format') == 'csv')
		{
			$writer = WriterEntityFactory::createCSVWriter();
			$writer->setFieldDelimiter($config->get('csv_delimiter', ','));
		}
		else
		{
			$writer = WriterEntityFactory::createXLSXWriter();
		}


		$writer->openToFile($filePath);

		if (empty($headers))
		{
			$headers = $fields;
		}

		// Write header columns
		$writer->addRow(WriterEntityFactory::createRowFromArray($headers));

		foreach ($rows as $row)
		{
			$data = [];

			foreach ($fields as $field)
			{
				if (property_exists($row, $field))
				{
					$data[] = $row->{$field};
				}
				else
				{
					$data[] = '';
				}
			}

			$writer->addRow(WriterEntityFactory::createRowFromArray($data));
		}

		$writer->close();

		return $filePath;
	}
}
