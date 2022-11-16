<?php


defined("\112\x50\101\124\110\137\102\101\x53\105") or die;
jimport("\x6a\157\x6f\x6d\154\141\x2e\x66\x6f\x72\x6d\x2e\x66\157\162\x6d\x66\151\145\x6c\x64");
class JFormFieldModifiedby extends JFormField
{
    protected $type = "\155\x6f\x64\151\x66\x69\145\144\x62\x79";
    protected function getInput()
    {
        $dW = array();
        $user = JFactory::getUser();
        $dW[] = "\74\x69\156\x70\x75\164\40\164\171\x70\145\x3d\x22\x68\x69\x64\x64\x65\156\42\x20\x6e\x61\155\x65\x3d\x22" . $this->name . "\x22\x20\166\x61\x6c\165\x65\x3d\42" . $user->id . "\42\x20\x2f\x3e";
        $dW[] = "\x3c\x64\151\166\76" . $user->name . "\x20\50" . $user->username . "\51\74\57\x64\x69\x76\x3e";
        return implode($dW);
    }
}
