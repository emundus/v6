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

if (!defined('_WF_EXT')) {
    define('_WF_EXT', 1);
}

class WFMediaManagerPlugin extends WFMediaManager
{
    /*
     * @var string
     */

    public $_filetypes = 'windowsmedia=avi,wmv,wm,asf,asx,wmx,wvx;quicktime=mov,qt,mpg,mpeg;flash=swf;shockwave=dcr;real=rm,ra,ram;divx=divx;video=mp4,ogv,ogg,webm,flv,f4v;audio=mp3,ogg,wav,m4a;silverlight=xap';

    protected $name = 'mediamanager';

    public function __construct($config = array())
    {
        parent::__construct($config);

        $request = WFRequest::getInstance();
        $request->setRequest(array($this, 'getEmbedData'));
    }

    /**
     * Display the plugin.
     */
    public function display()
    {
        parent::display();

        $document = WFDocument::getInstance();

        $document->addScript(array('mediamanager'), 'plugins');
        $document->addStyleSheet(array('mediamanager'), 'plugins');

        $document->addScriptDeclaration('MediaManagerDialog.settings=' . json_encode($this->getSettings()) . ';');

        $tabs = WFTabs::getInstance(array('base_path' => WF_EDITOR_PLUGIN));

        // Add tabs
        $tabs->addTab('file', 1, array('plugin' => $this));
        $tabs->addTab('media', $this->getParam('tabs_media', 1), array('plugin' => $this));
        $tabs->addTab('advanced', $this->getParam('tabs_advanced', 1));

        // Load Popups instance
        $popups = WFPopupsExtension::getInstance(array(
            // map src value to popup link href
            'map' => array('href' => 'src'),
            // show popup text field
            'text' => true,
            // default values
            'default' => $this->getParam('mediamanager.popups.default', ''),
        ));

        $popups->display();

        // Load video aggregators (Youtube, Vimeo etc)
        $this->loadAggregators();
    }

    public function getDefaultAttributes()
    {
        $attribs = parent::getDefaultAttributes();

        $video = $this->getDefaults('aggregator.video', array(
            'manifest' => WF_EDITOR_EXTENSIONS . '/aggregator/video.xml',
            'group' => 'aggregator.video',
        ));

        $audio = $this->getDefaults('aggregator.audio', array(
            'manifest' => WF_EDITOR_EXTENSIONS . '/aggregator/audio.xml',
            'group' => 'aggregator.audio',
        ));

        if (!empty($video)) {
            $video = array_filter($video, function (&$value) {
                if (is_numeric($value)) {
                    return (int) $value === 1;
                }

                return true;
            });

            $attribs['video'] = $video;
        }

        if (!empty($audio)) {
            $audio = array_filter($video, function (&$value) {
                if (is_numeric($value)) {
                    return (int) $value === 1;
                }

                return true;
            });

            $attribs['audio'] = $audio;
        }

        return $attribs;
    }

    public function onUpload($file, $relative = '')
    {        
        parent::onUpload($file, $relative);

        $app = JFactory::getApplication();

        if ($app->input->getInt('inline', 0) == 1) {

            // get the list of filetypes supported
            $filetypes = array_values($this->getFileTypes());

            // only allow a limited set that are support by the <video> and <audio> tags
            $filetypes = array_intersect($filetypes, array('mp3', 'oga', 'm4a', 'mp4', 'm4v', 'ogg', 'webm', 'ogv'));

            // check for support
            if (preg_match('#\.(' . implode('|', $filetypes) . ')$#', $file)) {
                $result = array(
                    'file' => $relative,
                    'name' => WFUtility::mb_basename($file),
                );

                $result = array_merge($result, array('attributes' => $this->getDefaultAttributes()));

                return $result;
            }
        }

        return array();
    }

    /**
     * Get a list of media extensions.
     *
     * @param bool    Map the extensions to media type
     *
     * @return string Extension list or type map
     */
    protected function getMediaTypes($map = false)
    {
        $extensions = $this->getParam('extensions', $this->get('_filetypes'));

        if ($map) {
            return $extensions;
        } else {
            $this->listFileTypes($extensions);
        }
    }

    protected function setMediaOption($name, $value)
    {
        // prevent duplicates
        if ($name === 'video' || $name === 'audio') {
            return;
        }

        $options = $this->get('_media_options');

        $options[$name] = $value;

        $this->set('_media_options', $options);
    }

    public function getMediaOptions()
    {
        $list = $this->getParam('extensions', $this->get('_filetypes'));

        $options = '';

        if ($list) {
            foreach (explode(';', $list) as $type) {
                $kv = explode('=', $type);

                if (substr($kv[0], 0, 1) === '-') {
                    continue;
                }

                $options .= '<option value="' . $kv[0] . '">' . JText::_('WF_MEDIAMANAGER_' . strtoupper($kv[0]) . '_TITLE') . '</option>' . "\n";
            }

            foreach ($this->get('_media_options') as $k => $v) {
                $options .= '<option value="' . $k . '">' . JText::_($v, ucfirst($k)) . '</option>' . "\n";
            }

            $options .= '<option value="iframe">' . JText::_('WF_MEDIAMANAGER_IFRAME_TITLE') . '</option>' . "\n";
        }

        return $options;
    }

