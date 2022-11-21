<?php


defined("\112\120\x41\x54\x48\x5f\102\101\123\105") or die;
jimport("\152\x6f\157\x6d\x6c\141\56\x66\157\162\155\x2e\146\157\162\x6d\146\x69\x65\x6c\x64");
class JFormFieldModifiedby extends JFormField
{
    protected $type = "\155\157\x64\x69\x66\x69\x65\x64\142\x79";
    protected function getInput()
    {
        $dW = array();
        $user = JFactory::getUser();
        $dW[] = "\74\151\x6e\160\165\164\x20\x74\x79\x70\145\75\42\x68\151\144\144\145\x6e\x22\x20\156\x61\155\145\x3d\42" . $this->name . "\x22\40\166\x61\x6c\x75\145\x3d\x22" . $user->id . "\42\x20\x2f\x3e";
        $dW[] = "\x3c\x64\x69\x76\x3e" . $user->name . "\x20\50" . $user->username . "\x29\x3c\57\144\x69\x76\x3e";
        return implode($dW);
    }
}
