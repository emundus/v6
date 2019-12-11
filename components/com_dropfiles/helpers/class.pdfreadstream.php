<?php
// Huge thank you to Erik Gustavsson erik@zikko.se, the original author of these classes
class pdf_readstream
{
    var $data;
    var $offset;
    var $size;
    var $allow_references;

    function __construct(&$data, $offset = 0)
    {
        $this->data = trim($data);
        $this->offset = $offset;
        $this->size = strlen($this->data);
    }

    function read_object()
    {
        $this->skip_whitespace();

        switch ($this->get_next()) {
            case '0':
            case '1':
            case '2':
            case '3':
            case '4':
            case '5':
            case '6':
            case '7':
            case '8':
            case '9':
            case '.':
            case '-':
            case '+':
                // number, object, reference

                $number = $this->read_while('0123456789+-.');

                if ($this->allow_references) {
                    if ($this->expect(' 0 R')) {
                        return $this->mark(new pdf_reference($number.' 0'));
                    } elseif ($this->expect(' 0 obj')) {
                        $value = $this->read_object();

                        $this->skip_whitespace();

                        $this->offset += 6;

                        #$endobj = $this->read(6);

                        #if( $endobj != 'endobj' )
                        #{
                        #    echo 'Unknown object data:'.$this->get_next(20)." at offset ".$this->offset."\n";
                        #    exit;
                        #}

                        #$value = new pdf_indirect_object( $value );

                        if (is_object($value)) {
                            return $this->mark($value);
                        } else {
                            return $value;
                        }
                    }
                }

                return (float)$number;

            case '(':
                // string;

                $this->offset++;

                $value='';

                $level=1;
                while (true) {
                    $next = $this->read();

                    if ($next=='(') {
                        $level++;
                    } elseif ($next==')') {
                        $level--;
                        if ($level==0) {
                            break;
                        }
                    } elseif ($next=='\\') {
                        $next = $this->read();

                        switch ($next) {
                            case 'n':
                                $value.="\n";
                                break;
                            case 'r':
                                $value.="\r";
                                break;
                            case 't':
                                $value.="\t";
                                break;
                            case 'b':
                                $value.="\b";
                                break;
                            case 'f':
                                $value.="\f";
                                break;
                            case '(':
                                $value.='(';
                                break;
                            case ')':
                                $value.=')';
                                break;
                            case '\\':
                                $value.='\\';
                                break;
                            default:
                                $next .= $this->read(2);
                                $value.=chr(octdec($next));
                                break;
                        }
                    } else {
                        $value .= $next;
                    }
                }

                return $value;

            case '/':
                // name;
                $this->offset++;
                return $this->read_until(' \n\r\/\<\>()\[\]');

            case '[':
                // array;
                $this->offset++;

                $value = array();

                while (true) {
                    $this->skip_whitespace();

                    if ($this->get_next() == ']') {
                        break;
                    }

                    $value[] = $this->read_object();
                }

                $this->offset++;
                return $value;

            case '%':
                // comment;
                $this->offset++;

                return $this->read_until(' \n\r');

            case '<':
                if (( $value = $this->read_dictionary() ) !== false) {
                    $this->skip_whitespace();

                    if ($this->expect('stream')) {
                        $data_length = $value['Length'];

                        if (is_object($data_length)) {
                            $data_length = $data_length->resolve();
                        }

                        $this->skip_whitespace();
                        $data = $this->read($data_length);

                        $this->skip_whitespace();

                        $this->offset += 9;

                        #$endstream = $this->read(9);

                        #if( $endstream != 'endstream' )
                        #{
                        #    echo 'Unknown object data:'.$this->get_next(20)." at offset ".$this->offset."\n";
                        #    exit;
                        #}

                        return $this->mark(new pdf_stream($value, $data));
                    } else {
                        return $value;
                    }
                } else {
                    // hex string
                    $this->offset++;

                    $hex = $this->read_until('>');

                    $value=pack('H*', $hex);

                    $this->offset++;

                    return $value;
                }
                break;

            case 'f':
                if ($this->expect('false')) {
                    return false;
                }

                break;

            case 'n':
                if ($this->expect('null')) {
                    return null;
                }

                break;


            case 's':
                if ($this->expect('startxref')) {
                    $this->skip_whitespace();
                    $value = $this->read_while('0123456789');

                    return $this->mark(new pdf_startxref($value));
                }
                break;

            case 't':
                if ($this->expect('true')) {
                    return true;
                } elseif ($this->expect('trailer')) {
                    $value = $this->read_object();
                    $value = $this->mark(new pdf_trailer($value));
                }

                break;
        }

        return new pdf_operator($this->read_until(' \n\r\/\<\>()\[\]'));
    }

