<?php
/**
 * Created by PhpStorm.
 * User: Stéphane
 * Date: 25/02/2015
 * Time: 10:52
 */


class JFalangMenu extends JMenu{

    public static function resetMenu(){
        self::$instances = null;
    }
}