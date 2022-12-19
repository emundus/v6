<?php


defined("\x5f\112\x45\x58\x45\103") or die("\122\145\163\164\162\x69\143\x74\x65\144\40\x61\143\143\x65\163\x73");
jimport("\x6a\x6f\x6f\155\x6c\141\x2e\x66\x6f\162\x6d\56\146\x6f\x72\155\x66\x69\145\x6c\144");
class JFormFieldSubmit extends JFormField
{
    protected $type = "\163\165\x62\x6d\x69\164";
    protected $value;
    protected $for;
    public function getInput()
    {
        $this->value = $this->getAttribute("\166\141\154\165\145");
        return "\74\142\165\x74\x74\x6f\x6e\40\151\x64\x3d\42" . $this->id . "\x22" . "\x20\156\141\155\145\75\x22\x73\x75\x62\155\x69\164\x5f" . $this->for . "\42" . "\40\x76\x61\154\x75\145\x3d\42" . $this->value . "\x22" . "\x20\164\151\x74\x6c\x65\x3d\x22" . JText::_("\112\x53\x45\101\x52\x43\110\x5f\106\x49\x4c\124\105\x52\x5f\x53\x55\x42\x4d\x49\x54") . "\42" . "\x20\x63\154\x61\x73\x73\75\x22\x62\164\x6e\42\40\x73\x74\171\154\x65\75\x22\x6d\141\x72\x67\151\156\55\164\157\160\72\40\x2d\61\x30\x70\x78\x3b\42\x3e" . JText::_("\112\123\x45\101\122\x43\x48\137\x46\111\x4c\x54\105\122\137\123\125\x42\115\111\x54") . "\x20\74\57\x62\165\164\164\x6f\x6e\x3e";
    }
}
