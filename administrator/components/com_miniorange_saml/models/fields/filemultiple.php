<?php


defined("\x4a\120\101\124\x48\x5f\102\101\123\x45") or die;
jimport("\x6a\x6f\x6f\155\x6c\x61\56\146\x6f\162\x6d\x2e\x66\157\162\155\x66\x69\145\x6c\144");
class JFormFieldFileMultiple extends JFormField
{
    protected $type = "\146\151\154\x65";
    protected function getInput()
    {
        $dW = "\74\x69\156\160\x75\x74\40\164\171\x70\x65\75\42\x66\x69\x6c\x65\42\x20\156\141\x6d\145\x3d\x22" . $this->name . "\x5b\135\x22\40\x6d\165\154\x74\151\x70\x6c\x65\76";
        return $dW;
    }
}
