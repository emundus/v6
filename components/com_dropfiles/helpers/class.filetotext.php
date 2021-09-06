<?php

//error_reporting(0);
class Filetotext
{

    private $filename;
    var $multibyte = 4; // Use setUnicode(TRUE|FALSE)
    var $convertquotes = ENT_QUOTES; // ENT_COMPAT (double-quotes), ENT_QUOTES (Both), ENT_NOQUOTES (None)
    var $showprogress = false; // TRUE if you have problems with time-out
    var $decodedtext = '';

    public function __construct($filePath)
    {
        $this->filename = $filePath;
    }

    public function convertToText()
    {

        if (isset($this->filename) && !file_exists($this->filename)) {
            return 'File Not exists';
        }

        $fileArray = pathinfo($this->filename);
        $file_ext = $fileArray['extension'];
        if ($file_ext == 'doc' || $file_ext == 'docx') {
            if ($file_ext == 'doc') {
                return $this->read_doc();
            } else {
                return $this->read_docx();
            }
        } elseif ($file_ext == 'pdf') {
            $this->setFilename($this->filename);
            $this->decodePDF();

            return $this->output();
        } else {
            return 'Invalid File Type';
        }
    }

    private function read_doc()
    {
        $fileHandle = fopen($this->filename, 'r');
        $line = @fread($fileHandle, filesize($this->filename));
        $lines = explode(chr(0x0D), $line);
        $outtext = '';
        foreach ($lines as $thisline) {
            $pos = strpos($thisline, chr(0x00));
            if (($pos !== false) || (strlen($thisline) == 0)) {
            } else {
                $outtext .= $thisline . ' ';
            }
        }
        $outtext = preg_replace("/[^a-zA-Z0-9\s\,\.\-\n\r\t@\/\_\(\)]/", '', $outtext);

        return $outtext;
    }

    private function read_docx()
    {
        $striped_content = '';
        $content = '';

        $zip = zip_open($this->filename);

        if (!$zip || is_numeric($zip)) {
            return false;
        }

        while ($zip_entry = zip_read($zip)) {
            if (zip_entry_open($zip, $zip_entry) == false) {
                continue;
            }

            if (zip_entry_name($zip_entry) != 'word/document.xml') {
                continue;
            }

            $content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

            zip_entry_close($zip_entry);
        }

        zip_close($zip);

        $content = str_replace('</w:r></w:p></w:tc><w:tc>', ' ', $content);
        $content = str_replace('</w:r></w:p>', "\r\n", $content);
        $striped_content = strip_tags($content);

        return $striped_content;
    }

    private function setFilename($filename)
    {
        $this->decodedtext = '';
        $this->filename = $filename;
    }

    private function output($echo = false)
    {
        if ($echo) {
            echo $this->decodedtext;
        } else {
            return $this->decodedtext;
        }
    }

    private function setUnicode($input)
    {
        if ($input == true) {
            $this->multibyte = 4;
        } else {
            $this->multibyte = 2;
        }
    }

    private function decodePDF()
    {
        $infile = @file_get_contents($this->filename, FILE_BINARY);
        if (empty($infile)) {
            return '';
        }

        $transformations = array();
        $texts = array();

        preg_match_all("#obj[\n|\r](.*)endobj[\n|\r]#ismU", $infile . "endobj\r", $objects);
        $objects = @$objects[1];

        for ($i = 0; $i < count($objects); $i++) {
            $currentObject = $objects[$i];

            @set_time_limit();
            if ($this->showprogress) {
                flush();
                ob_flush();
            }

            if (preg_match("#stream[\n|\r](.*)endstream[\n|\r]#ismU", $currentObject . "endstream\r", $stream)) {
                $stream = ltrim($stream[1]);
                $options = $this->getObjectOptions($currentObject);
                if (!(empty($options['Length1']) && empty($options['Type']) && empty($options['Subtype']))) {
                    continue;
                }

                unset($options['Length']);

                $data = $this->getDecodedStream($stream, $options);

                if (strlen($data)) {
                    if (preg_match_all("#BT[\n|\r](.*)ET[\n|\r]#ismU", $data . "ET\r", $textContainers)) {
                        $textContainers = @$textContainers[1];
                        $this->getDirtyTexts($texts, $textContainers);
                    } else {
                        $this->getCharTransformations($transformations, $data);
                    }
                }
            }
        }

        $this->decodedtext = $this->getTextUsingTransformations($texts, $transformations);
    }


