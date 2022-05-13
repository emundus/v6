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

// set as an extension parent
if (!defined('_WF_EXT')) {
    define('_WF_EXT', 1);
}

require_once WF_EDITOR_LIBRARIES . '/classes/manager/base.php';

JLoader::register('WFImage', WF_EDITOR_LIBRARIES . '/pro/classes/image/image.php');

class WFMediaManager extends WFMediaManagerBase
{
    public $can_edit_images = 0;

    public $show_view_mode = 0;

    protected $exifCache = array();

    public function __construct($config = array())
    {
        parent::__construct($config);

        $app = JFactory::getApplication();

        $request = WFRequest::getInstance();
        $layout = $app->input->getCmd('slot', 'plugin');

        if ($layout === 'plugin') {
            $this->addFileBrowserEvent('onBeforeUpload', array($this, 'onBeforeUpload'));
            $this->addFileBrowserEvent('onUpload', array($this, 'onUpload'));

            if ($app->input->getCmd('action') === 'thumbnail') {
                JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

                $file = $app->input->get('img', '', 'STRING');

                // check file path
                WFUtility::checkPath($file);

                // clean path
                $file = WFUtility::makeSafe($file);

                if ($file && preg_match('/\.(jpg|jpeg|png|gif|tiff|bmp|webp)$/i', $file)) {
                    return $this->createCacheThumb(rawurldecode($file));
                }
            }

            if ($this->get('can_edit_images') && $this->getParam('thumbnail_editor', 1)) {
                $request->setRequest(array($this, 'createThumbnail'));
                $request->setRequest(array($this, 'createThumbnails'));
                $request->setRequest(array($this, 'deleteThumbnail'));
            }

            if ($this->get('can_edit_images') && $this->checkAccess('image_editor', 1)) {
                $request->setRequest(array($this, 'resizeImages'));
            }

            $this->addFileBrowserEvent('onFilesDelete', array($this, 'onFilesDelete'));
            $this->addFileBrowserEvent('onGetItems', array($this, 'processListItems'));

        } else {
            $request->setRequest(array($this, 'applyImageEdit'));
            $request->setRequest(array($this, 'saveTextFile'));
        }

        $request->setRequest(array($this, 'saveImageEdit'));
        $request->setRequest(array($this, 'cleanEditorTmp'));
    }

    /**
     * Display the plugin.
     */
    public function display()
    {
        $document = WFDocument::getInstance();

        $layout = JFactory::getApplication()->input->getCmd('slot', 'plugin');

        // Plugin
        if ($layout === 'plugin') {
            if ($this->get('can_edit_images')) {
                if ($this->checkAccess('image_editor', 1)) {
                    $this->addFileBrowserButton('file', 'image_editor', array('action' => 'editImage', 'title' => JText::_('WF_BUTTON_EDIT_IMAGE'), 'restrict' => 'jpg,jpeg,png,gif,webp', 'multiple' => true));
                }

                if ($this->checkAccess('thumbnail_editor', 1)) {
                    $this->addFileBrowserButton('file', 'thumb_create', array('action' => 'createThumbnail', 'title' => JText::_('WF_BUTTON_CREATE_THUMBNAIL'), 'trigger' => true, 'multiple' => true, 'icon' => 'thumbnail'));
                    $this->addFileBrowserButton('file', 'thumb_delete', array('action' => 'deleteThumbnail', 'title' => JText::_('WF_BUTTON_DELETE_THUMBNAIL'), 'trigger' => true, 'multiple' => true, 'icon' => 'thumbnail-remove'));
                }
            }

            if ($this->checkAccess('text_editor', 0)) {
                $this->addFileBrowserButton('file', 'text_editor', array('action' => 'editText', 'title' => JText::_('WF_BUTTON_EDIT_FILE'), 'restrict' => 'txt,json,html,htm,xml,md,csv'));
            }

            // get parent display data
            parent::display();

            // add pro scripts
            $document->addScript(array('widget', 'transform', 'thumbnail'), 'pro');
            $document->addStyleSheet(array('manager', 'transform'), 'pro');
        }

        // Image Editor
        if ($layout === 'editor.image') {
            if ($this->checkAccess('image_editor', 1) === false) {
                throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
            }

            // cleanup tmp files
            $this->cleanTempDirectory();

            $view = $this->getView();
            $view->setLayout('image');
            $view->addTemplatePath(WF_EDITOR_LIBRARIES . '/pro/views/editor/image/tmpl');

            $lists = array();

            $lists['resize'] = $this->getPresetsList('resize');
            $lists['crop'] = $this->getPresetsList('crop');

            $view->lists = $lists;

            // get parent display data
            parent::display();

            $document->addScript(array('transform', 'editor/image.min'), 'pro');
            $document->addStyleSheet(array('transform', 'editor/image.min'), 'pro');
            $document->addScriptDeclaration('jQuery(document).ready(function($){ImageEditor.init({"site" : "' . JURI::root() . '", "root" : "' . JURI::root(true) . '"})});');

            $document->setTitle(JText::_('WF_MANAGER_IMAGE_EDITOR'));
        }

        if ($layout === 'editor.text') {
            if ($this->checkAccess('text_editor', 0) === false) {
                throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
            }

            $view = $this->getView();
            $view->setLayout('text');
            $view->addTemplatePath(WF_EDITOR_LIBRARIES . '/pro/views/editor/text/tmpl');

            // get parent display data
            parent::display();

            $theme = $this->getParam('editor.text_editor_theme', 'codemirror');

            $document->addScript(array(
                'editor/text.min',
            ), 'pro');

            $document->addScript(array(
                'libraries/pro/vendor/beautify/beautify.min',
                'libraries/pro/vendor/codemirror/js/codemirror.min',
            ), 'jce');

            $document->addStyleSheet(array(
                'editor/text.min',
            ), 'pro');

            $document->addStyleSheet(array(
                'libraries/pro/vendor/codemirror/css/codemirror.min',
                'libraries/pro/vendor/codemirror/css/theme/' . $theme,
            ), 'jce');

            $settings = array(
                'highlight' => $this->getParam('editor.text_editor_highlight', 1),
                'linenumbers' => $this->getParam('editor.text_editor_numbers', 1),
                'wrap' => $this->getParam('editor.text_editor_wrap', 1),
                'format' => $this->getParam('editor.text_editor_format', 1),
                'tag_closing' => $this->getParam('editor.text_editor_tag_closing', 1),
                'font_size' => $this->getParam('editor.text_editor_font_size', '', ''),
                'theme' => $this->getParam('editor.text_editor_theme', 'codemirror'),
                'site' => JURI::root(),
                'root' => JURI::root(true),
            );

            $document->addScriptDeclaration('jQuery(document).ready(function($){CodeEditor.init(' . json_encode($settings) . ')});');

            $document->setTitle(JText::_('WF_MANAGER_TEXT_EDITOR'));
        }
    }

