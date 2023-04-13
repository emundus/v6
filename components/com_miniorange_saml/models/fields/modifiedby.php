<?php


defined("\112\120\101\x54\110\x5f\x42\x41\x53\105") or die;
jimport("\x6a\x6f\157\x6d\154\x61\56\x66\157\162\x6d\56\146\x6f\x72\x6d\x66\151\145\154\x64");
class JFormFieldModifiedby extends JFormField
{
    protected $type = "\x6d\157\x64\151\146\x69\x65\144\142\171";
    protected function getInput()
    {
        $wL = array();
        $user = JFactory::getUser();
        $wL[] = "\74\151\156\x70\x75\x74\40\x74\x79\x70\x65\x3d\42\x68\x69\144\144\145\x6e\x22\x20\x6e\x61\x6d\145\75\42" . $this->name . "\x22\40\x76\x61\x6c\165\x65\75\x22" . $user->id . "\42\x20\x2f\76";
        $wL[] = "\74\x64\x69\166\x3e" . $user->name . "\x20\50" . $user->username . "\51\x3c\x2f\x64\151\166\76";
        return implode($wL);
    }
}
