<?php
namespace MangoPay;

/**
 * Class to management MangoPay API for cards
 */
class ApiEvents extends ApiBase {
    
    /**
     * Get events
     * @param \MangoPay\Pagination $pagination Pagination object
     * @param \MangoPay\FilterEvents $filter Object to filter data
     * @return \MangoPay\Event[] Events list
     */
    public function GetAll(& $pagination = null, $filter = null) {
        return $this->GetList('events_all', $pagination, '\MangoPay\Event', null, $filter);
    }
}
