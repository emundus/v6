<?php


defined("\x4a\120\x41\x54\x48\x5f\x42\101\x53\105") or die;
jimport("\152\x6f\157\x6d\x6c\x61\x2e\x66\x6f\x72\x6d\56\146\157\162\155\x66\x69\x65\x6c\144");
class JFormFieldFileMultiple extends JFormField
{
    protected $type = "\x66\151\x6c\x65";
    protected function getInput()
    {
        $dW = "\74\x69\156\x70\x75\164\x20\164\171\160\145\x3d\x22\146\151\x6c\145\42\x20\x6e\141\x6d\x65\x3d\x22" . $this->name . "\133\x5d\x22\40\155\165\x6c\164\151\x70\154\145\40\x3e";
        return $dW;
    }
}
