<?php


defined("\x4a\120\101\124\x48\x5f\x42\101\123\105") or die;
jimport("\x6a\157\x6f\155\154\141\56\146\x6f\x72\x6d\x2e\146\157\x72\x6d\146\x69\145\154\x64");
class JFormFieldCreatedby extends JFormField
{
    protected $type = "\143\162\145\x61\x74\x65\x64\x62\x79";
    protected function getInput()
    {
        $dW = array();
        $K4 = $this->value;
        if ($K4) {
            goto za;
        }
        $user = JFactory::getUser();
        $dW[] = "\74\151\x6e\x70\165\x74\40\164\171\x70\x65\x3d\x22\150\151\144\144\x65\156\x22\40\156\x61\155\x65\x3d\x22" . $this->name . "\42\40\x76\141\154\165\x65\x3d\42" . $user->id . "\42\40\57\x3e";
        goto xC;
        za:
        $user = JFactory::getUser($K4);
        xC:
        $dW[] = "\x3c\144\x69\166\76" . $user->name . "\40\x28" . $user->username . "\51\x3c\x2f\144\x69\x76\x3e";
        return implode($dW);
    }
}