    function read_dictionary()
    {
        if ($this->expect('<<')) {
            $value = array();

            while (true) {
                $this->skip_whitespace();

                if ($this->expect('>>')) {
                    break;
                }

                $obj = $this->read_object();

                if (is_string($obj)) {
                    $value[ $obj ] = $this->read_object();
                }
            }

            return $value;
        } else {
            return false;
        }
    }

    function mark($child)
    {
        $child->parent = $this;

        return $child;
    }

    function skip($count = 1)
    {
        $this->offset += $count;
    }

    function read($count = 1)
    {
        $v = substr($this->data, $this->offset, $count);
        $this->offset += $count;
        return $v;
    }

    function expect($str)
    {
        $l = strlen($str);

        if (substr($this->data, $this->offset, $l) == $str) {
            $this->offset += $l;
            return true;
        } else {
            return false;
        }
    }

    function get_next($count = 1)
    {
        return substr($this->data, $this->offset, $count);
    }

    function skip_whitespace()
    {
        preg_match('/['.' \n\r'.']*/', $this->data, $matches, 0, $this->offset);

        $this->offset += strlen($matches[0]);
    }

    function skip_until($chars)
    {
        preg_match('/[^'.$chars.']*/', $this->data, $matches, 0, $this->offset);

        $this->offset += strlen($matches[0]);
    }

    function skip_while($chars)
    {
        preg_match('/['.$chars.']*/', $this->data, $matches, 0, $this->offset);

        $this->offset += strlen($matches[0]);
    }

    function read_until($chars)
    {
        preg_match('/[^'.$chars.']*/', $this->data, $matches, 0, $this->offset);

        $this->offset += strlen($matches[0]);

        return $matches[0];
    }

    function read_while($chars)
    {
        preg_match('/['.$chars.']*/', $this->data, $matches, 0, $this->offset);

        $this->offset += strlen($matches[0]);

        return $matches[0];
    }

    function jump($offset)
    {
        $this->offset = $offset;
    }

    function eof()
    {
        return $this->offset >= strlen($this->data);
    }
}

class pdf extends pdf_readstream
{
    var $catalog;
    var $xref_table;

    function __construct($filename)
    {
        $infile = @file_get_contents($this->filename, FILE_BINARY);

        parent::__construct($infile);

        $this->xref_table = array();
        $this->objects_at_offsets = array();

        $this->allow_references = true;

        $this->jump(strrpos($this->data, 'startxref'));

        $this->expect('startxref');

        $offset = $this->read_object();

        $this->parse_xref($offset);

        if (isset($this->catalog)) {
            $this->catalog = $this->catalog->resolve();
        }
    }

    function parse_xref($offset)
    {
        $this->jump($offset);

        $this->expect('xref');

        while (true) {
            $this->skip_whitespace();

            if ($this->expect('trailer')) {
                break;
            }

            $start = $this->read_while('0123456789');

            $this->skip_whitespace();
            $count = $this->read_while('0123456789');

            for ($n=0; $n<$count; $n++) {
                $number = $start + $n;

                $this->skip_whitespace();
                $line = $this->read_while('0123456789 fn');

                list($offset,$generation,$type)=explode(' ', $line);

                $generation = (int)$generation;

                $this->xref_table[ $number.' '.$generation ] = (int)$offset;
            }
        }

        $this->skip_whitespace();
        $trailer = $this->read_dictionary();

        if (isset($trailer['Root'])) {
            $this->catalog = $trailer['Root'];
        }

        if (isset($trailer['Prev'])) {
            $this->parse_xref($trailer['Prev']);
        }
    }

