<?php

defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Component\Router\RouterBase;

class EmundusRouter extends RouterBase
{
    public function build(&$query)
    {
        $segments = array();
        if (isset($query['view']))
        {
           // $segments[] = $query['view'];
            unset($query['view']);
        }
        return $segments;
    }

    public function parse(&$segments)
    {
        /*$vars = array();

        switch($segments[0])
        {
            case 'files':
                $vars['view'] = 'dossiers';
                break;
            case 'evaluation':
                $vars['view'] = 'evaluations';
                break;
            case 'admission':
                $vars['view'] = 'admission';
                break;
        }
        return $vars;*/
    }

    
}
?>