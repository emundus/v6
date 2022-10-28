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
 * @since     1.6
 */


defined('_JEXEC') || die;

/**
 * DropfilesDocumentsHelper class
 */
class DropfilesDocumentsHelper
{
    /**
     * File path
     *
     * @var string
     */
    private $filePath;

    /**
     * File extension
     *
     * @var string
     */
    private $fileExt;

    /**
     * DropfilesDocumentsHelper constructor.
     *
     * @param array $file File information
     *
     * @return void
     * @since  version
     */
    public function __construct($file)
    {
        $this->filePath = JPATH_ROOT . '/media/com_dropfiles/' . $file->catid . '/' . $file->file;
        $this->fileExt = $file->ext;
    }

    /**
     * Get document content
     *
     * @return string
     * @since  version
     */
    public function getDocumentContent()
    {
        if (!file_exists($this->filePath)) {
            return '';
        }

        $encodes = 'UTF-8, ASCII,';
        $encodes .= 'ISO-8859-1, ISO-8859-2, ISO-8859-3, ISO-8859-4, ISO-8859-5,';
        $encodes .= 'ISO-8859-6, ISO-8859-7, ISO-8859-8, ISO-8859-9, ISO-8859-10,';
        $encodes .= 'ISO-8859-13, ISO-8859-14, ISO-8859-15, ISO-8859-16,';
        $encodes .= 'Windows-1251, Windows-1252, Windows-1254';

        switch ($this->fileExt) {
            case 'doc':
                $content = $this->getDocBody();
                break;
            case 'docx':
                $content = $this->getDocxBody();
                break;
            case 'xls':
                $content = $this->getXlsBody();
                break;
            case 'xlsx':
                $content = $this->getXlsxBody();
                break;
            case 'pdf':
                $content = $this->getPdfBody();
                break;
            case 'ppt':
                $content = $this->getPowerPointBody();
                break;
            case 'pptx':
                // $content = $this->getPowerPointBody();
                $content = '';
                break;
            case 'rtf':
                $content = $this->getRtfBody();
                break;
            case 'txt':
                $content = $this->getTxtBody();
                break;
            default:
                $content = '';
        }

        $encode = mb_detect_encoding($content, $encodes);
        switch ($encode) {
            case 'ISO-8859-1':
                return utf8_encode($content);
            default:
                return mb_convert_encoding($content, 'UTF-8', $encode);
        }
    }

    /**
     * Get doc content
     * Not working good with utf8
     *
     * @return string
     * @since  version
     */
    private function getDocBody()
    {
        $doccontent = '';
        // TODO: Decode file for content, this work fine with all latin char BUT NOT working well with utf8.
        // Need sonething else better!!
        $fh = fopen($this->filePath, 'r');
        if ($fh !== false) {
            $headers = fread($fh, 0xA00);
            $n1 = (ord($headers[0x21C]) - 1);

            // 1 = (ord(n)*1) ; Document has from 0 to 255 characters
            $n2 = ((ord($headers[0x21D]) - 8) * 256);

            // 1 = ((ord(n)-8)*256) ; Document has from 256 to 63743 characters
            $n3 = ((ord($headers[0x21E]) * 256) * 256);

            // 1 = ((ord(n)*256)*256) ; Document has from 63744 to 16775423 characters
            $n4 = (((ord($headers[0x21F]) * 256) * 256) * 256);

            // 1 = (((ord(n)*256)*256)*256) ; Document has from 16775424 to 4294965504 characters
            $textLength = ($n1 + $n2 + $n3 + $n4);

            // Total length of text in the document
            $extractedPlaintext = fread($fh, $textLength);
            $doccontent = preg_replace_callback(
                "/(\\\\x..)/isU",
                function ($m) {
                    return chr(hexdec(substr($m[0], 2)));
                },
                $extractedPlaintext
            );
        } else {
            $doccontent = '';
        }

        // Try with Filetotext
        if ($doccontent === '') {
            if (!class_exists('Filetotext')) {
                $pathFiletotext = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_dropfiles';
                $pathFiletotext .= DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'class.filetotext.php';
                require_once $pathFiletotext;
            }

            $handle = new Filetotext($this->filePath);
            $doccontent = $handle->convertToText();
        }

        return $doccontent;
    }

    /**
     * Get docx content
     *
     * @return string
     * @since  version
     */
    private function getDocxBody()
    {
        $content = $this->getContentFromArchive('word/document.xml');

        return $content;
    }

