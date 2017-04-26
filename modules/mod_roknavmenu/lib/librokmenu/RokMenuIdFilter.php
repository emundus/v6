<?php
/**
 * @version   $Id: RokMenuIdFilter.php 4585 2012-10-27 01:44:54Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokMenuIdFilter extends RecursiveFilterIterator {
    protected $id;

    public function __construct(RecursiveIterator $recursiveIter, $id) {
        $this->id = $id;
        parent::__construct($recursiveIter);
    }
    public function accept() {
        return $this->hasChildren() || $this->current()->getId() == $this->id;
    }

    public function getChildren() {
        return new self($this->getInnerIterator()->getChildren(), $this->id);
    }
}