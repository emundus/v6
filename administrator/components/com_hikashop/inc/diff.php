<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php


class HikashopDiffInc{

  const UNMODIFIED = 0;
  const DELETED    = 1;
  const INSERTED   = 2;

  public static function compare(
      $string1, $string2, $compareCharacters = false){

    $start = 0;
    if ($compareCharacters){
      $sequence1 = $string1;
      $sequence2 = $string2;
      $end1 = strlen($string1) - 1;
      $end2 = strlen($string2) - 1;
    }else{
      $sequence1 = preg_split('/\R/', $string1);
      $sequence2 = preg_split('/\R/', $string2);
      $end1 = count($sequence1) - 1;
      $end2 = count($sequence2) - 1;
    }

    while ($start <= $end1 && $start <= $end2
        && $sequence1[$start] == $sequence2[$start]){
      $start ++;
    }

    while ($end1 >= $start && $end2 >= $start
        && $sequence1[$end1] == $sequence2[$end2]){
      $end1 --;
      $end2 --;
    }

    $table = self::computeTable($sequence1, $sequence2, $start, $end1, $end2);

    $partialDiff =
        self::generatePartialDiff($table, $sequence1, $sequence2, $start);

    $diff = array();
    for ($index = 0; $index < $start; $index ++){
      $diff[] = array($sequence1[$index], self::UNMODIFIED);
    }
    while (count($partialDiff) > 0) $diff[] = array_pop($partialDiff);
    for ($index = $end1 + 1;
        $index < ($compareCharacters ? strlen($sequence1) : count($sequence1));
        $index ++){
      $diff[] = array($sequence1[$index], self::UNMODIFIED);
    }

    return $diff;

  }

  public static function compareFiles(
      $file1, $file2, $compareCharacters = false){

    return self::compare(
        file_get_contents($file1),
        file_get_contents($file2),
        $compareCharacters);

  }

  private static function computeTable(
      $sequence1, $sequence2, $start, $end1, $end2){

    $length1 = $end1 - $start + 1;
    $length2 = $end2 - $start + 1;

    $table = array(array_fill(0, $length2 + 1, 0));

    for ($index1 = 1; $index1 <= $length1; $index1 ++){

      $table[$index1] = array(0);

      for ($index2 = 1; $index2 <= $length2; $index2 ++){

        if ($sequence1[$index1 + $start - 1]
            == $sequence2[$index2 + $start - 1]){
          $table[$index1][$index2] = $table[$index1 - 1][$index2 - 1] + 1;
        }else{
          $table[$index1][$index2] =
              max($table[$index1 - 1][$index2], $table[$index1][$index2 - 1]);
        }

      }
    }

    return $table;

  }

  private static function generatePartialDiff(
      $table, $sequence1, $sequence2, $start){

    $diff = array();

    $index1 = count($table) - 1;
    $index2 = count($table[0]) - 1;

    while ($index1 > 0 || $index2 > 0){

      if ($index1 > 0 && $index2 > 0
          && $sequence1[$index1 + $start - 1]
              == $sequence2[$index2 + $start - 1]){

        $diff[] = array($sequence1[$index1 + $start - 1], self::UNMODIFIED);
        $index1 --;
        $index2 --;

      }elseif ($index2 > 0
          && $table[$index1][$index2] == $table[$index1][$index2 - 1]){

        $diff[] = array($sequence2[$index2 + $start - 1], self::INSERTED);
        $index2 --;

      }else{

        $diff[] = array($sequence1[$index1 + $start - 1], self::DELETED);
        $index1 --;

      }

    }

    return $diff;

  }

  public static function toString($diff, $separator = "\n"){

    $string = '';

    foreach ($diff as $line){

      switch ($line[1]){
        case self::UNMODIFIED : $string .= '  ' . $line[0];break;
        case self::DELETED    : $string .= '- ' . $line[0];break;
        case self::INSERTED   : $string .= '+ ' . $line[0];break;
      }

      $string .= $separator;

    }

    return $string;

  }

  public static function toHTML($diff, $separator = '<br/>'){

    $html = '';

    foreach ($diff as $line){

      switch ($line[1]){
        case self::UNMODIFIED : $element = 'span'; break;
        case self::DELETED    : $element = 'del';  break;
        case self::INSERTED   : $element = 'ins';  break;
      }
      $html .=
          '<' . $element . '>'
          . htmlspecialchars($line[0])
          . '</' . $element . '>';

      $html .= $separator;

    }

    return $html;

  }

  public static function toTable($diff, $indentation = '', $separator = '<br/>'){
    $html = $indentation . "<table class=\"hikadiff\">\n";

    $index = 0;
    while ($index < count($diff)){

      switch ($diff[$index][1]){

        case self::UNMODIFIED:
          $leftCell =
              self::getCellContent(
                  $diff, $indentation, $separator, $index, self::UNMODIFIED);
          $rightCell = $leftCell;
          break;

        case self::DELETED:
          $leftCell =
              self::getCellContent(
                  $diff, $indentation, $separator, $index, self::DELETED);
          $rightCell =
              self::getCellContent(
                  $diff, $indentation, $separator, $index, self::INSERTED);
          break;

        case self::INSERTED:
          $leftCell = '';
          $rightCell =
              self::getCellContent(
                  $diff, $indentation, $separator, $index, self::INSERTED);
          break;


        case 3:
            $leftCell = '<h2>'.$diff[$index][0].'</h2>'. $separator;
            $rightCell = '<h2>'.$diff[$index][2]. '</h2>'.$separator;
            $index++;
            break;

      }

      $html .=
          $indentation
          . "  <tr>\n"
          . $indentation
          . '    <td class="hikadiff'
          . ($leftCell == $rightCell
              ? 'Unmodified'
              : ($leftCell == '' ? 'Blank' : 'Deleted'))
          . '">'
          . $leftCell
          . "</td>\n"
          . $indentation
          . '    <td class="hikadiff'
          . ($leftCell == $rightCell
              ? 'Unmodified'
              : ($rightCell == '' ? 'Blank' : 'Inserted'))
          . '">'
          . $rightCell
          . "</td>\n"
          . $indentation
          . "  </tr>\n";

    }

    return $html . $indentation . "</table>\n";

  }

  private static function getCellContent(
      $diff, $indentation, $separator, &$index, $type){

    $html = '';

    while ($index < count($diff) && $diff[$index][1] == $type){
      $html .=
          '<span>'
          . htmlspecialchars($diff[$index][0])
          . '</span>';
      $index ++;
    }

    return $html;

  }

}