    /**
     * Get xls content for Office 2003 format
     * Not working well
     *
     * @return string
     * @since  version
     */
    private function getXlsBody()
    {
        if (!class_exists('Spreadsheet_Excel_Reader')) {
            $pathExceltotext = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_dropfiles';
            $pathExceltotext .= DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'class.exceltotext.php';
            require_once $pathExceltotext;
        }

        $handle     = new Spreadsheet_Excel_Reader($this->filePath);
        $rowNumbers = false;
        $colLetters = true;
        $sheet      = 0;
        $tableClass = 'excel';
        $content    = $handle->dump($rowNumbers, $colLetters, $sheet, $tableClass);
        $content    = mb_convert_encoding($content, 'UTF-8', null);
        $content    = html_entity_decode($content, ENT_COMPAT, 'UTF-8');

        return $this->cleanXmlContent($content);
    }

    /**
     * Get xlsx content
     *
     * @return string
     * @since  version
     */
    private function getXlsxBody()
    {
        $content = $this->getContentFromArchive('xl/sharedStrings.xml');

        return $content;
    }

    /**
     * Get pdf content
     *
     * @return string
     * @since  version
     */
    private function getPdfBody()
    {
        $pdfContent = '';
        $helpersPath = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_dropfiles';
        $helpersPath .= DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;

        // Pdfparser run only php 5.3+
        if (version_compare(PHP_VERSION, '5.3', '>=')) {
            require_once $helpersPath . 'pdfParserLoader.php';

            // A wrapper class was conditionally included if we're running PHP 5.3+ so let's try that
            if (class_exists('DropfilesPdfParser')) {
                include_once $helpersPath . 'pdfparser/autoload.php';

                // Try PdfParser first
                $parser = new DropfilesPdfParser;
                $parser = $parser->init();

                try {
                    $pdf = $parser->parseFile($this->filePath);
                    $pdfContent = $pdf->getText();
                } catch (Exception $e) {
                    $pdfContent = '';
                }
            }
        }

        // PDF2Text
        if ($pdfContent === '') {
            if (!class_exists('PDF2Text')) {
                include_once $helpersPath . 'class.pdf2text.php';
            }

            $pdfParser = new PDF2Text;
            $pdfParser->setFilename($this->filePath);
            $pdfParser->decodePDF();
            $pdfContent = $pdfParser->output();
        }

        $pdfContent = preg_replace('/<[^>]*>/', ' \\0 ', $pdfContent);
        $pdfContent = preg_replace('/&nbsp;/', ' ', $pdfContent);
        $pdfContent = str_replace(array('<br />', '<br/>', '<br>'), ' ', $pdfContent);

        $punctuation = array('(', ')', '·', "'", '´', '’', '‘', '”', '“', '„', '—', '–', '×', '…',
            '€', '\n', '.', ',', '/', '\\', '|', '[', ']', '{', '}', '•', '`', '  ', '\s\s');
        $pdfContent = str_replace($punctuation, ' ', $pdfContent);
        $pdfContent = trim(preg_replace('/\t+/', '', $pdfContent));
        $pdfContent = preg_replace('/[[:blank:]]+/', ' ', $pdfContent);
        $pdfContent = mb_convert_encoding($pdfContent, 'UTF-8', null);

        return $pdfContent;
    }

    /**
     * Get ppt content
     *
     * @return string
     * @since  version
     */
    private function getPowerPointBody()
    {
        $output = '';

        $zip = new ZipArchive;

        if (true === $zip->open($this->filePath)) {
            // Loop through each slide archive
            $slideNum = 1;

            while (false !== ($index = $zip->locateName('ppt/slides/slide' . absint($slideNum) . '.xml'))) {
                $data = $zip->getFromIndex($index);
                $output .= ' ' . $this->get_xml_content($data);
                $slideNum++;
            }

            $zip->close();
        }

        return sanitizeTextField($output);
    }

