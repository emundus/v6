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

require_once WF_EDITOR_LIBRARIES . '/classes/manager.php';
require_once WF_EDITOR_LIBRARIES . '/classes/extensions/popups.php';

class WFImgManagerExtPlugin extends WFMediaManager
{
    public $_filetypes = 'jpg,jpeg,png,apng,gif,webp,avif';

    protected $name = 'imgmanager_ext';

    public function __construct($config = array())
    {
        $config = array(
            'can_edit_images' => 1,
            'show_view_mode' => 1,
            'colorpicker' => true,
        );

        parent::__construct($config);

        $app = JFactory::getApplication();

        $request = WFRequest::getInstance();

        if ($app->input->getCmd('dialog', 'plugin') === 'plugin') {
            $this->addFileBrowserEvent('onUpload', array($this, 'onUpload'));
        }

        $request->setRequest(array($this, 'getImageProperties'));
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

        if ($this->getParam('imgmanager_ext.insert_multiple', 1)) {
            $this->addFileBrowserButton('file', 'insert_multiple', array('action' => 'selectMultiple', 'title' => JText::_('WF_BUTTON_INSERT_MULTIPLE'), 'multiple' => true, 'single' => false, 'icon' => 'multiple-images'));
        }

        parent::display();

        $document = WFDocument::getInstance();

        // create new tabs instance
        $tabs = WFTabs::getInstance(array(
            'base_path' => WF_EDITOR_PLUGINS . '/imgmanager',
        ));

        // Add tabs
        $tabs->addTab('image', 1, array('plugin' => $this));

        if ($this->allowEvents()) {
            $tabs->addTab('rollover', $this->getParam('tabs_rollover', 1));
        }
        $tabs->addTab('advanced', $this->getParam('tabs_advanced', 1));

        $document->addScript(array('imgmanager'), 'plugins');
        $document->addStyleSheet(array('imgmanager'), 'plugins');

        $document->addScriptDeclaration('ImageManagerDialog.settings=' . json_encode($this->getSettings()) . ';');

        // Load Popups instance
        $popups = WFPopupsExtension::getInstance(array(
            // map src value to popup link href
            'map' => array('href' => 'popup_src'),
            // set text to false
            'text' => false,
            // set url to true
            'url' => true,
            // default popup option
            'default' => $this->getParam('imgmanager_ext.popups.default', ''),
        ));

        $popups->display();

        if ($this->getParam('tabs_responsive', 1)) {
            $tabs->addTemplatePath(WF_EDITOR_PLUGINS . '/imgmanager_ext/tmpl');

            // Add tabs
            $tabs->addTab('responsive', 1, array('plugin' => $this));
        }
    }

    public function onUpload($file, $relative = '')
    {
        parent::onUpload($file, $relative);

        $app = JFactory::getApplication();

        if ($app->input->getInt('inline', 0) === 1) {
            $result = array(
                'file' => $relative,
                'name' => WFUtility::mb_basename($file),
            );

            if ($this->getParam('imgmanager_ext.always_include_dimensions', 1)) {
                $dim = @getimagesize($file);

                if ($dim) {
                    $result['width'] = $dim[0];
                    $result['height'] = $dim[1];
                }
            }
            
            // exif description
            $description = $this->getImageDescription($file);

            if ($description) {
                $result['alt'] = $description;
            }

            return array_merge($result, array('attributes' => $this->getDefaultAttributes()));
        }

        return array();
    }

    private function getThumbnailOptions()
    {
        $options = array();

        $values = array(
            'thumbnail_width' => 120,
            'thumbnail_height' => 90,
            'thumbnail_quality' => 80,
        );

        $states = array(
            'upload_thumbnail' => 1,
            'upload_thumbnail_state' => 0,
            'upload_thumbnail_crop' => 0,
        );

        foreach ($values as $key => $default) {
            $fallback = $this->getParam('editor.upload_' . $key, '', '$');
            $value = $this->getParam('imgmanager_ext.' . $key, '', '$');

            // indicates an unset value, so use the global value or default
            if ($value === '$') {
                $value = $fallback === '$' ? $default : $fallback;
            }

            $options['upload_' . $key] = $value;
        }

        // unset thumbnail width and height if both are empty, use global values
        if ($options['upload_thumbnail_width'] === '' && $options['upload_thumbnail_height'] === '') {
            unset($options['upload_thumbnail_width']);
            unset($options['upload_thumbnail_height']);
        }

        foreach ($states as $key => $default) {
            $value = $this->getParam('editor.' . $key, $default);
            $options[$key] = $this->getParam('imgmanager_ext.' . $key, '');

            // if the value is empty (unset), use the global value or default
            if ($options[$key] === '') {
                $options[$key] = $value;
            }
        }

        return $options;
    }

    public function getDefaultAttributes()
    {
        $attribs = parent::getDefaultAttributes();

        unset($attribs['always_include_dimensions']);

        return $attribs;
    }

    public function getImageProperties()
    {
        return $this->getDefaultAttributes();
    }

    public function getSettings($settings = array())
    {
        $settings = array(
            'attributes' => array(
                'dimensions' => $this->getParam('imgmanager_ext.attributes_dimensions', 1),
                'align' => $this->getParam('imgmanager_ext.attributes_align', 1),
                'margin' => $this->getParam('imgmanager_ext.attributes_margin', 1),
                'border' => $this->getParam('imgmanager_ext.attributes_border', 1),
            ),
            'always_include_dimensions' => (bool) $this->getParam('imgmanager_ext.always_include_dimensions', 1)
        );

        return parent::getSettings($settings);
    }

    protected function getFileBrowserConfig($config = array())
    {
        $config = $this->getThumbnailOptions();
        return parent::getFileBrowserConfig($config);
    }
}
