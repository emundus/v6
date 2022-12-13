<?php

/**
 * This file is part of FPDI PDF-Parser
 *
 * @package   setasign\FpdiPdfParser
 * @copyright Copyright (c) 2021 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @license   FPDI PDF-Parser Commercial Developer License Agreement (see LICENSE file within this package)
 */

namespace setasign\FpdiPdfParser\PdfParser\Filter;

use setasign\Fpdi\PdfParser\Filter\FilterException;

/**
 * Class Predictor
 */
class Predictor
{
    /**
     * @var int
     */
    protected $predictor = 1;

    /**
     * @var int
     */
    protected $colors = 1;

    /**
     * @var int
     */
    protected $bitsPerComponent = 8;

    /**
     * @var int
     */
    protected $columns = 1;

    /**
     * The constructor.
     *
     * @param int $predictor
     * @param int $colors
     * @param int $bitsPerComponent
     * @param int $columns
     */
    public function __construct(
        $predictor = 1,
        $colors = null,
        $bitsPerComponent = null,
        $columns = null
    ) {
        if ($predictor !== null && $predictor != 1) {
            $this->predictor = (int) $predictor;

            if ($colors !== null) {
                $this->colors = (int)$colors;
            }

            if ($bitsPerComponent !== null) {
                $this->bitsPerComponent = (int)$bitsPerComponent;
            }

            if ($columns !== null) {
                $this->columns = (int)$columns;
            }
        }
    }

    /**
     * Value prediction using the Alan W. Paeth algorithm.
     *
     * @param int|float $left The value to the left of the processed data entry.
     * @param int|float $above The value above the processed data entry.
     * @param int|float $upperLeft The value to the upper left of the processed data entry.
     * @return int|float Returns the prediction value according to the Peath algorithm
     */
    protected function paethPredictor($left, $above, $upperLeft)
    {
        // initial estimate
        $p = $left + $above - $upperLeft;

        // distances to a, b, c
        $pLeft = \abs($p - $left);
        $pAbove = \abs($p - $above);
        $pUpperLeft = \abs($p - $upperLeft);

        // return nearest of $left, $above, $upperLeft,
        // breaking ties in order $left, $above, $upperLeft.
        if ($pLeft <= $pAbove && $pLeft <= $pUpperLeft) {
            return $left;
        }

        if ($pAbove <= $pUpperLeft) {
            return $above;
        }

        return $upperLeft;
    }

    /**
     * Decodes a string using a predictor function.
     *
     * @param string $data The input string
     * @return string The decoded data
     * @throws FilterException
     * @throws PredictorException
     */
    public function decode($data)
    {
        // no predictor
        if ($this->predictor === 1) {
            return $data;
        }

        if ($this->predictor === 2) { // TIFF
            // not supported
            throw new FilterException(
                'TIFF predictor not yet supported',
                FilterException::NOT_IMPLEMENTED
            );
        }

        if ($this->predictor >= 10 && $this->predictor <= 15) { // PNG predictors
            return $this->decodePng($data);
        }

        throw new PredictorException(
            'Unrecognized predictor: ' . $this->predictor,
            PredictorException::UNRECOGNIZED_PREDICTOR
        );
    }

    /**
     * Decode png predictors.
     *
     * @param string $data
     * @return string
     * @throws PredictorException
     */
    protected function decodePng($data)
    {
        // compute bitmap parameters
        $bytesPerPixel = (int) \ceil($this->colors * $this->bitsPerComponent / 8);
        $bytesPerRow = (int) \ceil($this->colors * $this->columns * $this->bitsPerComponent / 8);

        // the return (decoded) data
        $out = '';

        // some variables needed to process the data
        /** @noinspection PhpUnusedLocalVariableInspection */
        $currRowString = ''; // the currently read row as a string
        $offset = 0; // the offset in the source data ($data) while reading/decoding it
        /** @noinspection PhpUnusedLocalVariableInspection */
        $currRowData = []; // the data of the current row
        $priorRowData = \array_fill(0, $bytesPerRow, 0); // the data of the previous row

        // initialize the predictor for the current row
        $currPredictor = $this->predictor;

        // read until EOF
        $eof = false;
        while (!$eof) {
            // read first algorithm byte
            if (isset($data[$offset])) {
                $currPredictor = \ord($data[$offset]) + 10;
            } else {
                $eof = true;
            }
            $offset++;

            if ($eof) {
                break;
            }

            // read row
            $currRowString = (string) \substr($data, $offset, $bytesPerRow);
            if (\strlen($currRowString) !== $bytesPerRow) {
                $eof = true;
            }

            // process row
            if ($currRowString !== '') {
                // copy current row into an array
                $currRowData = [];
                $currRowLength = \strlen($currRowString);
                for ($i = 0; $i < $currRowLength; $i++) {
                    $currRowData[$i] = \ord($currRowString[$i]);
                }

                // process row using the selected predictor
                switch ($currPredictor) {
                    case 10: // PNG_FILTER_NONE
                        break;

                    case 11: // PNG_FILTER_SUB (left)
                        for ($i = $bytesPerPixel; $i < $currRowLength; $i++) {
                            $currRowData[$i] = ($currRowData[$i] + $currRowData[$i - $bytesPerPixel]) & 0xff;
                        }
                        break;

                    case 12: // PNG_FILTER_UP (previous row)
                        for ($i = 0; $i < $currRowLength; $i++) {
                            $currRowData[$i] = ($currRowData[$i] + $priorRowData[$i]) & 0xff;
                        }
                        break;

                    case 13: // PNG_FILTER_AVERAGE (to the left and previous row)
                        for ($i = 0; $i < $bytesPerPixel; $i++) {
                            $currRowData[$i] = ($currRowData[$i] + \floor($priorRowData[$i] / 2)) & 0xff;
                        }
                        for ($i = $bytesPerPixel; $i < $currRowLength; $i++) {
                            $currRowData[$i] = (
                                (
                                    $currRowData[$i]
                                    + \floor(($currRowData[$i - $bytesPerPixel] + $priorRowData[$i]) / 2)
                                ) & 0xff
                            );
                        }
                        break;

                    case 14: // PNG_FILTER_PAETH
                        for ($i = 0; $i < $currRowLength; $i++) {
                            // execute peath predictor
                            $left = ($i < $bytesPerPixel) ? 0 : $currRowData[$i - $bytesPerPixel];
                            $above = $priorRowData[$i];
                            $upperLeft = ($i < $bytesPerPixel) ? 0 : $priorRowData[$i - $bytesPerPixel];
                            $predicted = $this->paethPredictor($left, $above, $upperLeft);

                            // encode data
                            $currRowData[$i] = ($currRowData[$i] + $predicted) & 0xff;
                        }
                        break;

                    default:
                        // error PNG filter unknown.
                        throw new PredictorException(
                            'Unrecognized png predictor (' . $currPredictor . ') while decoding data',
                            PredictorException::UNRECOGNIZED_PNG_PREDICTOR
                        );
                } // switch on current PNG predictor

                // copy data to output
                for ($i = 0; $i < $currRowLength; $i++) {
                    $out .= \chr($currRowData[$i]);
                }

                // copy current row to previous row
                $priorRowData = $currRowData;

                // offset to next row
                $offset += $bytesPerRow;
            } // if not eof
        } // while reading data

        // return decoded data
        return $out;
    }
}
