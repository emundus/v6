<?php

/**
 * Class jutranslation
 * Contains all feature for translation management
 */
class Jutranslation
{
    /**
     * Extension name
     *
     * @var string
     */
    private static $extension = 'com_dropfiles';

    /**
     * Extension slug
     *
     * @var string
     */
    private static $extension_slug = 'dropfiles';

    /**
     * Type
     *
     * @var string
     */
    protected $type = 'jutranslation';

    /**
     * Return a jform input element
     *
     * @param boolean $inline Inline
     *
     * @return string
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public static function getInput($inline = false)
    {
        $html = '';
        //Declare all js and css to include
        $jsInline = array(
            'jutranslation_token="' . JFactory::getSession()->getFormToken() . '";',
            'jutranslation_base_url="' . JUri::base() . 'index.php?option=' . self::$extension . '&";'
        );
        $jsFile = array(
            JUri::base() . 'components/' . self::$extension . '/assets/js/jutranslation.js'
        );
        $cssFile = array(
            JUri::base() . 'components/' . self::$extension . '/assets/css/jutranslation.css'
        );
        // Depending on how the scripts has to be included
        // Add it to the head or directly in the output code
        if ($inline) {
            //For installer script we don't have access to head
            if (count($jsInline)) {
                $html .= '<script type="text/javascript">';
                foreach ($jsInline as $item) {
                    $html .= $item . PHP_EOL;
                }
                $html .= '</script>';
            }
            if (count($jsFile)) {
                foreach ($jsFile as $item) {
                    $html .= '<script type="text/javascript" src="' . $item . '"></script>';
                }
            }
            if (count($cssFile)) {
                foreach ($cssFile as $item) {
                    $html .= '<link rel="stylesheet" type="text/css" href="' . $item . '" />';
                }
            }
        } else {
            foreach ($jsInline as $item) {
                JFactory::getApplication()->getDocument()->addScriptDeclaration($item);
            }
            foreach ($jsFile as $item) {
                JFactory::getApplication()->getDocument()->addScript($item);
            }
            foreach ($cssFile as $item) {
                JFactory::getApplication()->getDocument()->addStyleSheet($item);
            }
        }

        //Get all installed languages
        $languages = array();
        foreach (JLanguageHelper::getInstalledLanguages(0) as $lang) {
            $langObject = new stdClass();
            $langObject->installed = false;
            $langObject->extension_version = '';
            $langObject->revision = '1';
            $langObject->languageCode = $lang->element;
            $langObject->modified = '0';
            $languages[$lang->element] = $langObject;
        }
        foreach (JLanguageHelper::getLanguages() as $lang) {
            $langObject = new stdClass();
            $langObject->installed = false;
            $langObject->extension_version = '';
            $langObject->revision = '1';
            $langObject->languageCode = $lang->lang_code;
            $langObject->modified = '0';
            $languages[$lang->lang_code] = $langObject;
        }

        //Check if language is installed
        foreach ($languages as &$language) {
            $file = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR;
            $file .= self::$extension . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR;
            $file .= $language->languageCode . DIRECTORY_SEPARATOR . $language->languageCode;
            $file .= '.' . self::$extension . '.ini';
            if (file_exists($file)) {
                $language->installed = true;

                //Extract informations from language file
                $informations = self::extractInformations(file_get_contents($file));

                //Assign it to the language
                if (isset($informations['version'])) {
                    $language->extension_version = $informations['version'];
                }
                if (isset($informations['revision'])) {
                    $language->revision = $informations['revision'];
                }
                if (isset($informations['modified'])) {
                    $language->modified = $informations['modified'];
                } else {
                    //The header has not been found, so the language file has not been add by jutranslation
                    $language->modified = '1';
                }
            }

            //Check for language override
            $language->overrided = 0;
            $file = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR;
            $file .= self::$extension . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR;
            $file .= $language->languageCode . DIRECTORY_SEPARATOR . $language->languageCode;
            $file .= '.' . self::$extension . '.override.ini';
            if (file_exists($file)) {
                //Read the file to extract informations about translation
                $strings = self::extractLanguageStrings(file_get_contents($file));

                $language->overrided = count($strings);
            }
        }
        unset($language);

        //Get extension version
        $dbo = JFactory::getDbo();
        $query = 'SELECT extension_id FROM #__extensions';
        $query .= ' WHERE element=' . $dbo->quote(self::$extension) . ' AND type="component"';
        if (!$dbo->setQuery($query)) {
            return false;
        }
        $component = $dbo->loadResult();
        if (!$component) {
            return false;
        }
        $table = JTable::getInstance('extension');
        // Load the previous Data
        if (!$table->load($component, false)) {
            return false;
        }
        $manifest_cache = json_decode($table->manifest_cache);
        $extension_version = $manifest_cache->version;
        $hrefjoom = 'https://www.joomunited.com/documentation/ju-translation-translate-wordpress-and-joomla-extensions';
        $html .= '<p>You can refer to the ';
        $html .= '<a href="' . $hrefjoom . '" target="_blank">documentation page on Joomunited';
        $html .= '</a> for more informations about extension translation</p>';
        $html .= '<p>Current extension version is ' . $extension_version . '</p>';

        $jsLanguages = array();
        $html .= '<table id="jutranslations-languages" class="table table-striped" >
                    <thead >
                        <tr>
                            <th>Language</th>
                            <th>Current version</th>
                            <th>Latest available version</th>
                        </tr>
                    </thead>
                    <tbody>';
        $versions = array();
        $revisions = array();
        foreach ($languages as $language) {
            $html .= '<tr data-lang="' . $language->languageCode . '" data-installed="' . $language->installed;
            $html .= '" data-version="' . $language->extension_version . '">';
            $html .= '<td>' . $language->languageCode . '</td>';
            $html .= '<td class="current_version">' .
                ($language->installed ? ($language->extension_version ? ($language->revision ?
                    $language->extension_version . ' rev'
                    . $language->revision : $language->extension_version) : 'Unknown') : 'Not installed') . '</td>';
            $html .= '<td><div class="original_content">';
            $html .= '<span class="latest_version"><img src="components/com_dropfiles/assets/images/radio.svg" ';
            $html .= ' alt="loading"/></span><br/>';

            $html .= '<a class="jutranslation-override" href="#" data-language="' . $language->languageCode . '">';
            $html .= 'Override (<span class="jutranslation-override-count">' . $language->overrided . '</span>)</a> ';
            if ($language->languageCode !== 'en-GB') {
                //Reference en-GB file can't be modified
                $html .= '<a class="jutranslation-edition" href="#" data-language="' . $language->languageCode . '">';
                $html .= ' Edit original file</a>';
            }

            //No sharing for en-GB
            if ($language->languageCode !== 'en-GB') {
                $html .= ' <a class="jutranslation-share" style="'
                    . (($language->modified === '0') ? 'display:none' : '')
                    . '" href="#" data-language="' . $language->languageCode . '">Share with Joomunited</a>';
            }
            $html .= '</div><div class="temporary_content"></div></td>';
            $html .= '</tr>';

            $jsLanguages[] = '"' . $language->languageCode . '"';

            $versions[$language->languageCode] = $language->extension_version;
            $revisions[$language->languageCode] = $language->revision;
        }
        $html .= '</tbody></table>';

        $script = 'julanguages = {"extension" : "' . self::$extension_slug . '", "extension_version" : "';
        $script .= $extension_version . '", "languages" : [' . implode(',', $jsLanguages);
        $script .= '], "versions" : ' . json_encode($versions) . ',"revisions" : ' . json_encode($revisions) . '};';
        if ($inline) {
            $html .= '<script type="text/javascript">' . $script . '</script>';
        } else {
            JFactory::getDocument()->addScriptDeclaration($script);
        }

        return $html;
    }

    /**
     * Parse a text content to extract all strings
     *
     * @param string $content Content
     *
     * @return array of strings
     * @since  version
     */
    public static function extractLanguageStrings($content)
    {
        // -------- remove the utf-8 BOM ----
        $content = str_replace("\xEF\xBB\xBF", '', $content);

        //Array of translated strings
        $strings = array();

        //Loop over each line of the content
        $separator = "\r\n";
        $line = strtok($content, $separator);
        while ($line) {
            //Remove comments lines
            if (strpos($line, ';')) {
                $line = strtok($separator);
                continue;
            }

            //Make sure to only keep good content and remove empty strings
            $matches = array();
            preg_match('/^([a-zA-Z_]+) *= *\"(.+)\" *$/', $line, $matches);

            //Add the string to valid list
            if (count($matches) === 3) {
                $strings[$matches[1]] = str_replace('"QQ"', '"', $matches[2]);
            }

            $line = strtok($separator);
        }
        return $strings;
    }