    public function getPresetsList($type)
    {
        $list = array();

        switch ($type) {
            case 'resize':
                $list = $this->getParam('editor.resize_presets', '320x240,640x480,800x600,1024x768');

                if (is_string($list)) {
                    $list = explode(',', $list);
                }

                break;
            case 'crop':
                $list = $this->getParam('editor.crop_presets', '4:3,16:9,20:30,320x240,240x320,640x480,480x640,800x600,1024x768');

                if (is_string($list)) {
                    $list = explode(',', $list);
                }

                break;
        }

        return $list;
    }

    private function isFtp()
    {
        // Initialize variables
        jimport('joomla.client.helper');
        $FTPOptions = JClientHelper::getCredentials('ftp');

        return $FTPOptions['enabled'] == 1;
    }

    private static function convertIniValue($value)
    {
        $suffix = '';

        preg_match('#([0-9]+)\s?([a-z]+)#i', $value, $matches);

        // get unit
        if (isset($matches[2])) {
            $suffix = $matches[2];
        }
        // get value
        if (isset($matches[1])) {
            $value = (int) $matches[1];
        }

        // Convert to bytes
        switch (strtolower($suffix)) {
            case 'g':
            case 'gb':
                $value *= 1073741824;
                break;
            case 'm':
            case 'mb':
                $value *= 1048576;
                break;
            case 'k':
            case 'kb':
                $value *= 1024;
                break;
        }

        return (int) $value;
    }

    private static function checkMem($image)
    {
        $channels = ($image['mime'] == 'image/png') ? 4 : 3;

        if (function_exists('memory_get_usage')) {
            // try ini_get
            $limit = ini_get('memory_limit');

            // try get_cfg_var
            if (empty($limit)) {
                $limit = get_cfg_var('memory_limit');
            }

            // no limit set...
            if ($limit === '-1') {
                return true;
            }

            // can't get from ini, assume low value of 32M
            if (empty($limit)) {
                $limit = 32 * 1048576;
            } else {
                $limit = self::convertIniValue($limit);
            }

            // get memory used so far
            $used = memory_get_usage(true);

            return $image[0] * $image[1] * $channels * 1.7 < $limit - $used;
        }

        return true;
    }

    /**
     * Get and temporarily store the exif data of an image
     *
     * @param [String] $file The aboslute path to the image
     * @param [String] $key The key to store the data under
     * @return void
     */
    protected function getExifData($file, $key = null)
    {
        // use file name as key
        if (empty($key)) {
            $key = $file;
        }

        if (array_key_exists($key, $this->exifCache)) {
            return $this->exifCache[$key];
        }

        $exif = null;

        if (!function_exists('exif_read_data') || !is_file($file)) {
            return $exif;
        }

        $exif = @exif_read_data($file);

        if ($exif && is_array($exif) && array_key_exists('EXIF', $exif)) {
            $this->exifCache[$key] = $exif;
        }

        return $exif;
    }

    protected function cleanExifString($string)
    {
        $string = (string) filter_var($string, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_BACKTICK);
        return htmlspecialchars($string);
    }

    protected function getImageDescription($image)
    {
        $description = '';

        // must be a jpeg
        if (!preg_match('#\.(jpg|jpeg)$#', strtolower($image))) {
            return $description;
        }

        $data = $this->getExifData($image, WFUtility::mb_basename($image));

        if (!empty($data) && isset($data['ImageDescription'])) {
            $description = $this->cleanExifString($data['ImageDescription']);
        }

        return $description;
    }

    public function onBeforeUpload(&$file, &$dir, &$name)
    {
        // check for and reset image orientation
        if (preg_match('#\.(jpg|jpeg)$#i', $file['name'])) {

            // store exif data
            $exif = $this->getExifData($file['tmp_name'], $file['name']);

            $remove_exif = (bool) $this->getParam('editor.upload_remove_exif', false);

            // data exists and we are allowed to remove it
            if ($exif && $remove_exif) {
                if (false == $this->removeExifData($file['tmp_name'])) {
                    throw new InvalidArgumentException(JText::_('WF_MANAGER_UPLOAD_EXIF_REMOVE_ERROR'));
                }
            }
        }
    }

    /**
     * Manipulate file and folder list.
     *
     * @param  array file/folder array reference
     *
     * @since  1.5
     */
    public function processListItems(&$result)
    {
        $browser = $this->getFileBrowser();

        if (empty($result['files'])) {
            return;
        }

        // clean cache
        $filesystem = $browser->getFileSystem();

        for ($i = 0; $i < count($result['files']); ++$i) {
            $file = $result['files'][$i];

            if (empty($file['id'])) {
                continue;
            }

            // only some image types
            if (!preg_match('#\.(jpg|jpeg|png|webp)$#i', $file['id'])) {
                continue;
            }

            $thumbnail = $this->getThumbnail($file['id']);

            $classes = array();
            $properties = array();
            $trigger = array();

            // add thumbnail properties
            if ($thumbnail && $thumbnail != $file['id']) {
                $classes[] = 'thumbnail';
                $properties['thumbnail-src'] = WFUtility::makePath($filesystem->getRootDir(), $thumbnail, '/');

                $dim = @getimagesize(WFUtility::makePath($browser->getBaseDir(), $thumbnail));

                if ($dim) {
                    $properties['thumbnail-width'] = $dim[0];
                    $properties['thumbnail-height'] = $dim[1];
                }
                $trigger[] = 'thumb_delete';
            } else {
                $trigger[] = 'thumb_create';
            }

            // add trigger properties
            $properties['trigger'] = implode(',', $trigger);

            $image = $filesystem->toAbsolute($file['id']);
            $description = $this->getImageDescription($image);

            if ($description) {
                $properties['description'] = $description;
            }

            $result['files'][$i] = array_merge($file,
                array(
                    'classes' => implode(' ', array_merge(explode(' ', $file['classes']), $classes)),
                    'properties' => array_merge($file['properties'], $properties),
                )
            );
        }
    }

    protected function getImageLab($file)
    {
        static $instance = array();

        if (!isset($instance[$file])) {
            $browser = $this->getFileBrowser();
            $filesystem = $browser->getFileSystem();

            if (!$filesystem->is_file($file)) {
                return false;
            }

            // get the image as data
            $data = $filesystem->read($file);

            if (!$data) {
                return null;
            }

            try {
                $image = new WFImage(null, array(
                    'preferImagick' => (bool) $this->getParam('editor.prefer_imagick', true),
                    'removeExif' => (bool) $this->getParam('editor.upload_remove_exif', false),
                    'resampleImage' => (bool) $this->getParam('editor.resample_image', false),
                ));

                $image->loadString($data);

                // get extension
                $extension = WFUtility::getExtension($file);

                // set image type
                $image->setType($extension);

                // correct orientation
                $image->orientate();

                // create backup of original image resource
                $image->backup();

                // store instance
                $instance[$file] = $image;

            } catch (Exception $e) {
                $instance[$file] = null;
                $browser->setResult($e->getMessage(), 'error');
            }
        }

        return $instance[$file];
    }

