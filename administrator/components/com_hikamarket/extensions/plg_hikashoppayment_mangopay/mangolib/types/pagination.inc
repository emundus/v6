<?php

namespace MangoPay;

/**
 * Class represents pagination information
 */
class Pagination extends Dto {
    
    /**
     * Page number
     * @var Int
     */
    public $Page;
    
    /**
     * Number of items per page
     * @var Int
     */
    public $ItemsPerPage;
    
    /**
     * Number of total pages
     * @var Int
     */
    public $TotalPages;
    
    /**
     * Number of total items
     * @var Int
     */
    public $TotalItems;
    
    /**
     * Array with links to navigation. 
     * All values optional. Format:
     * array(
     *      first => http url
     *      prev => http url
     *      next => http url
     *      last => http url
     * )
     * @var array 
     */
    public $Links = array();
    
    /**
     * Construct
     * @param int $page Number of page
     * @param int $itemsPerPage Number of items on one page
     */
    function __construct($page = 1, $itemsPerPage = 10) {
        $this->Page = $page;
        $this->ItemsPerPage = $itemsPerPage;
    }
}