    /**
     * Parse a text content to extract all informations strings
     *
     * @param string $content Content
     *
     * @return array
     * @since  version
     */
    public static function extractInformations($content)
    {
        //Array of translated strings
        $informations = array();

        if (!$content) {
            return $informations;
        }

        //Loop over each line of the content
        $separator = "\r\n";
        $line = strtok($content, $separator);
        while ($line) {
            //If file is not a comment there is no more information to extract
            if (strpos($line, ';') !== 0) {
                break;
            }

            $matches = array();
            preg_match('/^;([a-zA-Z]+)=\"([0-9\.]+)\"$/', $line, $matches);
            //Add the information to array
            if (count($matches) === 3) {
                $informations[$matches[1]] = $matches[2];
            }

            //Next line
            $line = strtok($separator);
        }

        return $informations;
    }

    /**
     * Get component version
     *
     * @return null|string
     * @since  version
     */
    public static function getComponentVersion()
    {
        $manifest = self::getManifest(self::$extension);
        if (property_exists($manifest, 'version')) {
            return $manifest->version;
        }

        return null;
    }

    /**
     * Method to get an object containing the manifest values
     *
     * @param string $option Option
     *
     * @return object
     * @since  version
     */
    protected static function getManifest($option)
    {
        $dbo = JFactory::getDbo();
        $query = 'SELECT extension_id FROM #__extensions';
        $query .= ' WHERE element=' . $dbo->quote($option) . ' AND type="component"';
        if (!$dbo->setQuery($query)) {
            return false;
        }
        if (!$dbo->execute()) {
            return false;
        }
        $component = $dbo->loadResult();
        if (!$component) {
            return false;
        }
        $table = JTable::getInstance('extension');
        // Load the previous Data
        if (!$table->load($component, false)) {
            return false;
        }
        return json_decode($table->manifest_cache);
    }

    /**
     * Get config token
     *
     * @return string
     * @since  version
     */
    public static function juGetToken()
    {
        $token = null;
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('value');
        $query->from('#__joomunited_config');
        $query->where('name="ju_user_token"');
        $db->setQuery($query);
        $res = $db->loadObject();

        if (!empty($res) && !empty($res->value)) {
            $token = str_replace('token=', '', $res->value);
        }

        return $token;
    }
}