    /**
     * Check if rtf is Plain text
     *
     * @param array $s String array
     *
     * @return boolean
     * @since  version
     */
    private function rtfIsPlainText($s)
    {
        $arrfailAt = array('*', 'fonttbl', 'colortbl', 'datastore', 'themedata');
        $total     = count($arrfailAt);
        for ($i = 0; $i < $total; $i++) {
            if (!empty($s[$arrfailAt[$i]])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get rtf text content
     *
     * @return string
     * @since  version
     */
    private function getRtfBody()
    {
        // phpcs:disable Generic.PHP.NoSilencedErrors.Discouraged -- I don't want any error display here
        // Read the data from the input file.
        $text = file_get_contents($this->filePath);

        $textLen = strLen($text);

        if (!$textLen) {
            return '';
        }

        // Create empty stack array.
        $document = '';
        $stack    = array();
        $j        = - 1;

        // Read the data character-by- character…
        for ($i = 0, $len = $textLen; $i < $len; $i++) {
            $c = $text[$i];

            // Depending on current character select the further actions.
            switch ($c) {
                // The most important key word backslash
                case '\\':
                    // Read next character
                    $nc = $text[$i + 1];

                    // If it is another backslash or nonbreaking space or hyphen,
                    // then the character is plain text and add it to the output stream.
                    if ($nc === '\\' && $this->rtfIsPlainText($stack[$j])) {
                        $document .= '\\';
                    } elseif ($nc === '~' && $this->rtfIsPlainText($stack[$j])) {
                        $document .= ' ';
                    } elseif ($nc === '_' && $this->rtfIsPlainText($stack[$j])) {
                        $document .= '-';
                    } elseif ($nc === '*') {
                        // If it is an asterisk mark, add it to the stack.
                        $stack[$j]['*'] = true;
                    } elseif ($nc === "'") {
                        // If it is a single quote, read next two characters that are the hexadecimal notation
                        // of a character we should add to the output stream.
                        $hex = substr($text, $i + 2, 2);

                        if ($this->rtfIsPlainText($stack[$j])) {
                            $document .= html_entity_decode('&#' . hexdec($hex) . ';', ENT_COMPAT, 'UTF-8');
                        }

                        // Shift the pointer.
                        $i += 2;

                        // Since, we’ve found the alphabetic character, the next characters are control word
                        // and, possibly, some digit parameter.
                    } elseif ($nc >= 'a' && $nc <= 'z' || $nc >= 'A' && $nc <= 'Z') {
                        $word = '';
                        $param = null;

                        // Start reading characters after the backslash.
                        $textLen = strlen($text);
                        for ($k = $i + 1, $m = 0; $k < $textLen; $k++, $m++) {
                            $nc = $text[$k];

                            /*
                             * If the current character is a letter and there were no digits before it,
                             * then we’re still reading the control word. If there were digits, we should stop
                             * since we reach the end of the control word.
                            */

                            if ($nc >= 'a' && $nc <= 'z' || $nc >= 'A' && $nc <= 'Z') {
                                if (empty($param)) {
                                    $word .= $nc;
                                } else {
                                    break;
                                }
                                // If it is a digit, store the parameter.
                            } elseif ($nc >= '0' && $nc <= '9') {
                                $param .= $nc;
                            } elseif ($nc === '-') {
                                // Since minus sign may occur only before a digit parameter, check whether
                                // $param is empty. Otherwise, we reach the end of the control word.
                                if (empty($param)) {
                                    $param .= $nc;
                                } else {
                                    break;
                                }
                            } else {
                                break;
                            }
                        }

                        // Shift the pointer on the number of read characters.
                        $i += $m - 1;

                        // Start analyzing what we’ve read. We are interested mostly in control words.
                        $toText = '';

                        switch (strtolower($word)) {
                            /*
                             * If the control word is "u", then its parameter is the decimal notation of the
                             * Unicode character that should be added to the output stream.
                             * We need to check whether the stack contains \ucN control word. If it does,
                             * we should remove the N characters from the output stream.
                            */
                            case 'u':
                                $toText .= html_entity_decode('&#x' . dechex($param) . ';', ENT_COMPAT, 'UTF-8');
                                $ucDelta = @$stack[$j]['uc'];

                                if ($ucDelta > 0) {
                                    $i += $ucDelta;
                                }
                                break;

                            // Select line feeds, spaces and tabs.
                            case 'par':
                            case 'page':
                            case 'column':
                            case 'line':
                            case 'lbr':
                                $toText .= "\n";
                                break;
                            case 'emspace':
                            case 'enspace':
                            case 'qmspace':
                                $toText .= ' ';
                                break;
                            case 'tab':
                                $toText .= "\t";
                                break;

                            // Add current date and time instead of corresponding labels.
                            case 'chdate':
                                $toText .= date('m.d.Y');
                                break;
                            case 'chdpl':
                                $toText .= date('l, j F Y');
                                break;
                            case 'chdpa':
                                $toText .= date('D, j M Y');
                                break;
                            case 'chtime':
                                $toText .= date('H:i:s');
                                break;

                            // Replace some reserved characters to their html analogs.
                            case 'emdash':
                                $toText .= html_entity_decode('&mdash;', ENT_COMPAT, 'UTF-8');
                                break;
                            case 'endash':
                                $toText .= html_entity_decode('&ndash;', ENT_COMPAT, 'UTF-8');
                                break;
                            case 'bullet':
                                $toText .= html_entity_decode('&#149;', ENT_COMPAT, 'UTF-8');
                                break;
                            case 'lquote':
                                $toText .= html_entity_decode('&lsquo;', ENT_COMPAT, 'UTF-8');
                                break;
                            case 'rquote':
                                $toText .= html_entity_decode('&rsquo;', ENT_COMPAT, 'UTF-8');
                                break;
                            case 'ldblquote':
                                $toText .= html_entity_decode('&laquo;', ENT_COMPAT, 'UTF-8');
                                break;
                            case 'rdblquote':
                                $toText .= html_entity_decode('&raquo;', ENT_COMPAT, 'UTF-8');
                                break;

                            // Add all other to the control words stack. If a control word
                            // does not include parameters, set &param to true.
                            default:
                                $stack[$j][strtolower($word)] = empty($param) ? true : $param;
                                break;
                        }

                        // Add data to the output stream if required.
                        if ($this->rtfIsPlainText($stack[$j])) {
                            $document .= $toText;
                        }
                    }

                    $i++;
                    break;

                // If we read the opening brace {, then new subgroup starts and we add
                // new array stack element and write the data from previous stack element to it.
                case '{':
                    array_push($stack, $stack[$j++]);
                    break;

                // If we read the closing brace }, then we reach the end of subgroup and should remove
                // the last stack element.
                case '}':
                    array_pop($stack);
                    $j--;
                    break;

                // Skip “trash”.
                case '\0':
                case '\r':
                case '\f':
                case '\n':
                    break;

                // Add other data to the output stream if required.
                default:
                    if ($this->rtfIsPlainText($stack[$j])) {
                        $document .= $c;
                    }
                    break;
            }
        }

        // / phpcs:enable
        // Return result.
        return $this->cleanChar($document);
    }

    /**
     * Get txt file content
     *
     * @return string
     * @since  version
     */
    private function getTxtBody()
    {
        $content = file_get_contents($this->filePath);

        if (!$content) {
            return '';
        }

        if ($content === '') {
            try {
                $handle = fopen($this->filePath, 'r');
                $content = fread($handle, filesize($this->filePath));
                fclose($handle);
            } catch (Exception $e) {
                return '';
            }
        }

        return $this->sanitizeTextField($content);
    }

    /**
     * Get content from Archive
     *
     * @param string $xmlfilename Filename need get data
     *
     * @return string
     * @since  version
     */
    private function getContentFromArchive($xmlfilename)
    {
        if (!class_exists('ZipArchive')) {
            return '';
        }

        $output = '';
        $zip = new ZipArchive;

        if ($zip->open($this->filePath) === true) {
            $index = $zip->locatename($xmlfilename);

            if ($index !== false) {
                $data = $zip->getFromIndex($index);
                $output = $this->getXmlContent($data);
            }

            $zip->close();
        }

        return $this->sanitizeTextField($output);
    }

    /**
     * Get xml content
     *
     * @param string $data Xml string
     *
     * @return mixed|null|string|string[]
     * @since  version
     */
    private function getXmlContent($data = '')
    {
        if (!class_exists('DOMDocument')) {
            return '';
        }

        $xml = new DOMDocument;
        $xml->loadXML($data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);

        return $this->cleanXmlContent($xml->saveXML());
    }

    /**
     * Clean xml content
     *
     * @param string $content Content
     *
     * @return mixed|null|string|string[]
     * @since  version
     */
    private function cleanXmlContent($content = '')
    {
        if ($content === '') {
            return '';
        }

        if (function_exists('mb_convert_encoding')) {
            $content = mb_convert_encoding($content, 'UTF-8', null);
        }

        $acceptAtt = array(
            'a'     => array('title'),
            'img'   => array('alt', 'src', 'longdesc', 'title'),
            'input' => array('placeholder', 'value'),
        );

        $content = trim($content);
        $contenAtt = array();

        if (!empty($acceptAtt) && !empty($content) && is_array($acceptAtt)
            && class_exists('DOMDocument') && function_exists('libxml_use_internal_errors')) {
            $dom = new DOMDocument;
            libxml_use_internal_errors(true);
            $dom->loadHTML($content);

            foreach ($acceptAtt as $tag => $attributes) {
                $nodeList = $dom->getElementsByTagName($tag);

                for ($i = 0; $i < $nodeList->length; $i++) {
                    $node = $nodeList->item($i);

                    if ($node->hasAttributes()) {
                        foreach ($node->attributes as $attr) {
                            if (isset($attr->name) && in_array($attr->name, $attributes, true)) {
                                $contenAtt[] = $this->sanitizeTextField($attr->nodeValue);
                            }
                        }
                    }
                }
            }
        }

        if (!empty($contenAtt)) {
            $content .= ' ' . implode(' ', $contenAtt);
        }

        return $this->cleanChar($content);
    }

    /**
     * Clean char
     *
     * @param string $content Content
     *
     * @return mixed|null|string|string[]
     * @since  version
     */
    private function cleanChar($content = '')
    {
        if ($content === '') {
            return '';
        }

        $content = preg_replace('/<[^>]*>/', ' \\0 ', $content);
        $content = preg_replace('/&nbsp;/', ' ', $content);
        $content = str_replace(array('<br />', '<br/>', '<br>'), ' ', $content);
        $content = strip_tags($content);
        $content = stripslashes($content);

        $punct = array('(', ')', '·', "'", '´', '’', '‘', '”', '“', '„', '—', '–', '×', '…', '€', '\n', '.', ',',
            '/', '\\', '|', '[', ']', '{', '}', '•', '`');
        $content = str_replace($punct, '', $content);
        $content = preg_replace('/[[:punct:]]/uiU', ' ', $content);

        // Remove more space
        $content = preg_replace('/[[:space:]]/uiU', ' ', $content);
        $content = preg_replace('/\\n|\\R/uiU', ' ', $content);
        $content = $this->sanitizeTextField($content);
        $content = trim($content);

        return $content;
    }

    /**
     * Internal helper function to sanitize a string from user input or from the db
     *
     * @param string $str String to sanitize.
     *
     * @access private
     *
     * @return string Sanitized string.
     * @since  version
     */
    private function sanitizeTextField($str)
    {
        $filtered = $str;

        if (strpos($filtered, '<') !== false) {
            $filtered = $this->preKsesLessThan($filtered);

            // This will strip extra whitespace for us.
            $filtered = $this->dfStripAllTags($filtered, false);

            // Use html entities in a special case to make sure no later
            // newline stripping stage could lead to a functional tag
            $filtered = str_replace("<\n", "&lt;\n", $filtered);
        }

        $filtered = preg_replace('/[\r\n\t ]+/', ' ', $filtered);

        $filtered = trim($filtered);

        $found = false;

        while (preg_match('/%[a-f0-9]{2}/i', $filtered, $match)) {
            $filtered = str_replace($match[0], '', $filtered);
            $found = true;
        }

        if ($found) {
            // Strip out the whitespace that may now exist after removing the octets.
            $filtered = trim(preg_replace('/ +/', ' ', $filtered));
        }

        return $filtered;
    }

    /**
     * Clean text
     *
     * @param string $text Text input
     *
     * @return null|string|string[]
     * @since  version
     */
    private function preKsesLessThan($text)
    {
        return preg_replace_callback('%<[^>]*?((?=<)|>|$)%', array($this, 'preKsesLessThanCallback'), $text);
    }

    /**
     * Callback for preKsesLessThan
     *
     * @param array $matches Matches string
     *
     * @return mixed
     * @since  version
     */
    private function preKsesLessThanCallback($matches)
    {
        if (false === strpos($matches[0], '>')) {
            return htmlspecialchars($matches[0], ENT_COMPAT, 'UTF-8');
        }

        return $matches[0];
    }

    /**
     * Strip all tags
     *
     * @param string  $string       Input string
     * @param boolean $removeBreaks Remove break and tab character
     *
     * @return string
     * @since  version
     */
    private function dfStripAllTags($string, $removeBreaks = false)
    {
        $string = preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', $string);
        $string = strip_tags($string);

        if ($removeBreaks) {
            $string = preg_replace('/[\r\n\t ]+/', ' ', $string);
        }

        return trim($string);
    }
}
