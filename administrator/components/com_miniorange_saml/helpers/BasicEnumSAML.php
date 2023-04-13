<?php


defined("\137\112\x45\130\x45\x43") or die;
abstract class BasicEnumSAML
{
    private static $constCacheArray = NULL;
    public static function getConstants()
    {
        if (!(self::$constCacheArray == NULL)) {
            goto Mi;
        }
        self::$constCacheArray = array();
        Mi:
        $E3 = get_called_class();
        if (array_key_exists($E3, self::$constCacheArray)) {
            goto qI;
        }
        $B2 = new ReflectionClass($E3);
        self::$constCacheArray[$E3] = $B2->getConstants();
        qI:
        return self::$constCacheArray[$E3];
    }
    public static function isValidName($F6, $JQ = false)
    {
        $nW = self::getConstants();
        if (!$JQ) {
            goto ZN;
        }
        return array_key_exists($F6, $nW);
        ZN:
        $Hk = array_map("\163\x74\162\x74\157\154\x6f\x77\x65\162", array_keys($nW));
        return in_array(strtolower($F6), $Hk);
    }
    public static function isValidValue($Gt, $JQ = true)
    {
        $jl = array_values(self::getConstants());
        return in_array($Gt, $jl, $JQ);
    }
}
