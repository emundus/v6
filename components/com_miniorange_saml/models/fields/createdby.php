<?php


defined("\x4a\x50\101\124\110\x5f\102\101\x53\105") or die;
jimport("\152\x6f\x6f\x6d\x6c\141\56\x66\x6f\x72\x6d\56\x66\x6f\162\155\146\x69\145\154\x64");
class JFormFieldCreatedby extends JFormField
{
    protected $type = "\x63\162\145\x61\164\145\144\142\171";
    protected function getInput()
    {
        $wL = array();
        $NJ = $this->value;
        if ($NJ) {
            goto zI;
        }
        $user = JFactory::getUser();
        $wL[] = "\x3c\151\x6e\x70\x75\x74\x20\x74\x79\x70\x65\75\x22\150\x69\x64\x64\x65\156\42\40\x6e\141\x6d\x65\75\42" . $this->name . "\x22\x20\166\x61\x6c\165\145\x3d\x22" . $user->id . "\42\40\57\x3e";
        goto qy;
        zI:
        $user = JFactory::getUser($NJ);
        qy:
        $wL[] = "\x3c\144\151\166\76" . $user->name . "\40\x28" . $user->username . "\51\74\57\144\x69\x76\76";
        return implode($wL);
    }
}
