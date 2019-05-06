# PhpSpreadsheet Library for Joomla!

**PhpSpreadsheet Library for Joomla! 3.x**

If you already know how to use PhpSpreadsheet and you need it for a Joomla! project, than this is a library package to be used in Joomla! Easy to install and update.

Download
--------

You can download the current version / older version of PhpSpreadsheet-Joomla-Library using the [PhpSpreadsheet-Joomla-Library download page](https://github.com/ivanramosnet/PhpSpreadsheet-Joomla-Library/releases).

Version
-------

* The current Joomla! Library is using PhpSpreadsheet 1.6.0.

Usage
-----

    // Import PhpSpreadsheet library
    jimport('phpspreadsheet.phpspreadsheet');
    use PhpOffice\PhpSpreadsheet\Spreadsheet;

or 

    require_once JPATH_LIBRARIES . '/phpspreadsheet/phpspreadsheet.php';
    use PhpOffice\PhpSpreadsheet\Spreadsheet;


Now you can create a new Excel document:

    $spreadsheet = new Spreadsheet();


Documentation
-------------

* [PhpSpreadsheet Read the Docs page](https://phpspreadsheet.readthedocs.io)
* [PhpSpreadsheet github page](https://github.com/PHPOffice/PhpSpreadsheet)

Bugs? Problems? Feedback?
-------------------------

If you have any problems installing / updating PhpSpreadsheet Library in Joomla! feel free to [Add a New issue](https://github.com/ivanramosnet/PhpSpreadsheet-Joomla-Library/issues)

If you are having problems with PhpSpreadsheet itself and you think is a bug or something, check the [PhpSpreadsheet Issue Tracker](https://github.com/PHPOffice/PhpSpreadsheet/issues)

Credits
-------

* Special thanks to the [PHPOffice team](https://github.com/orgs/PHPOffice/people) which put the PhpSpreadsheet library together.
* Thanks to Valentin Despa for [PHPExcel Library for Joomla!](https://github.com/vdespa/PHPExcel-Joomla-Library) in which it is based this repository.
* Also thanks to Joe for the [inspiration](http://www.ostraining.com/howtojoomla/how-tos/development/how-to-package-joomla-libraries).


License
-------
PhpSpreadsheet is licensed under [LGPL (GNU LESSER GENERAL PUBLIC LICENSE)](https://github.com/PHPOffice/PhpSpreadsheet/blob/master/license.md)
