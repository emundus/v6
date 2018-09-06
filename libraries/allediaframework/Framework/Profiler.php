<?php
/**
 * @package   AllediaFramework
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC., All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\Framework;

defined('_JEXEC') or die();


class Profiler
{
    protected $startTime = 0;

    protected $initialMemory = 0;

    protected $maxLength = 80;

    protected $lastMemory = 0;

    public function start()
    {
        $this->initialMemory = memory_get_usage();
    }

    public function step($label = null)
    {
        $this->startStep($label);
        $this->endStep();
    }

    public function echoData()
    {
        echo "\n";
        $total    = memory_get_usage() - $this->initialMemory;
        $data     = "==== Mem: " . number_format($total, 0, '.', ',') . ' bytes';
        $diff     = $total - $this->lastMemory;
        $peak     = memory_get_peak_usage();
        $operator = '';

        echo $data;

        if ($diff != 0) {
            $operator = $diff > 0 ? '+' : '-';
        }

        echo  '    diff: ' . $operator . number_format(abs($diff), 0, '.', ',') . ' bytes    peak: ' . number_format($peak, '0', '.', ',') . ' bytes';

        $this->lastMemory = $total;
        echo "\n";
    }

    public function startStep($label = null)
    {
        echo "\n";
        $this->printHeader($label);
        $this->echoData();
    }

    public function endStep()
    {
        $this->echoData();
        $this->printSeparator();
        echo "\n";
    }

    protected function printHeader($label = null, $leftPadding = 4)
    {
        if (!is_null($label)) {
            $length = $leftPadding;

            echo str_repeat('=', $length);

            echo " $label ";
            $length += strlen($label) + 2;

            echo str_repeat('=', $this->maxLength - $length);
        } else {
            $this->printSeparator();
        }
    }

    protected function printSeparator()
    {
        echo str_repeat("=", $this->maxLength);
    }
}
