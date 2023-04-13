<?php


defined("\x4a\x50\101\x54\110\x5f\102\101\123\x45") or die;
jimport("\152\157\x6f\x6d\154\141\x2e\x66\x6f\162\155\x2e\146\x6f\162\x6d\x66\151\x65\x6c\x64");
class JFormFieldFileMultiple extends JFormField
{
    protected $type = "\146\151\154\145";
    protected function getInput()
    {
        $wL = "\74\x69\x6e\160\x75\164\x20\164\171\160\145\75\42\x66\x69\154\x65\42\40\156\x61\x6d\x65\75\42" . $this->name . "\133\135\x22\40\155\x75\154\164\151\160\x6c\145\40\x3e";
        return $wL;
    }
}
