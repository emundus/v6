<?php


defined("\112\120\101\124\110\x5f\102\101\123\x45") or die;
jimport("\152\x6f\157\155\x6c\x61\x2e\x66\157\162\x6d\56\146\157\x72\x6d\x66\151\x65\154\144");
class JFormFieldCreatedby extends JFormField
{
    protected $type = "\143\x72\145\141\164\x65\x64\x62\171";
    protected function getInput()
    {
        $wL = array();
        $NJ = $this->value;
        if ($NJ) {
            goto D2;
        }
        $user = JFactory::getUser();
        $wL[] = "\x3c\x69\x6e\160\165\x74\40\x74\x79\x70\145\x3d\42\150\151\x64\x64\145\x6e\42\x20\156\141\155\145\75\42" . $this->name . "\x22\x20\x76\x61\x6c\x75\145\75\42" . $user->id . "\x22\40\57\x3e";
        goto Ah;
        D2:
        $user = JFactory::getUser($NJ);
        Ah:
        $wL[] = "\74\x64\151\166\x3e" . $user->name . "\40\x28" . $user->username . "\x29\x3c\x2f\x64\x69\166\76";
        return implode($wL);
    }
}
