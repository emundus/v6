<?php


defined("\137\x4a\105\130\x45\103") or die;
abstract class BasicEnumSAML
{
    private static $constCacheArray = NULL;
    public static function getConstants()
    {
        if (!(self::$constCacheArray == NULL)) {
            goto ps;
        }
        self::$constCacheArray = array();
        ps:
        $eO = get_called_class();
        if (array_key_exists($eO, self::$constCacheArray)) {
            goto lk;
        }
        $Q4 = new ReflectionClass($eO);
        self::$constCacheArray[$eO] = $Q4->getConstants();
        lk:
        return self::$constCacheArray[$eO];
    }
    public static function isValidName($UC, $pw = false)
    {
        $Lu = self::getConstants();
        if (!$pw) {
            goto lz;
        }
        return array_key_exists($UC, $Lu);
        lz:
        $zY = array_map("\163\x74\162\x74\x6f\154\x6f\x77\x65\162", array_keys($Lu));
        return in_array(strtolower($UC), $zY);
    }
    public static function isValidValue($Uf, $pw = true)
    {
        $oz = array_values(self::getConstants());
        return in_array($Uf, $oz, $pw);
    }
}
