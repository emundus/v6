<?php
/**
* @version   $Id$
* @package   Jumi
* @copyright (C) 2008 - 2010 Martin Hajek, 2011 Edvard Ananyan
* @license   GNU/GPL v3 http://www.gnu.org/licenses/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
?>
<p><strong>Hello in the world of Jumi!</strong></p>
<p>This is the default Jumi demo file.</p>
<p>Your current content of $jumi[] array is:</p>
<?php
   if (!empty($jumi)){
      echo "<ul>\n";
      foreach ($jumi as $key => $value) {
      echo '<li>$jumi['.$key.'] = '.$value.'</li>';
    }
    echo "</ul>\n";
    }
    else {
        echo "<p>empty</p>";
    }
?>
<p>Jumi is a set of custom code extensions for CMS Joomla! Jumi comes as a component, a plugin and a module.</p>
<p>Jumi brings you sufficient power, flexibility and simplicity for quick development and reliable operation of your applications running under Joomla!</p>
<p>Jumi resources: <a href="http://edo.webmaster.am/jumi">Downloads & guides</a>, <a href="http://edo.webmaster.am/jumi/tutorial">Tips & tricks</a>.</p>