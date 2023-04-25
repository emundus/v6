<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2019. Faboba.com All rights reserved.
 */

namespace Falang\Database;

use Joomla\CMS\Factory;
use Joomla\Database\FetchOrientation;
use Joomla\Database\Mysqli\MysqliStatement;
use Joomla\Database\StatementInterface;


class FMysqliStatement extends MysqliStatement implements StatementInterface
{

    public function numFields()
    {
        return $this->statement->field_count;
    }

    public function getMeta($i = 0)
    {
        $meta = $this->statement->result_metadata();
        $metaData = [];

        if ($meta !== false) {
            $metaData = [];

            foreach ($meta->fetch_fields() as $col) {
                $metaData[] = $col;
            }

            $meta->free();


        }
        if (isset($metaData[$i])) {
            return $metaData[$i];
        } else {
            return null;
        }
    }
}