<?php


defined("\x4a\x50\101\x54\x48\137\102\101\123\x45") or die;
jimport("\x6a\x6f\x6f\x6d\154\141\56\x66\x6f\x72\x6d\56\x66\157\162\x6d\x66\151\145\154\x64");
class JFormFieldFileMultiple extends JFormField
{
    protected $type = "\x66\x69\154\145";
    protected function getInput()
    {
        $wL = "\x3c\151\156\x70\165\164\40\164\171\x70\x65\75\x22\x66\151\x6c\x65\x22\x20\156\141\155\x65\x3d\42" . $this->name . "\x5b\135\42\40\155\165\x6c\164\151\160\x6c\x65\x3e";
        return $wL;
    }
}
