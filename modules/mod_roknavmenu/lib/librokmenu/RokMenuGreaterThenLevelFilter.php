<?php
/**
 * @version   $Id: RokMenuGreaterThenLevelFilter.php 4585 2012-10-27 01:44:54Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokMenuGreaterThenLevelFilter  extends RecursiveFilterIterator  {
   protected $level;

    public function __construct(RecursiveIterator $recursiveIter, $end) {
        $this->level = $end;
        parent::__construct($recursiveIter);
    }
    public function accept() {
        return $this->hasChildren() || $this->current()->getLevel() > $this->level;
    }

    public function getChildren() {
        return new self($this->getInnerIterator()->getChildren(), $this->level);
    }
}
