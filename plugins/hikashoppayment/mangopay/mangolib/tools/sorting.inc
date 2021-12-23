<?php
namespace MangoPay;

 /**
 * Base sorting object
 */
class Sorting {
    
    /**
     * Fields separator in sort parameters in URL
     */
    const SortFieldSeparator = "_";
    
    /**
     * Fields separator in sort parameters in URL
     */
    const SortUrlParameterName = "Sort";
    
    /**
     * Array with fileds to sort
     * @var type Array
     */
    private $_sortFields;
    
    /**
     * Add filed to sort
     * @param string $filedName Property name to sort
     * @param \MangoPay\SortDirection $sortDirection Sort direction
     */
    public function AddFiled($filedName, $sortDirection) {
        $this->_sortFields[$filedName] = $sortDirection;
    }
    
    /**
     * Get sort parametrs to URL
     * @return array 
     */
    public function GetSortParameter() {
        return array(self::SortUrlParameterName => $this->_getFields());
    }
    
    private function _getFields() {
        $sortValues = "";
        foreach ($this->_sortFields as $key => $value) {
            if (!empty($sortValues))
                $sortValues .= self::SortFieldSeparator;
     
            $sortValues .= $key . ":" . $value;
        }
        
        return $sortValues;
    }
}