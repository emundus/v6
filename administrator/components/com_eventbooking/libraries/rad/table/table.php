<?php
/**
 * @package     Joomla.RAD
 * @subpackage  Table
 * @author      Ossolution Team
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('JPATH_PLATFORM') or die;

/**
 * Since the JTable class is marked as abstract, we need to define this package so that we can create a JTable object without having to creating a file for it.
 * Simply using the syntax $row = new RADTable('#__mycom_mytable', 'id', $db);
 */
class RADTable extends JTable
{
}