    /**
     * Resize an image
     *
     * @param [String] $file A relative path of an image
     * @param [Array] $options An array of options for resizing
     * @param [Array] $cache A persistent cache to add the image to
     * @return void
     */
    protected function resizeImage($file, $options, &$cache)
    {
        $browser = $this->getFileBrowser();
        $filesystem = $browser->getFileSystem();

        // get imagelab instance
        $instance = $this->getImageLab($file);

        // no instance was created, perhaps due to memory error?
        if (!$instance) {
            return false;
        }

        // get width
        $width = $instance->getWidth();

        // get height
        $height = $instance->getHeight();

        // get extension
        $extension = WFUtility::getExtension($file);

        // get passed in options
        extract($options);

        $count = max(count($resize_width), count($resize_height));

        // default resize crop
        $default_resize_crop = (int) $browser->get('upload_resize_crop');

        for ($i = 0; $i < $count; $i++) {
            // need at least one value
            if (!empty($resize_width[$i]) || !empty($resize_height[$i])) {

                // calculate width if not set
                if (empty($resize_width[$i])) {
                    $resize_width[$i] = round($resize_height[$i] / $height * $width, 0);
                }

                // calculate height if not set
                if (empty($resize_height[$i])) {
                    $resize_height[$i] = round($resize_width[$i] / $width * $height, 0);
                }

                // get scale based on aspect ratio
                $scale = ($width > $height) ? $resize_width[$i] / $width : $resize_height[$i] / $height;

                // don't allow scale up
                if ($scale > 1 && !$browser->get('upload_resize_enlarge')) {
                    continue;
                }

                $destination = '';

                // get file path
                $path = WFUtility::mb_dirname($file);

                // get file name
                $name = WFUtility::mb_basename($file);

                // remove file extension
                $name = WFUtility::stripExtension($name);

                if (!isset($resize_crop[$i])) {
                    $resize_crop[$i] = $default_resize_crop;
                }

                $suffix = '';

                if (empty($resize_suffix[$i])) {
                    $resize_suffix[$i] = '';
                }

                // create suffix based on width/height values for images after first
                if (empty($resize_suffix[$i]) && $i > 0) {
                    $suffix = '_' . $resize_width[$i] . '_' . $resize_height[$i];
                } else {
                    // replace width and height variables
                    $suffix = str_replace(array('$width', '$height'), array($resize_width[$i], $resize_height[$i]), $resize_suffix[$i]);
                }

                $name .= $suffix . '.' . $extension;

                // validate name
                WFUtility::checkPath($name);

                // create new destination
                $destination = WFUtility::makePath($path, $name);

                if ($resize_crop[$i]) {
                    $instance->fit($resize_width[$i], $resize_height[$i]);
                } else {
                    $instance->resize($resize_width[$i], $resize_height[$i]);
                }

                $data = $instance->toString($extension, array('quality' => $resize_quality));

                // restore image lab instance
                $instance->restore();

                if ($data) {
                    // write to file
                    if ($filesystem->write($destination, $data)) {
                        $cache[$destination] = $data;
                    } else {
                        $browser->setResult(JText::_('WF_MANAGER_RESIZE_ERROR'), 'error');
                    }
                }
            }
        }

        return true;
    }

    /**
     * Resize an array of images
     *
     * @param [Array] $files An array of relative image paths
     * @return void
     */
    public function resizeImages($files)
    {
        $files = (array) $files;

        $app = JFactory::getApplication();
        $browser = $this->getFileBrowser();
        $filesystem = $browser->getFileSystem();

        // default resize crop
        $resize_crop = (int) $browser->get('upload_resize_crop');

        // get parameter values, allow empty but fallback to system default
        $resize_width = $browser->get('upload_resize_width');
        $resize_height = $browser->get('upload_resize_height');

        // both values cannot be empty
        if (empty($resize_width) && empty($resize_height)) {
            $resize_width = 640;
            $resize_height = 480;
        }

        if (!is_array($resize_width)) {
            $resize_width = explode(',', (string) $resize_width);
        }

        if (!is_array($resize_height)) {
            $resize_height = explode(',', (string) $resize_height);
        }

        // create array of integer value
        $resize_crop = array($resize_crop);

        foreach (array('resize_width', 'resize_height', 'resize_crop') as $var) {
            $$var = $app->input->get($var, array(), 'array');
            // pass each value through intval
            $$var = array_map('intval', $$var);
        }

        $resize_suffix = $app->input->get('resize_suffix', array(), 'array');

        // clean suffix
        $resize_suffix = WFUtility::makeSafe($resize_suffix);

        $quality = (int) $browser->get('upload_resize_quality', 100);
        $resize_quality = (int) $app->input->get('resize_quality', $quality);

        $cache = array();

        foreach ($files as $file) {
            // check path
            WFUtility::checkPath($file);

            // create resize options array
            $options = compact(array('resize_width', 'resize_height', 'resize_crop', 'resize_suffix', 'resize_quality'));

            $this->resizeImage($file, $options, $cache);
        }

        return $browser->getResult();
    }

    protected function resizeUploadImage($file, &$cache)
    {
        $app = JFactory::getApplication();

        $browser = $this->getFileBrowser();
        $filesystem = $browser->getFileSystem();

        // resize state
        $resize = (int) $browser->get('upload_resize_state');

        // resize crop
        $upload_resize_crop = (int) $browser->get('upload_resize_crop');

        // get parameter values, allow empty but fallback to system default
        $resize_width = $browser->get('upload_resize_width');
        $resize_height = $browser->get('upload_resize_height');

        // both values cannot be empty
        if (empty($resize_width) && empty($resize_height)) {
            $resize_width = 640;
            $resize_height = 480;
        }

        if (!is_array($resize_width)) {
            $resize_width = explode(',', (string) $resize_width);
        }

        if (!is_array($resize_height)) {
            $resize_height = explode(',', (string) $resize_height);
        }

        // create array of integer value
        $resize_crop = array($upload_resize_crop);

        $resize_quality = (int) $browser->get('upload_resize_quality', 100);

        $resize_suffix = array();

        // dialog/form upload
        if ($app->input->getInt('inline', 0) === 0) {
            $file_resize = false;

            // Resize options visible
            if ((bool) $browser->get('upload_resize')) {
                $resize = $app->input->getInt('upload_resize_state', 0);

                // set empty default values
                $file_resize_width = array();
                $file_resize_height = array();
                $file_resize_crop = array();

                foreach (array('resize_width', 'resize_height', 'resize_crop', 'file_resize_width', 'file_resize_height', 'file_resize_crop') as $var) {
                    $$var = $app->input->get('upload_' . $var, array(), 'array');
                    // pass each value through intval
                    $$var = array_map('intval', $$var);
                }

                $resize_suffix = $app->input->get('upload_resize_suffix', array(), 'array');

                // clean suffix
                $resize_suffix = WFUtility::makeSafe($resize_suffix);

                // check for individual resize values
                foreach (array_merge($file_resize_width, $file_resize_height) as $item) {
                    // at least one value set, so resize
                    if (!empty($item)) {
                        $file_resize = true;

                        break;
                    }
                }

                // transfer values
                if ($file_resize) {
                    $resize_width = $file_resize_width;
                    $resize_height = $file_resize_height;
                    $resize_crop = $file_resize_crop;

                    // get file resize suffix
                    $file_resize_suffix = $app->input->get('upload_file_resize_suffix', array(), 'array');

                    // clean suffix
                    $file_resize_suffix = WFUtility::makeSafe($file_resize_suffix);

                    // transfer values
                    $resize_suffix = $file_resize_suffix;

                    // set global resize option
                    $resize = true;
                }
            }
        }

        // no resizing, return empty array
        if (!$resize) {
            return false;
        }

        // create resize options array
        $options = compact(array('resize_width', 'resize_height', 'resize_crop', 'resize_suffix', 'resize_quality'));

        $this->resizeImage($file, $options, $cache);

        return true;
    }