    function get_pages()
    {
        $pages = array();

        if (isset($this->catalog['Pages']) && is_object($this->catalog['Pages'])) {
            $this->add_pages($this->catalog['Pages']->resolve(), $pages);
        }

        return $pages;
    }

    function add_pages($array, &$pages)
    {
        $type = $array['Type'];

        if ($type=='Pages') {
            $kids = $array['Kids'];

            foreach ($kids as $kid) {
                $this->add_pages($kid->resolve(), $pages);
            }
        } elseif ($type=='Page') {
            $pages[] = new pdf_page($array);
        }
    }

    function get_dimensions($array = false)
    {
        $pages = $this->get_pages();

        return $pages[0]->get_dimensions();
    }

    function debug()
    {
        return pdf_debug($this->catalog);
    }

    function resolve($reference)
    {
        $old_offset = $this->offset;

        $this->offset = $this->xref_table[ $reference->value ];

        $value = $this->read_object();

        $this->offset = $old_offset;

        return $value;
    }
}

class pdf_page
{
    var $props;

    function __construct($props)
    {
        $this->props = $props;
    }

    function get_dimensions()
    {
        $mediabox = false;
        $rotate = false;

        $array = $this->props;

        while (true) {
            if ($mediabox === false) {
                if (isset($array['MediaBox'])) {
                    $mediabox = $array['MediaBox'];
                }
            }

            if ($rotate === false) {
                if (isset($array['Rotate'])) {
                    $rotate = $array['Rotate'];
                }
            }

            if ($mediabox !== false and $rotate !== false) {
                break;
            } elseif (isset($array['Parent'])) {
                $array = $array['Parent']->resolve();
            } else {
                break;
            }
        }

        if ($rotate===false) {
            $rotate=0;
        }

        list( $x1, $y1, $x2, $y2 ) = $mediabox;

        $width = abs($x1-$x2);
        $height = abs($y1-$y2);

        if (( $rotate % 180 ) == 0) {
            return array( $width, $height );
        } else {
            return array( $height, $width );
        }
    }

    function get_content_stream()
    {
        $contents = $this->props['Contents'];

        if (is_array($contents)) {
            $content_data = '';
            foreach ($contents as $part) {
                $content_data .= $part->resolve()->get_data();
            }
        } else {
            // TODO: in some cases an array is returned here, which triggers a fatal error
            if (! is_array($contents->resolve())) {
                $content_data = $contents->resolve()->get_data();
            } else {
                $content_data = '';
            }
        }

        return new pdf_content_stream($content_data);
    }

    function get_text()
    {
        return $this->get_content_stream()->get_text();
    }

    function debug()
    {
        return pdf_debug($this->props);
    }
}

class pdf_content_stream extends pdf_readstream
{
    var $operators;

    function __construct(&$data)
    {
        parent::__construct($data);

        $this->allow_references = false;

        $this->operators = array();

        $operands = array();

        $textarea=false;

        while (!$this->eof()) {
            $object = $this->read_object();

            if (is_object($object)) {
                #if( is_a($object,'operator') )
                {
                if ($object->value == 'BT') {
                    $textarea = true;
                } elseif ($object->value == 'ET') {
                        $textarea = false;
                }

                if ($textarea) {
                    $object->operands = $operands;
                    $this->operators[] = $object;
                }
                }

                $operands = array();
            } else {
                $operands[] = $object;
            }
        }
    }

