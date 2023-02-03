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
define('_WF_EXT', 1);

// Load class dependencies

class WFIframePlugin extends WFEditorPlugin
{
    public function display()
    {
        $this->set('colorpicker', true);

        $language = JFactory::getLanguage();
        $language->load('WF_pro', JPATH_SITE);

        parent::display();

        $document = WFDocument::getInstance();

        // create new tabs instance
        $tabs = WFTabs::getInstance(array(
            'base_path' => WF_EDITOR_PLUGIN,
        ));

        $document->addScript(array('iframe'), 'plugins');
        $document->addStyleSheet(array('iframe'), 'plugins');

        $document->addScriptDeclaration('IframeDialog.settings=' . json_encode($this->getSettings()) . ';');

        // Add tabs
        $tabs->addTab('file');
        $tabs->addTab('advanced', $this->getParam('tabs_advanced', 1));

        $this->aggregators = array();

        // Load video aggregators (Youtube, Vimeo etc)
        $video = new WFAggregatorExtension(array('format' => 'video', 'embed' => 'false'));

        if ($video) {
            $this->aggregators[] = $video;
            $video->display();
        }

        $maps = new WFAggregatorExtension(array('format' => 'maps'));

        if ($maps) {
            $this->aggregators[] = $maps;
            $maps->display();
        }

        if (!empty($this->aggregators)) {
            $tabs->addTab('options', $this->getParam('tabs_options', 1), array('plugin' => $this));
        }
    }

    public function getAggregatorOptions()
    {
        $names = array();

        foreach ($this->aggregators as $aggregator) {
            foreach ($aggregator->getAggregators() as $item) {
                if ($item->getName() === 'video' || $item->getName() === 'audio') {
                    continue;
                }
                
                $names[] = JText::_($item->getTitle());
            }
        }

        return $names;
    }

    public function getAggregatorTemplate()
    {
        $tpl = '';

        foreach ($this->aggregators as $aggregator) {
            foreach ($aggregator->getAggregators() as $item) {
                if ($item->getName() === 'video' || $item->getName() === 'audio') {
                    continue;
                }
                
                $data = $aggregator->loadTemplate($item->getName());

                if (!empty($data)) {
                    $tpl .= '<div class="aggregator_option ' . $item->getName() . '" id="' . $item->getName() . '_options"><h4>' . JText::_($item->getTitle()) . '</h4>';
                    $tpl .= $data;
                    $tpl .= '</div>';
                }
            }
        }

        return $tpl;
    }

    public function getSettings($settings = array())
    {
        $profile = $this->getProfile();

        $settings = array(
            'defaults' => $this->getDefaults(),
            'file_browser' => $this->getParam('file_browser', 1) && in_array('browser', explode(',', $profile->plugins)),
        );

        return parent::getSettings($settings);
    }
}
