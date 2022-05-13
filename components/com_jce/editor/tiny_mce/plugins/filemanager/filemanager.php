<?php

/**
 * @copyright     Copyright (c) 2009-2021 Ryan Demmer. All rights reserved
 * @license       GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('JPATH_PLATFORM') or die;
// Set flag that this is an extension parent
if (!defined('_WF_EXT')) {
    define('_WF_EXT', 1);
}

class WFFileManagerPlugin extends WFMediaManager
{
    /*
     * @var string
     */
    protected $name = 'filemanager';

    public $_filetypes = 'acrobat=pdf;office=doc,docx,dot,dotx,ppt,pps,pptx,ppsx,xls,xlsx;image=gif,jpeg,jpg,png,apng,webp,avif;archive=zip,tar,gz;video=swf,mov,wmv,avi,flv,mp4,ogv,ogg,webm,mpeg,mpg;audio=wav,mp3,ogg,webm,aiff;openoffice=odt,odg,odp,ods,odf;text=txt,rtf,md';

    public function __construct()
    {
        $config = array(
            'can_edit_images' => 1,
            'show_view_mode' => 1,
        );

        parent::__construct($config);

        $request = WFRequest::getInstance();
        $request->setRequest(array($this, 'getFileDetails'));

        $browser = $this->getBrowser();
        $browser->addEvent('onUpload', array($this, 'onUpload'));
    }

    /**
     * Display the plugin.
     */
    public function display()
    {
        $slot = JFactory::getApplication()->input->getCmd('slot', 'plugin');

        if ($slot === 'editor') {
            return parent::display();
        }

        parent::display();

        $document = WFDocument::getInstance();

        $tabs = WFTabs::getInstance(array('base_path' => WF_EDITOR_PLUGIN));
        // Add tabs
        $tabs->addTab('file');
        $tabs->addTab('advanced', $this->getParam('tabs_advanced', 1));

        // Load Popups instance
        $popups = WFPopupsExtension::getInstance(array(
            'text' => false,
            'default' => $this->getParam('filemanager.popups.default', ''),
        ));

        $popups->display();

        $document->addScript(array('filemanager'), 'plugins');
        $document->addStyleSheet(array('filemanager'), 'plugins');

        $document->addScriptDeclaration('FileManager.settings=' . json_encode($this->getSettings()) . ';');
    }

    protected function getIconMap()
    {
        jimport('joomla.filesystem.folder');

        $path = $this->getParam('filemanager.icon_path', 'media/jce/icons');
        $format = $this->getParam('filemanager.icon_format', '{$name}.png');
        $extensions = $this->getParam('extensions', $this->get('_filetypes'));

        if (strpos($path, 'http') !== false) {
            return $extensions;
        }

        // get extension from format
        $ext = JFile::getExt($format);
        // get all matched icons
        $icons = JFolder::files(JPATH_SITE . '/' . $path, '\.' . $ext);

        if ($icons) {
            for ($i = 0; $i < count($icons); ++$i) {
                // convert format to regex equivalent
                $format = str_replace('{$name}', '([a-z0-9]+)', $format);
                // get icon name
                preg_match('#' . $format . '#i', $icons[$i], $matches);

                if ($matches) {
                    $icons[$i] = basename($matches[0], '.' . $ext);
                }
            }
        } else {
            $icons = array();
        }

        $map = array();

        // map through extensions and remove icons that do not exist
        foreach (explode(';', $extensions) as $group) {
            // only if valid extensions group
            if (substr($group, 0, 1) === '-') {
                continue;
            }

            // remove extensions that are disabled
            $group = preg_replace('#(,)?-([\w]+)#', '', $group);

            // get the groups parts eg: image, 'jpg,jpeg,png,gif'
            $parts = explode('=', $group);

            $key = $parts[0];
            $values = explode(',', $parts[1]);

            foreach (array_diff($values, $icons) as $item) {
                $map[$item] = $key;
            }

            foreach (array_intersect($values, $icons) as $item) {
                $map[$item] = $item;
            }
        }

        return $map;
    }

    public function onUpload($file, $relative = '')
    {
        $app = JFactory::getApplication();

        // inline upload
        if ($app->input->getInt('inline', 0) === 1) {
            $result = array(
                'file' => $relative,
                'name' => WFUtility::mb_basename($file),
            );

            $result['method'] = $this->getParam('filemanager.format', 'link');

            if ($result['method'] === 'embed') {
                $result['openwith'] = $this->getParam('filemanager.method_openwith', '');

                $result['width'] = $this->getParam('filemanager.embed_width', 640);
                $result['height'] = $this->getParam('filemanager.embed_height', 480);

                // remove px if present
                $result['width'] = str_replace('px', '', $result['width']);
                $result['height'] = str_replace('px', '', $result['height']);

                return $result;
            }

            $defaults = $this->getDefaultAttributes();
            $features = array();
            $attribs = array();

            // add icon first
            if ($defaults['option_icon_check']) {
                jimport('joomla.filesystem.file');
                $ext = JFile::getExt(basename($file));
                $map = $this->getIconMap();

                $icon = str_replace('{$name}', $map[$ext], $this->getParam('filemanager.icon_format', '{$name}.png'));
                $icon = $this->getParam('filemanager.icon_path', 'media/jce/icons') . '/' . $icon;

                $features[] = array('node' => 'img', 'attribs' => array('src' => $icon, 'alt' => basename($icon), 'class' => 'wf_file_icon'));
            }

            // add text
            $features[] = array('node' => 'span', 'attribs' => array('class' => 'wf_file_text'), 'html' => basename($file));

            foreach ($defaults as $key => $value) {
                switch ($key) {
                    default:
                        if ($value !== '') {
                            $attribs[$key] = $value;
                        }
                        break;
                    case 'method':
                    case 'width':
                    case 'height':
                    case 'option_date_check':
                        break;
                    case 'option_size_check':
                        if ($value) {
                            $features[] = array('node' => 'span', 'attribs' => array('class' => 'wf_file_size', 'style' => 'margin-left:5px;'), 'html' => WFUtility::formatSize(filesize($file)));
                        }
                        break;
                    case 'option_date_check':
                        if ($value) {
                            $features[] = array('node' => 'span', 'attribs' => array('class' => 'wf_file_date', 'style' => 'margin-left:5px;'), 'html' => WFUtility::formatDate(filemtime($file), $this->getParam('filemanager.date_format', '%d/%m/%Y, %H:%M')));
                        }
                        break;
                }
            }

            if (!empty($attribs)) {
                $result['attributes'] = $attribs;
            }

            if (!empty($features)) {
                $result['features'] = $features;
            }

            return $result;
        }

        return array();
    }

    public function getFileDetails($file)
    {
        $browser = $this->getBrowser();

        $data = array('size' => '', 'date' => '', 'url' => '');

        $filesystem = $browser->getFileSystem();
        // get array with folder date and content count eg: array('date'=>'00-00-000', 'folders'=>1, 'files'=>2);
        $details = $filesystem->getFileDetails($file);

        if (!empty($details['size'])) {
            $details['size'] = WFUtility::formatSize($details['size']);
        }

        if (!empty($details['modified'])) {
            $details['date'] = WFUtility::formatDate($details['modified'], $this->getParam('filemanager.date_format', '%d/%m/%Y, %H:%M'));
        }

        return array_merge($data, $details);
    }

    public function getSettings($settings = array())
    {
        $settings = array(
            'icon_map' => $this->getIconMap(),
            'icon_path' => $this->getParam('filemanager.icon_path', 'media/jce/icons'),
            'icon_format' => $this->getParam('filemanager.icon_format', '{$name}.png'),
            'date_format' => $this->getParam('filemanager.date_format', '%d/%m/%Y, %H:%M'),
            'method' => $this->getParam('filemanager.method', 'link'),
            'width' => $this->getParam('filemanager.embed_width', ''),
            'height' => $this->getParam('filemanager.embed_height', ''),
            'target' => $this->getParam('filemanager.target', ''),
            'text_alert' => $this->getParam('filemanager.text_alert', 1),
            'replace_text' => $this->getParam('filemanager.replace_text', 1),
            'can_edit_images' => 1,

            'option_icon_check' => $this->getParam('filemanager.option_icon_check', 0),
            'option_size_check' => $this->getParam('filemanager.option_size_check', 0),
            'option_date_check' => $this->getParam('filemanager.option_date_check', 0),
        );

        return parent::getSettings($settings);
    }
}
