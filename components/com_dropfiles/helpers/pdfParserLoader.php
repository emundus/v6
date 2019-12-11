<?php
// no direct access
defined('_JEXEC') || die;
// in order to accommodate for PHP 5.2 this needs to be abstracted to it's own file and conditionally included

class DropfilesPdfParser
{

    function init()
    {
        $parser = new \Smalot\PdfParser\Parser();
        return $parser;
    }
}
