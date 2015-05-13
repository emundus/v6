<?php
// +---------------------------------------------------------------------+
// | FPDI PDF-Parser v.1.1.1                                               |
// | Copyright (c) 2009-2014 Setasign - Jan Slabon                       |
// +---------------------------------------------------------------------+
// | This source file is subject to the                                  |
// |    "FPDI PDF-Parser Commercial Developer License Agreement"         |
// | that is bundled with this package in the file                       |
// |    "FPDI-PDF-Parser-License.pdf"                                    |
// +---------------------------------------------------------------------+
// | Homepage: http://www.setasign.com                                   |
// | E-mail: support@setasign.com                                        |
// +---------------------------------------------------------------------+

/**
 * Class FilterPredictor
 */
class FilterPredictor
{
    /**
     * Whether or not to only write algorithm byte if predictor value is 15.
     * If set to <i>true</i>, the algorithm byte is written at the beginning
     * of every line for all PNG predictors. If set to <i>false</i>, this
     * byte is only written for optimum png compression, which can vary
     * the compression algorithm for each row.
     */
    public $alwaysWritePredictorByte = true;

    /**
     * Value prediction using the Alan W. Paeth algorithm
     *
     * @param $left The value to the left of the processed data entry.
     * @param $above The value above the processed data entry.
     * @param $upperLeft The value to the upper left of the processed data entry.
     * @return Returns the prediction value according to the Peath algorithm
     */
    protected function _paethPredictor($left, $above, $upperLeft) {
        // initial estimate
        $p = $left + $above - $upperLeft;

        // distances to a, b, c
        $pLeft      = abs($p - $left);
        $pAbove     = abs($p - $above);
        $pUpperLeft = abs($p - $upperLeft);

        // return nearest of $left, $above, $upperLeft,
        // breaking ties in order $left, $above, $upperLeft.
        if ($pLeft <= $pAbove && $pLeft <= $pUpperLeft) {
            return $left;
        } else if ($pAbove <= $pUpperLeft) {
            return $above;
        } else {
            return $upperLeft;
        }
    }

    /**
     * Decodes data using a predictor function.
     *
     * @param string $data The data area to be decoded.
     * @param int $predictor The predictor used to encode the data.
     * @param null $columns The number of columns if the encoded data is a bitmap.
     * @param null $colors The number of colors if the encoded data is a bitmap.
     * @param null $bitsPerComponent The number of bits per component/element of the encoded data.
     *
     * @return string The decoded data.
     * @throws Exception
     */
    public function decode($data, $predictor = 0, $columns = null, $colors = null, $bitsPerComponent = null)
    {
        // no predictor
        if ($predictor == 1) {
            return $data;

        } else if ($predictor == 2) { // TIFF
            // not supported
            throw new Exception("TIFF predictor not yet supported");

        } else if ($predictor >= 10 && $predictor <= 15) { // PNG predictors

            // make sure parameters contain valid values
            $columns          = is_null($columns)          ? 1 : (int)$columns;
            $colors           = is_null($colors)           ? 1 : (int)$colors;
            $bitsPerComponent = is_null($bitsPerComponent) ? 8 : (int)$bitsPerComponent;

            // compute bitmap parameters
            $bytesPerPixel = (int) ceil($colors * $bitsPerComponent / 8);
            $bytesPerRow = (int) (($colors * $columns * $bitsPerComponent + 7) / 8);

            // the return (decoded) data
            $out = '';

            // some variables needed to process the data
            $currRowString = '';	// the currently read row as a string
            $offset = 0; // the offset in the source data ($data) while reading/decoding it
            $currRowData = array(); // the data of the current row
            $priorRowData = array_fill(0, $bytesPerRow, 0); // the data of the previous row

            // initialize the predictor for the current row
            $currPredictor = $predictor;

            // read until EOF
            $eof = false;
            while (!$eof)
            {
                // read first algorithm byte for PNG predictor 15
                if ($this->alwaysWritePredictorByte || $predictor == 15) {
                    $currPredictor = ord(substr($data, $offset++, 1));
                    if (!is_null($currPredictor)) {
                        $currPredictor += 10;
                    } else {
                        $eof = true;
                    }
                }

                // read row
                if (!$eof) {
                    $currRowString = substr($data, $offset, $bytesPerRow);
                    if (strlen($currRowString) != $bytesPerRow) {
                        $eof = true;
                        if (strlen($currRowString) != 0) {
                            throw new Exception("Could not read complete row while decoding data");
                        }
                    }

                    // process row
                    if (!$eof) {
                        // copy current row into an array
                        $currRowData = array();
                        $currRowLength = strlen($currRowString);
                        for ($i = 0; $i < $currRowLength; $i++) {
                            $currRowData[$i] = ord($currRowString[$i]);
                        }

                        // process row using the selected predictor
                        switch ($currPredictor) {
                            case 10: // PNG_FILTER_NONE
                                break;

                            case 11: // PNG_FILTER_SUB (left)
                                for ($i = $bytesPerPixel; $i < $bytesPerRow; $i++) {
                                    $currRowData[$i] = ($currRowData[$i] + $currRowData[$i - $bytesPerPixel]) & 0xff;
                                }
                                break;

                            case 12: // PNG_FILTER_UP (previous row)
                                for ($i = 0; $i < $bytesPerRow; $i++) {
                                    $currRowData[$i] = ($currRowData[$i] + $priorRowData[$i]) & 0xff;
                                }
                                break;

                            case 13: // PNG_FILTER_AVERAGE (to the left and previous row)
                                for ($i = 0; $i < $bytesPerPixel; $i++) {
                                    $currRowData[$i] = ($currRowData[$i] + floor($priorRowData[$i] / 2)) & 0xff;
                                }
                                for ($i = $bytesPerPixel; $i < $bytesPerRow; $i++) {
                                    $currRowData[$i] = ($currRowData[$i] + floor(($currRowData[$i - $bytesPerPixel] + $priorRowData[$i]) / 2)) & 0xff;
                                }
                                break;

                            case 14: // PNG_FILTER_PAETH
                                for ($i = 0; $i < $bytesPerRow; $i++) {
                                    // execute peath predictor
                                    $left      = ($i < $bytesPerPixel) ? 0 : $currRowData[$i - $bytesPerPixel];
                                    $above     = $priorRowData[$i];
                                    $upperLeft = ($i < $bytesPerPixel) ? 0 : $priorRowData[$i - $bytesPerPixel];
                                    $predicted = $this->_paethPredictor($left, $above, $upperLeft);

                                    // encode data
                                    $currRowData[$i] = ($currRowData[$i] + $predicted) & 0xff;
                                }
                                break;

                            default:
                                // error PNG filter unknown.
                                throw new Exception('unrecognized png predictor (' . $currPredictor . ') while decoding data');

                        } // switch on current PNG predictor

                        // copy data to output
                        for ($i = 0; $i < $currRowLength; $i++) {
                            $out .= chr($currRowData[$i]);
                        }

                        // copy current row to previous row
                        $priorRowData = $currRowData;

                        // offset to next row
                        $offset += $bytesPerRow;
                    }
                }
            }

            // return decoded data
            return $out;

        } else { // if PNG predictor
            throw new Exception("unrecognized predictor: " . $predictor);
        }
    }