    protected function getViewable()
    {
        return $this->get('filetypes');
    }

    protected function loadAggregators()
    {
        $extension = WFAggregatorExtension::getInstance(array('format' => 'video'));
        $extension->display();

        foreach ($extension->getAggregators() as $aggregator) {
            // set the Media Type option
            $this->setMediaOption($aggregator->getName(), $aggregator->getTitle());
        }
    }

    public function getAggregatorTemplate()
    {
        $tpl = '';

        $extension = WFAggregatorExtension::getInstance();

        foreach ($extension->getAggregators() as $aggregator) {

            if ($aggregator->getName() === 'audio' || $aggregator->getName() === 'video') {
                continue;
            }

            $tpl .= '<div class="media_option ' . $aggregator->getName() . '" id="' . $aggregator->getName() . '_options" style="display:none;"><h4>' . JText::_($aggregator->getTitle()) . '</h4>';
            $tpl .= $extension->loadTemplate($aggregator->getName());
            $tpl .= '</div>';
        }

        return $tpl;
    }

    public function getSettings($settings = array())
    {
        $settings = array(
            // Plugin parameters
            'media_types' => $this->get('filetypes', $this->get('_filetypes')),
            'defaults' => $this->getDefaults(),
        );

        return parent::getSettings($settings);
    }

    public function getEmbedData($provider, $url, $type = '')
    {
        $providers = array(
            'youtube' => 'https://www.youtube.com/oembed?url=',
            'vimeo' => 'https://vimeo.com/api/oembed.json?url=',
            'dailymotion' => 'http://www.dailymotion.com/services/oembed?url=',
            'scribd' => 'https://www.scribd.com/services/oembed/?format=json&url=',
            /*'facebook' => array(
                'posts' => 'https://www.facebook.com/plugins/post/oembed.json?url=',
                'videos' => 'https://www.facebook.com/plugins/video/oembed.json?url=',
            ),
            'instagram' => 'https://api.instagram.com/oembed?url=',*/
            'reddit' => 'https://www.reddit.com/oembed?url=',
            'slideshare' => 'https://www.slideshare.net/api/oembed/2?format=json&url=',
            'soundcloud' => 'https://soundcloud.com/oembed?format=json&url=',
            'spotify' => 'https://embed.spotify.com/oembed?url=',
            'ted' => 'http://www.ted.com/talks/oembed.json?url=',
            'twitch' => 'https://api.twitch.tv/v4/oembed?url=',
            'twitter' => 'https://publish.twitter.com/oembed?url=',
            'audio'   => '',
            'video'   => ''
        );

        $params = (object) $this->getParam('mediamanager', array());

        // decode url
        $url = rawurldecode($url);

        // clean the url
        $url = filter_var($url, FILTER_SANITIZE_URL);

        // custom or not supported
        $custom = JFactory::getApplication()->triggerEvent('onWfGetCustomEmbedData', array(&$url));

        if (!empty($custom)) {
            foreach ($custom as $item) {
                if (isset($item[$provider]) && is_array($item[$provider])) {
                    return $item[$provider];
                }
            }
        }

        if (!array_key_exists($provider, $providers)) {
            return false;
        }

        $data   = array();
        $source = $providers[$provider];

        if ($source) {
            if (is_array($source) && $type) {
                $source = (array_key_exists($type, $source)) ? $source[$type] : $source[0];
            }
    
            $source .= rawurlencode($url);

            $data = $this->getDataFromOEmbed($source);

            $data = json_decode($data, true);

            if (empty($data)) {
                $data['src'] = $url;
            }
        }

        $extension = WFAggregatorExtension::getInstance(array('format' => 'video'));

        foreach ($extension->getAggregators() as $aggregator) {
            if ($aggregator->getName() === $provider) {
                $data = $aggregator->getEmbedData($data, $url);
            }
        }

        return $data;
    }

    private function getData($url)
    {
        $http = JHttpFactory::getHttp();

        try {
            $response = $http->get($url);
        } catch (\RuntimeException $e) {
            $response = null;
        }

        if ($response === null || $response->code !== 200) {
            return array('error' => JText::_('Unable to get OEmbed Data - Invalid response from ' . $url));
        }

        return $response->body;
    }

    private function getDataFromOEmbed($url)
    {                       
        /*$options = array(
            'http' => array(
                'method' => "GET",
                'header' => "Accept-language: en\r\n" .
                "Cookie: foo=bar\r\n" .
                "User-Agent: Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.2.14) Gecko/20110105 Firefox/3.6.14\r\n", // i.e. An iPad
            ),
        );

        $context = stream_context_create($options);

        return @file_get_contents($url, false, $context);*/

        return $this->getData($url);
    }
}
