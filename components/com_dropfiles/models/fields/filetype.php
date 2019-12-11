<?php

/**
 * Dropfiles
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 *
 * @package   Dropfiles
 * @copyright Copyright (C) 2013 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2013 Damien Barrère (http://www.crac-design.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') || die;

jimport('joomla.form.formfield');
JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 */
class JFormFieldFiletype extends JFormFieldList
{
    /**
     * Type
     *
     * @var string
     */
    protected $type = 'Filetype';

    /**
     * Method to get a list of tags
     *
     * @return array  The field option objects.
     *
     * @since 3.1
     */
    protected function getOptions()
    {
        $params = JComponentHelper::getParams('com_dropfiles');
        $allowedext_list = '7z,ace,bz2,dmg,gz,rar,tgz,zip,csv,doc,docx,html,key,keynote,odp,ods,odt,pages,pdf,pps,ppt,'
            . 'pptx,rtf,tex,txt,xls,xlsx,xml,bmp,exif,gif,ico,jpeg,jpg,png,psd,tif,tiff,aac,aif,aiff,alac,amr,au,cdda,'
            . 'flac,m3u,m4a,m4p, mid, mp3, mp4, mpa, ogg, pac, ra, wav, wma, 3gp,asf,avi,flv,m4v,mkv,mov,mpeg,mpg,'
            . 'rm,swf,vob,wmv';
        $filetype = $params->get('allowedext', $allowedext_list);
        $exts = explode(',', $filetype);
        $options = array();
        if (!empty($exts)) {
            foreach ($exts as $ext) {
                $options[] = JHtml::_('select.option', $ext, $ext);
            }
        }

        return $options;
    }
}
