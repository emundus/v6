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

	        // This patch helps avoid double opening views. This caused a double refresh on AJAX calls within those views.
	        // SEO was adding the ?view= to links which already had views (ex: emundus.fr/files/?view=files)
	        $v_exceptions = ['files', 'evaluation', 'decision', 'admission', 'users'];

	        if (in_array($query['view'], $v_exceptions))
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