    private function decodeAsciiHex($input)
    {
        $output = '';

        $isOdd = true;
        $isComment = false;

        for ($i = 0, $codeHigh = -1; $i < strlen($input) && $input[$i] != '>'; $i++) {
            $c = $input[$i];

            if ($isComment) {
                if ($c == '\r' || $c == '\n') {
                    $isComment = false;
                }
                continue;
            }

            switch ($c) {
                case '\0':
                case '\t':
                case '\r':
                case '\f':
                case '\n':
                case ' ':
                    break;
                case '%':
                    $isComment = true;
                    break;

                default:
                    $code = hexdec($c);
                    if ($code === 0 && $c != '0') {
                        return '';
                    }

                    if ($isOdd) {
                        $codeHigh = $code;
                    } else {
                        $output .= chr($codeHigh * 16 + $code);
                    }

                    $isOdd = !$isOdd;
                    break;
            }
        }

        if ($input[$i] != '>') {
            return '';
        }

        if ($isOdd) {
            $output .= chr($codeHigh * 16);
        }

        return $output;
    }

    private function decodeAscii85($input)
    {
        $output = '';

        $isComment = false;
        $ords = array();

        for ($i = 0, $state = 0; $i < strlen($input) && $input[$i] != '~'; $i++) {
            $c = $input[$i];

            if ($isComment) {
                if ($c == '\r' || $c == '\n') {
                    $isComment = false;
                }
                continue;
            }

            if ($c == '\0' || $c == '\t' || $c == '\r' || $c == '\f' || $c == '\n' || $c == ' ') {
                continue;
            }
            if ($c == '%') {
                $isComment = true;
                continue;
            }
            if ($c == 'z' && $state === 0) {
                $output .= str_repeat(chr(0), 4);
                continue;
            }
            if ($c < '!' || $c > 'u') {
                return '';
            }

            $code = ord($input[$i]) & 0xff;
            $ords[$state++] = $code - ord('!');

            if ($state == 5) {
                $state = 0;
                for ($sum = 0, $j = 0; $j < 5; $j++) {
                    $sum = $sum * 85 + $ords[$j];
                }
                for ($j = 3; $j >= 0; $j--) {
                    $output .= chr($sum >> ($j * 8));
                }
            }
        }
        if ($state === 1) {
            return '';
        } elseif ($state > 1) {
            for ($i = 0, $sum = 0; $i < $state; $i++) {
                $sum += ($ords[$i] + ($i == $state - 1)) * pow(85, 4 - $i);
            }
            for ($i = 0; $i < $state - 1; $i++) {
                try {
                    if (false == ($o = chr($sum >> ((3 - $i) * 8)))) {
                        throw new Exception('Error');
                    }
                    $output .= $o;
                } catch (Exception $e) { /*Dont do anything*/
                }
            }
        }

        return $output;
    }

    private function decodeFlate($data)
    {
        return @gzuncompress($data);
    }

    private function getObjectOptions($object)
    {
        $options = array();

        if (preg_match('#<<(.*)>>#ismU', $object, $options)) {
            $options = explode('/', $options[1]);
            @array_shift($options);

            $o = array();
            for ($j = 0; $j < @count($options); $j++) {
                $options[$j] = preg_replace('#\s+#', ' ', trim($options[$j]));
                if (strpos($options[$j], ' ') !== false) {
                    $parts = explode(' ', $options[$j]);
                    $o[$parts[0]] = $parts[1];
                } else {
                    $o[$options[$j]] = true;
                }
            }
            $options = $o;
            unset($o);
        }

        return $options;
    }

    private function getDecodedStream($stream, $options)
    {
        $data = '';
        if (empty($options['Filter'])) {
            $data = $stream;
        } else {
            $length = !empty($options['Length']) ? $options['Length'] : strlen($stream);
            $_stream = substr($stream, 0, $length);

            foreach ($options as $key => $value) {
                if ($key == 'ASCIIHexDecode') {
                    $_stream = $this->decodeAsciiHex($_stream);
                } elseif ($key == 'ASCII85Decode') {
                    $_stream = $this->decodeAscii85($_stream);
                } elseif ($key == 'FlateDecode') {
                    $_stream = $this->decodeFlate($_stream);
                } elseif ($key == 'Crypt') { // TO DO
                }
            }
            $data = $_stream;
        }

        return $data;
    }

    private function getDirtyTexts(&$texts, $textContainers)
    {
        for ($j = 0; $j < count($textContainers); $j++) {
            if (preg_match_all("#\[(.*)\]\s*TJ[\n|\r]#ismU", $textContainers[$j], $parts)) {
                $texts = array_merge($texts, array(@implode('', $parts[1])));
            } elseif (preg_match_all("#T[d|w|m|f]\s*(\(.*\))\s*Tj[\n|\r]#ismU", $textContainers[$j], $parts)) {
                $texts = array_merge($texts, array(@implode('', $parts[1])));
            } elseif (preg_match_all("#T[d|w|m|f]\s*(\[.*\])\s*Tj[\n|\r]#ismU", $textContainers[$j], $parts)) {
                $texts = array_merge($texts, array(@implode('', $parts[1])));
            }
        }
    }

