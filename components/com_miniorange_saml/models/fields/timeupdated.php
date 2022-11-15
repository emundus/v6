<?php


defined("\112\x50\x41\124\110\x5f\102\101\x53\105") or die;
jimport("\x6a\157\157\155\x6c\141\56\x66\157\162\x6d\x2e\146\157\x72\155\146\151\145\154\x64");
class JFormFieldTimeupdated extends JFormField
{
    protected $type = "\x74\x69\x6d\x65\x75\160\x64\x61\x74\145\144";
    protected function getInput()
    {
        $dW = array();
        $Km = $this->value;
        $xm = (bool) $this->element["\150\151\144\144\x65\156"];
        if (!($xm == null || !$xm)) {
            goto yu;
        }
        if (!strtotime($Km)) {
            goto By;
        }
        $wJ = new JDate($Km);
        $AE = $wJ->format(JText::_("\x44\101\124\x45\137\106\x4f\x52\x4d\x41\x54\137\114\103\62"));
        $dW[] = "\74\144\151\166\x3e" . $AE . "\74\x2f\x64\x69\166\76";
        goto lJ;
        By:
        $dW[] = "\x2d";
        lJ:
        yu:
        $jz = JFactory::getDate()->toSql();
        $dW[] = "\x3c\x69\156\160\165\x74\x20\x74\171\160\x65\x3d\42\150\151\144\x64\x65\x6e\42\40\156\141\x6d\x65\x3d\42" . $this->name . "\42\x20\x76\141\154\165\145\75\42" . $jz . "\42\x20\x2f\x3e";
        return implode($dW);
    }
}