    protected function watermarkImage($file, &$cache)
    {
        $app = JFactory::getApplication();
        $browser = $this->getFileBrowser();
        $filesystem = $browser->getFileSystem();

        // get imagelab instance
        $instance = $this->getImageLab($file);

        // no instance was created, perhaps due to memory error?
        if (!$instance) {
            return false;
        }

        // get extension
        $extension = WFUtility::getExtension($file);

        // map of options and default values
        $vars = array(
            'type' => 'text',
            'text' => '',
            'font_style' => 'LiberationSans-Regular.ttf',
            'font_size' => '32',
            'font_color' => '#FFFFFF',
            'opacity' => 50,
            'position' => 'center',
            'margin' => 10,
            'angle' => 0,
            'image' => '',
        );

        // process options with passed in values or parameters
        foreach ($vars as $key => $value) {
            $value = $app->input->get('watermark_' . $key, $this->getParam('editor.watermark_' . $key, $value));

            if ($key == 'font_style') {
                // default LiberationSans fonts
                if (preg_match('#^LiberationSans-(Regular|Bold|BoldItalic|Italic)\.ttf$#', $value)) {
                    $value = WFUtility::makePath(WF_EDITOR_LIBRARIES, '/pro/fonts/' . $value);
                    // custom font
                } else {
                    $value = WFUtility::makePath(JPATH_SITE, $value);
                }
            }

            if ($key == 'image') {
                if (strpos($value, '://') !== false) {
                    $value = '';
                } else {
                    $value = WFUtility::makePath(JPATH_SITE, $value);
                }
            }

            $options[$key] = $value;
        }

        // should image quality be set?
        $quality = (int) $this->getParam('editor.upload_quality', 100);

        // watermark
        foreach ($cache as $destination => $data) {
            // load processed data if available
            if ($data) {
                $instance->loadString($data);
            }

            $instance->watermark($options);

            $data = $instance->toString($extension, array('quality' => $quality));

            // valid data string
            if ($data) {
                // write to file and update cache
                if ($filesystem->write($destination, $data)) {
                    $cache[$destination] = $data;
                } else {
                    $browser->setResult(JText::_('WF_MANAGER_WATERMARK_ERROR'), 'error');
                }
            }

            // restore backup resource
            $instance->restore();
        }

        return true;
    }

    public function watermarkImages($files)
    {
        $files = (array) $files;

        $cache = array();

        foreach ($files as $file) {
            $cache[$file] = '';

            $this->watermarkImage($file, $cache);
        }

        return true;
    }

    protected function watermarkUploadImage($file, &$cache)
    {
        $app = JFactory::getApplication();

        $browser = $this->getFileBrowser();
        $filesystem = $browser->getFileSystem();

        // watermark state
        $watermark = (int) $browser->get('upload_watermark_state');

        // option visible so allow user set value
        if ((bool) $browser->get('upload_watermark')) {
            $watermark = $app->input->getInt('upload_watermark_state', 0);
        }

        // no watermark, return false
        if (!$watermark) {
            return false;
        }

        // if the files array is empty, no resizing was done, create a new one for further processing
        if (empty($cache)) {
            $cache = array(
                $file => '',
            );
        }

        $this->watermarkImage($file, $cache);

        return true;
    }

    protected function thumbnailUploadImage($file, &$cache)
    {
        $app = JFactory::getApplication();

        $browser = $this->getFileBrowser();
        $filesystem = $browser->getFileSystem();

        // get extension
        $extension = WFUtility::getExtension($file);

        $thumbnail = (int) $browser->get('upload_thumbnail_state');

        // get parameter values, allow empty but fallback to system default
        $tw = $browser->get('upload_thumbnail_width');
        $th = $browser->get('upload_thumbnail_height');

        // both values cannot be empty
        if (empty($tw) && empty($th)) {
            $tw = 120;
            $th = 90;
        }

        $crop = $browser->get('upload_thumbnail_crop');

        // Thumbnail options visible
        if ((bool) $browser->get('upload_thumbnail')) {
            $thumbnail = $app->input->getInt('upload_thumbnail_state', 0);

            $tw = $app->input->getInt('upload_thumbnail_width');
            $th = $app->input->getInt('upload_thumbnail_height');

            // Crop Thumbnail
            $crop = $app->input->getInt('upload_thumbnail_crop', 0);
        }

        // not activated
        if (!$thumbnail) {
            return false;
        }

        $tq = $browser->get('upload_thumbnail_quality');

        // cast values to integer
        $tw = (int) $tw;
        $th = (int) $th;

        // need at least one value
        if ($tw || $th) {

            // get imagelab instance
            $instance = $this->getImageLab($file);

            // no instance was created, perhaps due to memory error?
            if (!$instance) {
                $browser->setResult(JText::_('WF_IMGMANAGER_EXT_THUMBNAIL_ERROR'), 'error');
                return false;
            }

            // if the files array is empty, no other processing was done, create a new one for further processing
            if (empty($cache)) {
                $cache = array(
                    $file => '',
                );
            }

            foreach ($cache as $destination => $data) {
                // if image data is available, load it
                if ($data) {
                    $instance->loadString($data);
                }

                $thumb = WFUtility::makePath($this->getThumbDir($destination, true), $this->getThumbName($destination));

                $w = $instance->getWidth();
                $h = $instance->getHeight();

                // calculate width if not set
                if (!$tw) {
                    $tw = round($th / $h * $w, 0);
                }

                // calculate height if not set
                if (!$th) {
                    $th = round($tw / $w * $h, 0);
                }

                if ($crop) {
                    $instance->fit($tw, $th);
                } else {
                    $instance->resize($tw, $th);
                }

                $data = $instance->toString($extension, array('quality' => $tq));

                if ($data) {
                    // write to file
                    if (!$filesystem->write($thumb, $data)) {
                        $browser->setResult(JText::_('WF_IMGMANAGER_EXT_THUMBNAIL_ERROR'), 'error');
                    }
                }

                // restore backup resource
                $instance->restore();
            }
        }

        return true;
    }