    private function getCharTransformations(&$transformations, $stream)
    {
        preg_match_all('#([0-9]+)\s+beginbfchar(.*)endbfchar#ismU', $stream, $chars, PREG_SET_ORDER);
        preg_match_all('#([0-9]+)\s+beginbfrange(.*)endbfrange#ismU', $stream, $ranges, PREG_SET_ORDER);

        for ($j = 0; $j < count($chars); $j++) {
            $count = $chars[$j][1];
            $current = explode("\n", trim($chars[$j][2]));
            for ($k = 0; $k < $count && $k < count($current); $k++) {
                if (preg_match('#<([0-9a-f]{2,4})>\s+<([0-9a-f]{4,512})>#is', trim($current[$k]), $map)) {
                    $transformations[str_pad($map[1], 4, '0')] = $map[2];
                }
            }
        }
        for ($j = 0; $j < count($ranges); $j++) {
            $count = $ranges[$j][1];
            $current = explode("\n", trim($ranges[$j][2]));
            for ($k = 0; $k < $count && $k < count($current); $k++) {
                if (preg_match('#<([0-9a-f]{4})>\s+<([0-9a-f]{4})>\s+<([0-9a-f]{4})>#is', trim($current[$k]), $map)) {
                    $from = hexdec($map[1]);
                    $to = hexdec($map[2]);
                    $_from = hexdec($map[3]);

                    for ($m = $from, $n = 0; $m <= $to; $m++, $n++) {
                        $transformations[sprintf('%04X', $m)] = sprintf('%04X', $_from + $n);
                    }
                } elseif (preg_match('#<([0-9a-f]{4})>\s+<([0-9a-f]{4})>\s+\[(.*)\]#ismU', trim($current[$k]), $map)) {
                    $from = hexdec($map[1]);
                    $to = hexdec($map[2]);
                    $parts = preg_split('#\s+#', trim($map[3]));

                    for ($m = $from, $n = 0; $m <= $to && $n < count($parts); $m++, $n++) {
                        $transformations[sprintf('%04X', $m)] = sprintf('%04X', hexdec($parts[$n]));
                    }
                }
            }
        }
    }

    private function getTextUsingTransformations($texts, $transformations)
    {
        $document = '';
        for ($i = 0; $i < count($texts); $i++) {
            $isHex = false;
            $isPlain = false;

            $hex = '';
            $plain = '';
            for ($j = 0; $j < strlen($texts[$i]); $j++) {
                $c = $texts[$i][$j];
                switch ($c) {
                    case '<':
                        $hex = '';
                        $isHex = true;
                        $isPlain = false;
                        break;
                    case '>':
                        $hexs = str_split($hex, $this->multibyte); // 2 or 4 (UTF8 or ISO)
                        for ($k = 0; $k < count($hexs); $k++) {
                            $chex = str_pad($hexs[$k], 4, '0'); // Add tailing zero
                            if (isset($transformations[$chex])) {
                                $chex = $transformations[$chex];
                            }
                            $document .= html_entity_decode('&#x' . $chex . ';', ENT_COMPAT,'UTF-8');
                        }
                        $isHex = false;
                        break;
                    case '(':
                        $plain = '';
                        $isPlain = true;
                        $isHex = false;
                        break;
                    case ')':
                        $document .= $plain;
                        $isPlain = false;
                        break;
                    case '\\':
                        $c2 = $texts[$i][$j + 1];
                        if (in_array($c2, array('\\', '(', ')'))) {
                            $plain .= $c2;
                        } elseif ($c2 == 'n') {
                            $plain .= '\n';
                        } elseif ($c2 == 'r') {
                            $plain .= '\r';
                        } elseif ($c2 == 't') {
                            $plain .= '\t';
                        } elseif ($c2 == 'b') {
                            $plain .= '\b';
                        } elseif ($c2 == 'f') {
                            $plain .= '\f';
                        } elseif ($c2 >= '0' && $c2 <= '9') {
                            $oct = preg_replace('#[^0-9]#', '', substr($texts[$i], $j + 1, 3));
                            $j += strlen($oct) - 1;
                            $plain .= html_entity_decode('&#' . octdec($oct) . ';', $this->convertquotes, 'UTF-8');
                        }
                        $j++;
                        break;

                    default:
                        if ($isHex) {
                            $hex .= $c;
                        } elseif ($isPlain) {
                            $plain .= $c;
                        }
                        break;
                }
            }
            $document .= "\n";
        }

        return $document;
    }
}
