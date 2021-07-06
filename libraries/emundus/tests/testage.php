<?php

/**
 * @package    Joomla.Cli
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
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

// Load Testcase class in PHPUnit
use PHPUnit\Framework\TestCase;

// Unit Tests for age() function
class ageTest extends TestCase {
  public function testage1() {
          // Good value, the function should return 25
          $myage = '1996-01-01';
          $year = date("Y");
          $this->assertSame($year-1996, age($myage));
  }
  public function testage2() {
          // Wrong value, the date doesn't exist (an exception should be present in the function is tested in this unit test)
          $myage = '1996-00-00';
          $year = date("Y");
          $this->assertSame($year-1996, age($myage));
  }
  public function testage3() {
          // The format is wrong (xxxx-xx-xx expected)
          $myage = '01-01-1996';
          $year = date("Y");
          $this->assertSame($year-1996, age($myage));
  }
  public function testage4() {
          // The format is wrong (xxxx-xx-xx expected)
          $myage = '01 janvier 1996';
          $year = date("Y");
          $this->assertSame($year-1996, age($myage));
  }
  public function testage5() {
        // The format is wrong (xxxx-xx-xx expected)
        $myage = 'janvier 01 1996';
        $year = date("Y");
        $this->assertSame($year-1996, age($myage));
  }
}

/* Result when running 5 unit tests on the age() function

exec in project_path :
  > libraries/emundus/vendor/bin/phpunit libraries/emundus/tests/testage.php 

PHPUnit 4.8.36 by Sebastian Bergmann and contributors.

..FFF

Time: 124 ms, Memory: 8.00MB

There were 3 failures:

1) ageTest::testage3
Failed asserting that 2020 is identical to 25.

/home/debian/preprod/c69_i677_wmaillet.tchooz.us/libraries/emundus/tests/testage.php:43

2) ageTest::testage4
Failed asserting that 2020 is identical to 25.

/home/debian/preprod/c69_i677_wmaillet.tchooz.us/libraries/emundus/tests/testage.php:49

3) ageTest::testage5
Failed asserting that 2021 is identical to 25.

/home/debian/preprod/c69_i677_wmaillet.tchooz.us/libraries/emundus/tests/testage.php:55

FAILURES!
Tests: 5, Assertions: 5, Failures: 3.


  What is it?

The PHPUnit execution report shows two ok tests represented by a dot each and 4 failed tests represented by the character F. Why?

The function input data that a user could provide to the application is incorrect for the 2,3,4,5 tests. 

This implies displaying incorrect information to the user because the code does not protect itself sufficiently. It could also involve storing incorrect information that could be used later by another feature in the application that works with incorrect data. 

To avoid this, every call to the age() function from the test cases (test 2,3,4,5) here should return a PHP exception. The presence of this exception could then be tested from a unit test.

These tests show for example that we should add to the age() function several exceptions:
- if $naiss does not respect the format xxxx-xx-xx (year-month-day)
- if $year is not between 1901 and the current year
- if $month is not between 01 and 12 and $date is not between 01 and 31 
- if $naiss is a possible date of birth in the calendar (see TU number 2).

Then, we have to check that in the unit tests [2-5], we return the exception set up in these cases in the age() function.

Once this is done, these 5 unit tests of this function will be successful and will guarantee the behavior of this function in these cases.

Thinking about this type of test before designing a new function may help you later on in the design of your function.

You are our supermen, our heroes, the unit tests are one of the elements that will allow us to increase progressively and step by step the quality of our application and to give you more serenity on the stability of the code.

Thanks to you,

*/