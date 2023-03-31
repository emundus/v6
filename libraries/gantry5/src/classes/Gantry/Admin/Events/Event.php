<?php

/**
 * @package   Gantry5
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2022 RocketTheme, LLC
 * @license   Dual License: MIT or GNU/GPLv2 and later
 *
 * http://opensource.org/licenses/MIT
 * http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Gantry Framework code that extends GPL code is considered GNU/GPLv2 and later
 */

namespace Gantry\Admin\Events;

use Gantry\Component\Controller\RestfulControllerInterface;
use Gantry\Framework\Gantry;

/**
 * Class AssigmentsEvent
 * @package Gantry\Admin\Events
 */
class Event extends \RocketTheme\Toolbox\Event\Event
{
    /** @var Gantry */
    public $gantry;
    /** @var RestfulControllerInterface */
    public $controller;
    /** @var array */
    public $data;
}
