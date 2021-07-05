<?php

/**
 * @package    Joomla.Cli
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * A command line cron job to trash expired cache data.
 */

// Initialize Joomla framework
define('_JEXEC', 1); 
define('JPATH_BASE', dirname(__DIR__) . '/../../');
require_once ( JPATH_BASE . 'includes/defines.php' );
require_once ( JPATH_BASE . 'includes/framework.php' );

// Load emundus dependencies
require_once ( JPATH_BASE . 'libraries/emundus/vendor/autoload.php' );

// Load age()
require_once ( JPATH_BASE . 'libraries/emundus/pdf_communs.php' );

// Load Testcase() in PHPUnit
use PHPUnit\Framework\TestCase;

// UT for age() function
class ageTest extends TestCase {
  public function testage1() {
          $myage = '1996-06-07';
          $this->assertSame(25, age($myage));
  }
  public function testage2() {
          $myage = '1996-13-33';
          $this->assertSame(24, age($myage));
  }
  public function testage3() {
          $myage = '07-06-1996';
          $this->assertSame(25, age($myage));
  }
  public function testage4() {
          $myage = '15 juin 1996';
          $this->assertSame(25, age($myage));
  }
  public function testage5() {
        $myage = 'juin 06 1996';
        $this->assertSame(25, age($myage));
  }
}