    /**
     * Special function to determine whether an image can be resampled, as this required Imagick support
     *
     * @return boolean
     */
    protected function canResampleImage()
    {
        $resample = (bool) $this->getParam('editor.resample_image', false);
        $imagick = (bool) $this->getParam('editor.prefer_imagick', true);

        return $resample && $imagick && extension_loaded('imagick');
    }

    public function onUpload($file, $relative = '')
    {
        // get file extension
        $ext = WFUtility::getExtension($file);

        // must be an image
        if (!in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'apng', 'webp'])) {
            return array();
        }

        // get filesystem reference
        $filesystem = $this->getFileBrowser()->getFileSystem();

        // make file path relative
        $file = $filesystem->toRelative($file);

        // a cache of processed files. This includes the original file, and any others created by resizing
        $cache = array();

        // process image resize
        $this->resizeUploadImage($file, $cache);

        // process thumbnails
        $this->thumbnailUploadImage($file, $cache);

        // process image watermark
        $this->watermarkUploadImage($file, $cache);

        // should image quality be set?
        $upload_quality = (int) $this->getParam('editor.upload_quality', 100);

        // should the image be resampled?
        $upload_resample = $this->canResampleImage();

        if (empty($cache)) {
            // are we resampling or setting upload quality?
            if ($upload_resample || $upload_quality < 100) {
                // get filesystem reference
                $filesystem = $this->getFileBrowser()->getFileSystem();

                // get imagelab instance
                $instance = $this->getImageLab($file);

                if ($instance) {
                    $cache = array(
                        $file => '',
                    );

                    foreach ($cache as $destination => $data) {
                        if ($data) {
                            $instance->loadString($data);
                        }

                        $options = array();

                        if ($upload_quality < 100) {
                            $options['quality'] = $upload_quality;
                        }

                        $data = $instance->toString($ext, $options);

                        if ($data) {
                            $filesystem->write($destination, $data);
                        }
                    }
                }

            }
        }

        if (!empty($cache)) {
            $instance = $this->getImageLab($file);

            if ($instance) {
                $instance->destroy();
            }
        }

        return array();
    }

    private function toRelative($file)
    {
        return WFUtility::makePath(str_replace(JPATH_ROOT . '/', '', WFUtility::mb_dirname(JPath::clean($file))), WFUtility::mb_basename($file));
    }

    private function cleanTempDirectory()
    {
        $files = JFolder::files($this->getCacheDirectory(), '^(wf_ie_)([a-z0-9]+)\.(jpg|jpeg|gif|png|webp)$');

        if (!empty($files)) {
            $time = strtotime('24 hours ago');
            clearstatcache();
            foreach ($files as $file) {
                // delete files older than 24 hours
                if (@filemtime($file) >= $time) {
                    @JFile::delete($file);
                }
            }
        }
    }

    public function cleanEditorTmp($file = null, $exit = true)
    {
        // Check for request forgeries
        JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

        // check for image editor access
        if ($this->checkAccess('image_editor', 1) === false) {
            throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
        }

        if ($file) {
            $ext = WFUtility::getExtension($file);

            // create temp file
            $tmp = 'wf_ie_' . md5($file) . '.' . $ext;
            $path = WFUtility::makePath($this->getCacheDirectory(), $tmp);

            self::validateFilePath($file);

            $result = false;

            if (is_file($path)) {
                $result = @JFile::delete($path);
            }

            if ($exit) {
                return (bool) $result;
            }
        } else {
            $this->cleanTempDirectory();
        }

        return true;
    }

    /**
     * Apply an image edit to a file and return a url to a temp version of that file
     *
     * @param [string] $file The name of the file being edited
     * @param [string] $task The edit type to apply, eg: resize
     * @param [object] $value The edit value to apply
     * @return WFFileSystemResult
     */
    public function applyImageEdit($file, $task, $value)
    {
        // Check for request forgeries
        JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

        // check for image editor access
        if ($this->checkAccess('image_editor', 1) === false) {
            throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
        }

        $app = JFactory::getApplication();

        $browser = $this->getFileBrowser();

        // check file
        self::validateFilePath($file);

        $upload = $app->input->files->get('file', array(), 'array');

        // create a filesystem result object
        $result = new WFFileSystemResult();

        if (isset($upload) && isset($upload['tmp_name']) && is_uploaded_file($upload['tmp_name'])) {
            self::validateFile($upload);

            $ext = WFUtility::getExtension($file);

            // create temp file
            $tmp = 'wf_ie_' . md5($file) . '.' . $ext;
            $tmp = WFUtility::makePath($this->getCacheDirectory(), $tmp);

            // delete existing tmp file
            if (is_file($tmp)) {
                @JFile::delete($tmp);
            }

            $image = new WFImage(null, array(
                'preferImagick' => (bool) $this->getParam('editor.prefer_imagick', true),
            ));

            $image->loadFile($upload['tmp_name']);
            $image->setType($ext);

            switch ($task) {
                case 'resize':
                    $image->resize($value->width, $value->height);
                    break;
                case 'crop':
                    $image->crop($value->width, $value->height, $value->x, $value->y, false, 1);
                    break;
            }

            // get image data
            $data = $image->toString($ext);

            // write to file
            if ($data) {
                $result->state = (bool) @JFile::write($tmp, $data);
            }

            if ($result->state === true) {
                $tmp = str_replace(WFUtility::cleanPath(JPATH_SITE), '', $tmp);
                $browser->setResult(WFUtility::cleanPath($tmp, '/'), 'files');
            } else {
                $browser->setResult(JText::_('WF_IMAGE_EDIT_APPLY_ERROR'), 'error');
            }

            @unlink($upload['tmp_name']);

            return $browser->getResult();
        }
    }

    public function saveImageEdit($file, $name, $options = array(), $quality = 100)
    {
        // Check for request forgeries
        JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

        // check for image editor access
        if ($this->checkAccess('image_editor', 1) === false) {
            throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
        }

        $app = JFactory::getApplication();

        $browser = $this->getFileBrowser();
        $filesystem = $browser->getFileSystem();

        // check file
        self::validateFilePath($file);

        // clean temp
        $this->cleanEditorTmp($file, false);

        // check new name
        self::validateFilePath($name);

        $upload = $app->input->files->get('file', '', 'files', 'array');

        // create a filesystem result object
        $result = new WFFileSystemResult();

        if (isset($upload) && isset($upload['tmp_name']) && is_uploaded_file($upload['tmp_name'])) {
            $tmp = $upload['tmp_name'];

            self::validateFile($upload);
            $result = $filesystem->upload('multipart', trim($tmp), WFUtility::mb_dirname($file), $name);

            @unlink($tmp);
        } else {
            // set upload as false - JSON request
            $upload = false;

            $file = WFUtility::makePath($filesystem->getBaseDir(), $file);
            $dest = WFUtility::mb_dirname($file) . '/' . WFUtility::mb_basename($name);

            // get extension
            $ext = WFUtility::getExtension($dest);

            // create image
            $image = $this->getImageLab($file);

            foreach ($options as $filter) {
                if (isset($filter->task)) {
                    $args = isset($filter->args) ? (array) $filter->args : array();

                    switch ($filter->task) {
                        case 'resize':
                            $w = $args[0];
                            $h = $args[1];

                            $image->resize($w, $h);
                            break;
                        case 'crop':
                            $w = $args[0];
                            $h = $args[1];

                            $x = $args[2];
                            $y = $args[3];

                            $image->crop($w, $h, $x, $y);
                            break;
                        case 'rotate':
                            $image->rotate(array_shift($args));
                            break;
                        case 'flip':
                            $image->flip(array_shift($args));
                            break;
                    }
                }
            }

            // get image data
            $data = $image->toString($ext);

            // make path relative
            $dest = $filesystem->toRelative($dest);

            // write to file
            if ($data) {
                $result->state = (bool) $filesystem->write($dest, $data);
            }

            // set path
            $result->path = $dest;
        }

        if ($result->state === true) {
            // check if its a valid image
            if (@getimagesize($result->path) === false) {
                JFile::delete($result->path);
                throw new InvalidArgumentException('Invalid image file');
            } else {
                $result->path = str_replace(WFUtility::cleanPath(JPATH_SITE), '', $result->path);
                $browser->setResult(WFUtility::cleanPath($result->path, '/'), 'files');
            }
        } else {
            $browser->setResult($result->message || JText::_('WF_MANAGER_EDIT_SAVE_ERROR'), 'error');
        }

        // return to WFRequest
        return $browser->getResult();
    }

    public function saveTextFile($file, $name)
    {
        // Check for request forgeries
        JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

        // check for text editor access
        if ($this->checkAccess('text_editor', 0) === false) {
            throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
        }

        // check file
        self::validateFilePath($file);

        // check new name
        self::validateFilePath($name);

        // check for allowed file types
        if (!preg_match('#\.(txt|html|htm|xml|md|csv|json)$#i', $name)) {
            throw new Exception(JText::_('WF_MANAGER_EDIT_TEXT_SAVE_INVALID'));
        }

        // get permitted file types
        $types = $this->getFileTypes();

        // validate extension against file types
        if (!in_array(WFUtility::getExtension(strtolower($file)), $types)) {
            throw new Exception(JText::_('WF_MANAGER_EDIT_TEXT_SAVE_INVALID'));
        }

        $app = JFactory::getApplication();

        $browser = $this->getFileBrowser();
        $filesystem = $browser->getFileSystem();

        // create a filesystem result object
        $result = new WFFileSystemResult();

        $file = WFUtility::makePath($filesystem->getBaseDir(), $file);
        $dest = WFUtility::mb_dirname($file) . '/' . WFUtility::mb_basename($name);

        // make path relative
        $dest = $filesystem->toRelative($dest);

        $data = $app->input->post->get('data', '', 'RAW');
        $data = rawurldecode($data);

        // write to file
        if ($data) {
            $result->state = (bool) $filesystem->write($dest, $data);
        }

        // set path
        $result->path = $dest;

        if ($result->state === true) {
            $result->path = str_replace(WFUtility::cleanPath(JPATH_SITE), '', $result->path);
            $browser->setResult(WFUtility::cleanPath($result->path, '/'), 'files');
        } else {
            $browser->setResult($result->message || JText::_('WF_MANAGER_EDIT_TEXT_SAVE_ERROR'), 'error');
        }

        // return to WFRequest
        return $browser->getResult();
    }

    private function getCacheDirectory()
    {
        $app = JFactory::getApplication();

        jimport('joomla.filesystem.folder');

        $cache = $app->getCfg('tmp_path');
        $dir = $this->getParam('editor.cache', $cache);

        // make sure a value is set
        if (empty($dir)) {
            $dir = 'tmp';
        }

        // check for and create absolute path
        if (strpos($dir, JPATH_SITE) === false) {
            $dir = WFUtility::makePath(JPATH_SITE, JPath::clean($dir));
        }

        if (!is_dir($dir)) {
            if (@JFolder::create($dir)) {
                return $dir;
            }
        }

        return $dir;
    }

    private function cleanCacheDir()
    {
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');

        $cache_max_size = intval($this->getParam('editor.cache_size', 10, 0)) * 1024 * 1024;
        $cache_max_age = intval($this->getParam('editor.cache_age', 30, 0)) * 86400;
        $cache_max_files = intval($this->getParam('editor.cache_files', 0, 0));

        if ($cache_max_age > 0 || $cache_max_size > 0 || $cache_max_files > 0) {
            $path = $this->getCacheDirectory();
            $files = JFolder::files($path, '^(wf_thumb_cache_)([a-z0-9]+)\.(jpg|jpeg|gif|png|webp)$');
            $num = count($files);
            $size = 0;
            $cutofftime = time() - 3600;

            if ($num) {
                foreach ($files as $file) {
                    $file = WFUtility::makePath($path, $file);
                    if (is_file($file)) {
                        $ftime = @fileatime($file);
                        $fsize = @filesize($file);
                        if ($fsize == 0 && $ftime < $cutofftime) {
                            @JFile::delete($file);
                        }
                        if ($cache_max_files > 0) {
                            if ($num > $cache_max_files) {
                                @JFile::delete($file);
                                --$num;
                            }
                        }
                        if ($cache_max_age > 0) {
                            if ($ftime < (time() - $cache_max_age)) {
                                @JFile::delete($file);
                            }
                        }
                        if ($cache_max_files > 0) {
                            if (($size + $fsize) > $cache_max_size) {
                                @JFile::delete($file);
                            }
                        }
                    }
                }
            }
        }

        return true;
    }

    private function redirectThumb($file, $mime)
    {
        if (is_file($file)) {
            header('Content-length: ' . filesize($file));
            header('Content-type: ' . $mime);
            header('Location: ' . $this->toRelative($file));
        }
    }

    private function outputImage($file, $mime)
    {
        if (is_file($file)) {
            header('Content-length: ' . filesize($file));
            header('Content-type: ' . $mime);
            ob_clean();
            flush();

            @readfile($file);
        }

        exit();
    }

    private function getCacheThumbPath($file, $width, $height)
    {
        jimport('joomla.filesystem.file');

        $mtime = @filemtime($file);
        $thumb = 'wf_thumb_cache_' . md5(WFUtility::mb_basename(WFUtility::stripExtension($file)) . $mtime . $width . $height) . '.' . WFUtility::getExtension($file);

        return WFUtility::makePath($this->getCacheDirectory(), $thumb);
    }

    private function createCacheThumb($file)
    {
        jimport('joomla.filesystem.file');

        $browser = $this->getFileBrowser();

        // check path
        WFUtility::checkPath($file);

        $extension = WFUtility::getExtension($file);

        // lowercase extension
        $extension = strtolower($extension);

        // not an image
        if (!in_array($extension, array('jpeg', 'jpeg', 'png', 'tiff', 'gif', 'webp'))) {
            exit();
        }

        $file = WFUtility::makePath($browser->getBaseDir(), $file);

        // default for list thumbnails
        $width = 100;
        $height = 100;
        $quality = 75;

        $info = @getimagesize($file);

        // not a valid image?
        if (!$info) {
            exit();
        }

        list($w, $h, $type, $text, $mime) = $info;

        // smaller than thumbnail so output file instead
        if (($w < $width && $h < $height)) {
            return $this->outputImage($file, $mime);
        }

        $exif_types = array('jpg', 'jpeg', 'tiff');

        // try exif thumbnail
        if (in_array($extension, $exif_types)) {
            $exif = exif_thumbnail($file, $width, $height, $mime);

            if ($exif !== false) {
                header('Content-type: ' . $mime);
                die($exif);
            }
        }

        $thumb = $this->getCacheThumbPath($file, $width, $height);

        if (is_file($thumb)) {
            return $this->outputImage($thumb, $mime);
        }

        // create thumbnail file
        $image = new WFImage($file, array(
            'preferImagick' => (bool) $this->getParam('editor.prefer_imagick', true),
        ));

        $image->fit($width, $height);

        if ($image->toFile($thumb, $extension, array('quality' => $quality))) {
            if (is_file($thumb)) {
                return $this->outputImage($thumb, $mime);
            }
        }

        // exit with no data
        exit();
    }

    public function getThumbnails($files)
    {
        $browser = $this->getFileBrowser();

        jimport('joomla.filesystem.file');

        $thumbnails = array();
        foreach ($files as $file) {
            $thumbnails[$file['name']] = $this->getCacheThumb(WFUtility::makePath($browser->getBaseDir(), $file['url']), true, 50, 50, WFUtility::getExtension($file['name']), 50);
        }

        return $thumbnails;
    }

    protected static function validateFile($file)
    {
        return WFUtility::isSafeFile($file);
    }

    /**
     * Validate an image path and extension.
     *
     * @param type $path Image path
     *
     * @throws InvalidArgumentException
     */
    protected static function validateFilePath($path)
    {
        // nothing to validate
        if (empty($path)) {
            return false;
        }

        // clean path
        $path = WFUtility::cleanPath($path);

        // check file path
        WFUtility::checkPath($path);

        // check file name and contents
        WFUtility::validateFileName($path);
    }

    /**
     * Get an image's thumbnail file name.
     *
     * @param string $file the full path to the image file
     *
     * @return string of the thumbnail file
     */
    protected function getThumbName($file)
    {
        $prefix = $this->getParam('thumbnail_prefix', 'thumb_$');

        $ext = WFUtility::getExtension($file);

        if (strpos($prefix, '$') !== false) {
            return str_replace('$', WFUtility::mb_basename($file, '.' . $ext), $prefix) . '.' . $ext;
        }

        return (string) $prefix . WFUtility::mb_basename($file);
    }

    protected function getThumbDir($file, $create)
    {
        $browser = $this->getFileBrowser();
        $filesystem = $browser->getFileSystem();

        // get base directory from editor parameter
        $baseDir = $this->getParam('editor.thumbnail_folder', '', 'thumbnails');

        // get directory from plugin parameter, if any (Image Manager Extended)
        $folder = $this->getParam($this->getName() . '.thumbnail_folder', '', '$$');

        // ugly workaround for parameter issues - a $ or $$ value denotes un unset value, so fallback to global
        // a user can "unset" the value, if it has been stored as an empty string, by setting the value to $
        if ($folder === "$" || $folder === "$$") {
            $folder = $baseDir;
        }

        // make path relative to source file
        $dir = WFUtility::makePath(WFUtility::mb_dirname($file), $folder);

        // create the folder if it does not exist
        if ($create && !$filesystem->exists($dir)) {
            $filesystem->createFolder(WFUtility::mb_dirname($dir), WFUtility::mb_basename($dir));
        }

        return $dir;
    }

    /**
     * Create a thumbnail.
     *
     * @param string $file    relative path of the image
     * @param string $width   thumbnail width
     * @param string $height  thumbnail height
     * @param string $quality thumbnail quality (%)
     * @param string $mode    thumbnail mode
     */
    public function createThumbnail($file, $width = null, $height = null, $quality = 100, $box = null)
    {
        // check path
        self::validateFilePath($file);

        $browser = $this->getFileBrowser();
        $filesystem = $browser->getFileSystem();

        $thumb = WFUtility::makePath($this->getThumbDir($file, true), $this->getThumbName($file));

        $extension = WFUtility::getExtension($file);

        $instance = $this->getImageLab($file);

        if ($instance) {
            if ($box) {
                $box = (array) $box;
                $instance->crop($box['sw'], $box['sh'], $box['sx'], $box['sy']);
            }

            $instance->resize($width, $height);

            $data = $instance->toString($extension, array('quality' => $quality));

            if ($data) {
                // write to file
                if (!$filesystem->write($thumb, $data)) {
                    $browser->setResult(JText::_('WF_IMGMANAGER_EXT_THUMBNAIL_ERROR'), 'error');
                }
            }
        }

        return $browser->getResult();
    }

    /**
     * Creates thumbnails for an array of files.
     *
     * @param array $files  relative path of the image
     */
    public function createThumbnails($files)
    {
        $files = (array) $files;

        $app = JFactory::getApplication();
        $browser = $this->getFileBrowser();
        $filesystem = $browser->getFileSystem();

        $tw = $app->input->getInt('thumbnail_width');
        $th = $app->input->getInt('thumbnail_height');

        // Crop Thumbnail
        $crop = $app->input->getInt('thumbnail_crop', 0);

        $tq = $browser->get('upload_thumbnail_quality');

        // need at least one value
        if ($tw || $th) {

            foreach ($files as $file) {

                // check path
                WFUtility::checkPath($file);

                // get extension
                $extension = WFUtility::getExtension($file);

                // get imagelab instance
                $instance = $this->getImageLab($file);

                // no instance was created, perhaps due to memory error?
                if (!$instance) {
                    $browser->setResult(JText::_('WF_IMGMANAGER_EXT_THUMBNAIL_ERROR'), 'error');
                    return false;
                }

                $thumb = WFUtility::makePath($this->getThumbDir($file, true), $this->getThumbName($file));

                $w = $instance->getWidth();
                $h = $instance->getHeight();

                // calculate width if not set
                if (!$tw) {
                    $tw = round($th / $h * $w, 0);
                }

                // calculate height if not set
                if (!$th) {
                    $th = round($tw / $w * $h, 0);
                }

                if ($crop) {
                    $instance->fit($tw, $th);
                } else {
                    $instance->resize($tw, $th);
                }

                $data = $instance->toString($extension, array('quality' => $tq));

                if ($data) {
                    // write to file
                    if (!$filesystem->write($thumb, $data)) {
                        $browser->setResult(JText::_('WF_IMGMANAGER_EXT_THUMBNAIL_ERROR'), 'error');
                    }
                }
            }
        }

        return $browser->getResult();
    }

    /**
     * Remove exif data from an image by rewriting it. This will also rotate images to correct orientation.
     *
     * @param $file Absolute path to the image file
     *
     * @return bool
     */
    private function removeExifData($file)
    {
        $exif = null;

        // check if exif_read_data disabled...
        if (function_exists('exif_read_data')) {

            // get exif data
            $exif = @exif_read_data($file, 'EXIF');
            $rotate = 0;

            if ($exif && !empty($exif['Orientation'])) {
                $orientation = (int) $exif['Orientation'];

                // Fix Orientation
                switch ($orientation) {
                    case 3:
                        $rotate = 180;
                        break;
                    case 6:
                        $rotate = 90;
                        break;
                    case 8:
                        $rotate = 270;
                        break;
                }
            }
        }

        if (extension_loaded('imagick')) {
            try {
                $img = new Imagick($file);

                if ($rotate) {
                    $img->rotateImage(new ImagickPixel(), $rotate);
                }

                $img->stripImage();

                $img->writeImage($file);
                $img->clear();
                $img->destroy();

                return true;
            } catch (Exception $e) {
            }
        } elseif (extension_loaded('gd')) {
            try {

                $handle = imagecreatefromjpeg($file);

                // extended resource check
                if (!((is_object($handle) && get_class($handle) == 'GdImage') || (is_resource($handle) && get_resource_type($handle) == 'gd'))) {
                    return false;
                }

                if ($rotate) {
                    $rotation = imagerotate($handle, -$rotate, 0);

                    if ($rotation) {
                        $handle = $rotation;
                    }
                }

                imagejpeg($handle, $file);
                @imagedestroy($handle);

                return true;

            } catch (Exception $e) {
            }
        }

        return false;
    }

    protected function getFileBrowserConfig($config = array())
    {
        $resize_width = $this->getParam('editor.resize_width', '', 640);

        if (!is_array($resize_width)) {
            $resize_width = explode(',', (string) $resize_width);
        }

        $resize_height = $this->getParam('editor.resize_height', '', 480);

        if (!is_array($resize_height)) {
            $resize_height = explode(',', (string) $resize_height);
        }

        $data = array(
            'view_mode' => $this->getParam('editor.mode', 'list'),
            'can_edit_images' => $this->get('can_edit_images'),
            'cache_enable' => $this->getParam('editor.cache_enable', 0),
            // Upload
            'upload_resize' => $this->getParam('editor.upload_resize', 1),
            'upload_resize_state' => $this->getParam('editor.upload_resize_state', 0),
            // value must be cast as string for javascript processing
            'upload_resize_width' => $resize_width,
            // value must be cast as string for javascript processing
            'upload_resize_height' => $resize_height,
            'upload_resize_quality' => $this->getParam('editor.resize_quality', 100),
            'upload_resize_crop' => $this->getParam('editor.upload_resize_crop', 0),
            'upload_resize_enlarge' => $this->getParam('editor.upload_resize_enlarge', 0),
            // watermark
            'upload_watermark' => $this->getParam('editor.upload_watermark', 0),
            'upload_watermark_state' => $this->getParam('editor.upload_watermark_state', 0),
            // thumbnail
            'upload_thumbnail' => $this->getParam('editor.upload_thumbnail', 1),
            'upload_thumbnail_state' => $this->getParam('editor.upload_thumbnail_state', 0),
            'upload_thumbnail_crop' => $this->getParam('editor.upload_thumbnail_crop', 0),
            // value must be cast as string for javascript processing
            'upload_thumbnail_width' => (string) $this->getParam('editor.upload_thumbnail_width', '', 120),
            // value must be cast as string for javascript processing
            'upload_thumbnail_height' => (string) $this->getParam('editor.upload_thumbnail_height', '', 90),
            'upload_thumbnail_quality' => $this->getParam('editor.upload_thumbnail_quality', 80),
        );

        $config = WFUtility::array_merge_recursive_distinct($data, $config);

        return parent::getFileBrowserConfig($config);
    }

    /**
     * Check for the thumbnail for a given file.
     *
     * @param string $relative The relative path of the file
     *
     * @return The thumbnail URL or false if none
     */
    private function getThumbnail($relative)
    {
        // get browser
        $browser = $this->getFileBrowser();
        $filesystem = $browser->getFileSystem();

        $path = WFUtility::makePath($browser->getBaseDir(), $relative);
        $dim = @getimagesize($path);

        if (empty($dim)) {
            return false;
        }

        /*$thumbfolder = $this->getParam('thumbnail_folder', '', 'thumbnails');

        $dir = WFUtility::makePath(str_replace('\\', '/', dirname($relative)), $thumbfolder);
        $thumbnail = WFUtility::makePath($dir, $this->getThumbName($relative));*/

        $thumbnail = $this->getThumbPath($relative);

        // Image is a thumbnail
        if ($relative === $thumbnail) {
            return $relative;
        }

        // The original image is smaller than a thumbnail so just return the url to the original image.
        if ($dim[0] <= $this->getParam('thumbnail_size', 120) && $dim[1] <= $this->getParam('thumbnail_size', 90)) {
            return $relative;
        }

        //check for thumbnails, if exists return the thumbnail url
        if (file_exists(WFUtility::makePath($browser->getBaseDir(), $thumbnail))) {
            return $thumbnail;
        }

        return false;
    }

    private function getThumbPath($file)
    {
        return WFUtility::makePath($this->getThumbDir($file, false), $this->getThumbName($file));
    }

    public function onFilesDelete($file)
    {
        $browser = $this->getFileBrowser();

        if (file_exists(WFUtility::makePath($browser->getBaseDir(), $this->getThumbPath($file)))) {
            $this->deleteThumbnail($file);
        }

        return array();
    }

    public function getThumbnailDimensions($file)
    {
        return $this->getDimensions($this->getThumbPath($file));
    }

    public function deleteThumbnail($files)
    {
        if (!$this->checkAccess('thumbnail_editor', 1)) {
            throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
        }

        $files = (array) $files;

        for ($i = 0; $i < count($files); $i++) {
            $file = $files[$i];

            // check path
            WFUtility::checkPath($file);

            $browser = $this->getFileBrowser();
            $filesystem = $browser->getFileSystem();
            $dir = $this->getThumbDir($file, false);

            $thumb = $this->getThumbPath($file);

            if ($browser->deleteItem($thumb)) {
                if ($i == count($files) - 1) {
                    if ($filesystem->countFiles($dir) == 0 && $filesystem->countFolders($dir) == 0) {
                        if (!$browser->deleteItem($dir)) {
                            $browser->setResult(JText::_('WF_IMGMANAGER_EXT_THUMBNAIL_FOLDER_DELETE_ERROR'), 'error');
                        }
                    }
                }
            }
        }

        return $browser->getResult();
    }
}
