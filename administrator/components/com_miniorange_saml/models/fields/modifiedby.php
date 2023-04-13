<?php


defined("\112\x50\101\x54\110\137\102\101\x53\x45") or die;
jimport("\x6a\x6f\157\x6d\x6c\x61\x2e\x66\157\162\x6d\56\x66\157\162\155\x66\151\145\154\144");
class JFormFieldModifiedby extends JFormField
{
    protected $type = "\x6d\x6f\144\x69\x66\151\145\144\x62\171";
    protected function getInput()
    {
        $wL = array();
        $user = JFactory::getUser();
        $wL[] = "\74\151\x6e\x70\165\x74\x20\164\171\x70\x65\75\42\x68\151\x64\144\145\x6e\42\40\x6e\141\x6d\145\75\x22" . $this->name . "\42\40\x76\141\x6c\x75\x65\x3d\x22" . $user->id . "\x22\x20\57\x3e";
        $wL[] = "\x3c\144\x69\x76\76" . $user->name . "\40\50" . $user->username . "\51\x3c\x2f\144\x69\x76\76";
        return implode($wL);
    }
}
