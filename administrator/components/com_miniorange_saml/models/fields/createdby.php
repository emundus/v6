<?php


defined("\112\120\101\124\110\137\102\x41\x53\x45") or die;
jimport("\152\157\157\x6d\x6c\x61\x2e\x66\157\162\x6d\x2e\146\157\162\x6d\x66\x69\145\154\144");
class JFormFieldCreatedby extends JFormField
{
    protected $type = "\x63\x72\145\x61\x74\x65\144\142\x79";
    protected function getInput()
    {
        $dW = array();
        $K4 = $this->value;
        if ($K4) {
            goto Sg;
        }
        $user = JFactory::getUser();
        $dW[] = "\x3c\x69\x6e\x70\165\164\x20\x74\x79\x70\145\x3d\x22\150\x69\x64\x64\145\156\42\x20\156\141\x6d\x65\x3d\42" . $this->name . "\42\40\x76\141\x6c\x75\145\x3d\x22" . $user->id . "\x22\40\x2f\x3e";
        goto Rq;
        Sg:
        $user = JFactory::getUser($K4);
        Rq:
        $dW[] = "\x3c\x64\x69\166\x3e" . $user->name . "\40\50" . $user->username . "\51\x3c\57\x64\x69\166\x3e";
        return implode($dW);
    }
}