    /**
     * Encodes data using a predictor function.
     *
     * @param string $data The data area to be encoded.
     * @param int $predictor The predictor to be used to encode the data.
     * @param null $columns The number of columns if the data to be encoded is a bitmap.
     * @param null $colors The number of colors if the data to be encoded is a bitmap.
     * @param null $bitsPerComponent The number of bits per component/element of the data to be encoded.
     *
     * @return string The encoded data.
     * @throws Exception
     */
    function encode($data, $predictor = 0, $columns = null, $colors = null, $bitsPerComponent = null)
    {

        if ($predictor == 1) { // no predictor
            return $data;

        } else if ($predictor == 2) {// TIFF
            // not supported
            throw new Exception("TIFF predictor not yet supported");

        } else if ($predictor >= 10 && $predictor <= 15) { // PNG predictors
            // make sure parameters contain valid values
            $columns          = is_null($columns)          ? 1 : (int)$columns;
            $colors           = is_null($colors)           ? 1 : (int)$colors;
            $bitsPerComponent = is_null($bitsPerComponent) ? 8 : (int)$bitsPerComponent;

            // compute bitmap parameters
            $bytesPerPixel = $colors * $bitsPerComponent / 8;
            $bytesPerRow = (int) (($colors * $columns * $bitsPerComponent + 7) / 8);

            // the return (encoded) data
            $out = '';

            // some variables needed to process the data
            $currRowString = '';	// the currently read row as a string
            $offset = 0; // the offset in the source data ($data) while reading/decoding it
            $currRowData = array(); // the data of the current row
            $priorRowData = array_fill(0, $bytesPerRow, 0); // the data of the previous row

            // read the filter type byte and a whole row of data
            while ( ($currRowString = substr($data, $offset, $bytesPerRow)) &&
                strlen($currRowString) == $bytesPerRow) {
                // copy current row into an array
                $currRowData = array();
                $currRowLength = strlen($currRowString);
                for ($i = 0; $i < $currRowLength; $i++) {
                    $currRowData[$i] = ord($currRowString[$i]);
                }

                // select predictor
                $currPredictor = $predictor;

                // find optimal predictor
                if ($predictor == 15) {
                    // compute a value for the SUB predictor
                    $subPredictor = 0;
                    for ($i = $bytesPerRow - 1; $i >= $bytesPerPixel; $i--) {
                        $subPredictor += abs($currRowData[$i] - $currRowData[$i - $bytesPerPixel]);
                    }

                    // compute a value for the UP predictor
                    $upPredictor = 0;
                    for ($i = 0; $i < $bytesPerRow; $i++) {
                        $upPredictor += abs($currRowData[$i] - $priorRowData[$i]);
                    }

                    // compute a value for the AVERAGE predictor
                    $averagePredictor = 0;
                    for ($i = $bytesPerRow - 1; $i >=$bytesPerPixel; $i--) {
                        $averagePredictor += abs($currRowData[$i] - floor(($currRowData[$i - $bytesPerPixel] + $priorRowData[$i]) / 2));
                    }

                    for ($i = 0; $i < $bytesPerPixel; $i++) {
                        $averagePredictor += abs($currRowData[$i] - floor($priorRowData[$i] / 2));
                    }

                    // compute a value for the PEATH predictor
                    $peathPredictor = 0;
                    for ($i = $bytesPerRow - 1; $i >= 0; $i--) {
                        $left      = ($i < $bytesPerPixel) ? 0 : $currRowData[$i - $bytesPerPixel];
                        $above     = $priorRowData[$i];
                        $upperLeft = ($i < $bytesPerPixel) ? 0 : $priorRowData[$i - $bytesPerPixel];
                        $predicted = $this->_paethPredictor($left, $above, $upperLeft);
                        $peathPredictor += abs($currRowData[$i] - $predicted);
                    }

                    // select the best predictor
                    if ($subPredictor <= $upPredictor && $subPredictor <= $averagePredictor && $subPredictor <= $peathPredictor) {
                        $currPredictor = 11;
                    } else if ($upPredictor <= $subPredictor && $upPredictor <= $averagePredictor && $upPredictor <= $peathPredictor) {
                        $currPredictor = 12;
                    } else if ($averagePredictor <= $subPredictor && $averagePredictor <= $upPredictor && $averagePredictor <= $peathPredictor) {
                        $currPredictor = 13;
                    } else {
                        $currPredictor = 14;
                    }
                }

                // process row using the selected filter
                switch ($currPredictor) {
                    case 10: // PNG_FILTER_NONE
                        break;

                    case 11: // PNG_FILTER_SUB (left)
                        for ($i = $bytesPerRow - 1; $i >= $bytesPerPixel; $i--) {
                            $currRowData[$i] = ($currRowData[$i] - $currRowData[$i - $bytesPerPixel]) & 0xff;
                        }
                        break;

                    case 12: // PNG_FILTER_UP (previous row)
                        for ($i = 0; $i < $bytesPerRow; $i++) {
                            $currRowData[$i] = ($currRowData[$i] - $priorRowData[$i]) & 0xff;
                        }
                        break;

                    case 13: // PNG_FILTER_AVERAGE (to the left and previous row)
                        for ($i = $bytesPerRow - 1; $i >= $bytesPerPixel; $i--) {
                            $currRowData[$i] = ($currRowData[$i] - floor(($currRowData[$i - $bytesPerPixel] + $priorRowData[$i]) / 2) & 0xff);
                        }
                        for ($i = 0; $i < $bytesPerPixel; $i++) {
                            $currRowData[$i] = ($currRowData[$i] - floor($priorRowData[$i] / 2)) & 0xff;
                        }
                        break;

                    case 14: // PNG_FILTER_PAETH
                        for ($i = $bytesPerRow - 1; $i >= 0 ; $i--) {
                            // execute peath predictor
                            $left      = ($i < $bytesPerPixel) ? 0 : $currRowData[$i - $bytesPerPixel];
                            $above     = $priorRowData[$i];
                            $upperLeft = ($i < $bytesPerPixel) ? 0 : $priorRowData[$i - $bytesPerPixel];
                            $predicted = $this->_paethPredictor($left, $above, $upperLeft);

                            // encode data
                            $currRowData[$i] = ($currRowData[$i] - $predicted) & 0xff;
                        }
                        break;

                    default:
                        // error PNG filter unknown.
                        throw new Exception('unrecognized png predictor (' . $currPredictor . ') while encoding data');

                } // switch on current PNG predictor

                // copy data to output
                if ($this->alwaysWritePredictorByte || $predictor == 15) {
                    $out .= chr($currPredictor - 10);
                }
                for ($i = 0; $i < $currRowLength; $i++) {
                    $out .= chr($currRowData[$i]);
                }

                // copy current row to previous row
                for ($i = 0; $i < $currRowLength; $i++) {
                    $priorRowData[$i] = ord($currRowString[$i]);
                }

                // offset to next row
                $offset += $bytesPerRow;

            } // while reading data

            // return encoded data
            return $out;

        } else { // if PNG predictor
            throw new Exception("unrecognized predictor: " . $predictor);
        }
    }
}