    function get_text()
    {
        $text='';

        reset($this->operators);
        foreach ($this->operators as $operator) {
            $text .= $operator->get_text();
        }

        return $text;
    }

    function debug($level = 0)
    {
        $inset=str_repeat("\t", $level);

        echo $inset."content_stream\n";
        echo $inset."(\n";

        reset($this->operators);
        foreach ($this->operators as $operator) {
            echo $operator->debug($level+1);
        }

        echo $inset.")\n";
    }
}



function pdf_debug($value, $level = 0)
{
    $inset = str_repeat("\t", $level);

    if (is_object($value)) {
        return $value->debug($level);
    } elseif (is_array($value)) {
        $str='';
        $str.=$inset."Array\n";
        $str.=$inset."(\n";

        reset($value);
        while (list($key,$v)=each($value)) {
            if (is_object($v) or is_array($v)) {
                $str.=$inset."\t".$key." =>\n";
                $str.=pdf_debug($v, $level+2);
            } else {
                $str.=$inset."\t".$key.' => '.pdf_debug($v);
            }
        }

        $str.=$inset.")\n";

        return $str;
    } elseif (is_bool($value)) {
        if ($value) {
            return $inset."true\n";
        } else {
            return $inset."false\n";
        }
    } elseif (is_null($value)) {
        return $inset."NULL\n";
    } elseif (is_string($value)) {
        return $inset."\"$value\"\n";
    } else {
        return $inset.$value."\n";
    }
}

class pdf_object
{
    var $parent;
    var $value;

    function __construct($value)
    {
        $this->value = $value;
    }

    function resolve()
    {
        return $this;
    }

    function get_value()
    {
        return $this->value;
    }

    function debug($level = 0)
    {
        $inset = str_repeat("\t", $level);

        $str = $inset.get_class($this).' : '."\n";

        $str .= $inset."(\n";
        $str .= pdf_debug($this->value, $level+1);
        $str .= $inset.")\n";

        return $str;
    }
}

class pdf_reference extends pdf_object
{
    function resolve()
    {
        return $this->parent->resolve($this);
    }
}

class pdf_stream extends pdf_object
{
    var $data;
    function __construct($value, &$data)
    {
        $this->value = $value;
        $this->data = $data;
    }

    function get_data()
    {
        $object = $this->resolve();

        $filter = $object->value['Filter'];

        switch ($filter) {
            case false:
                return $object->data;

            case 'FlateDecode':
                $data = @gzuncompress($object->data);

                if (!$data) {
                    return false;
                }

                return $data;

            default:
                return false;
        }
    }

    function get_text()
    {
        return $this->get_content_stream()->get_text();
    }
}

class pdf_operator extends pdf_object
{
    var $operands;

    function debug($level = 0)
    {
        $inset=str_repeat("\t", $level);

        echo $inset.'operator( '.$this->value." )\n";

        if (count($this->operands)) {
            echo $inset."(\n";

            reset($this->operands);
            foreach ($this->operands as $operand) {
                echo pdf_debug($operand, $level+1);
            }

            echo $inset.")\n";
        }
    }

    function get_text()
    {
        switch ($this->value) {
            case 'Tj':
                return $this->operands[0];

            case '\'':
                return "\n".$this->operands[0];

            case '"':
                return "\n".$this->operands[2];

            case 'TJ':
                $string='';

                $parts = $this->operands[0];

                foreach ($parts as $part) {
                    if (is_string($part)) {
                        $string .= $part;
                    } elseif ($part < -150) {
                        $string .= ' ';
                    }
                }

                return $string;

            case 'Td':
            case 'TD':
                $delta_y = $this->operands[1];

                if ($delta_y!=0) {
                    return "\n";
                } else {
                    return '';
                }

            case 'Tm':
                $delta_y = $this->operands[5];

                if ($delta_y!=0) {
                    return "\n";
                } else {
                    return '';
                }

            case 'T*':
                return "\n";

            default:
                return '';
        }
    }
}
