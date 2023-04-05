<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event\MultiFactor;

use DomainException;
use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultAwareInterface;
use Joomla\CMS\Event\Result\ResultTypeObjectAware;
use Joomla\Component\Users\Administrator\DataShape\SetupRenderOptions;
use Joomla\Component\Users\Administrator\Table\MfaTable;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Concrete Event class for the onUserMultifactorGetSetup event
 *
 * @since 4.2.0
 */
class GetSetup extends AbstractImmutableEvent implements ResultAwareInterface
{
    use ResultAware;
    use ResultTypeObjectAware;

    /**
     * Public constructor
     *
     * @param   MfaTable  $record  The record to display the setup page for
     *
     * @since   4.2.0
     */
    public function __construct(MfaTable $record)
    {
        parent::__construct('onUserMultifactorGetSetup', ['record' => $record]);

        $this->resultIsNullable        = true;
        $this->resultAcceptableClasses = [
            SetupRenderOptions::class,
        ];
    }

    /**
     * Validate the value of the 'record' named parameter
     *
     * @param   MfaTable  $value  The value to validate
     *
     * @return  MfaTable
     * @since   4.2.0
     */
    public function setRecord(MfaTable $value): MfaTable
    {
        if (empty($value)) {
            throw new DomainException(sprintf('Argument \'record\' of event %s must be a MfaTable object.', $this->name));
        }

        return $value;
    }
}
