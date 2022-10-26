<?php


defined("\x4a\x50\101\x54\x48\137\102\x41\x53\105") or die;
jimport("\152\x6f\157\155\x6c\141\56\x66\x6f\x72\155\56\146\157\x72\x6d\x66\151\x65\154\x64");
class JFormFieldTimeupdated extends JFormField
{
    protected $type = "\x74\x69\155\145\165\160\144\x61\164\145\x64";
    protected function getInput()
    {
        $dW = array();
        $Km = $this->value;
        $xm = (bool) $this->element["\150\x69\144\x64\x65\156"];
        if (!($xm == null || !$xm)) {
            goto cd;
        }
        if (!strtotime($Km)) {
            goto pK;
        }
        $wJ = new JDate($Km);
        $AE = $wJ->format(JText::_("\x44\x41\124\x45\x5f\106\117\122\x4d\101\124\x5f\114\103\x32"));
        $dW[] = "\x3c\144\151\x76\x3e" . $AE . "\74\x2f\x64\x69\x76\x3e";
        goto fu;
        pK:
        $dW[] = "\55";
        fu:
        cd:
        $jz = JFactory::getDate("\x6e\x6f\x77", JFactory::getConfig()->get("\x6f\x66\x66\163\145\164"))->toSql(true);
        $dW[] = "\74\x69\156\160\165\164\x20\164\171\x70\145\x3d\42\x68\x69\x64\x64\x65\156\42\x20\156\x61\155\145\x3d\x22" . $this->name . "\42\x20\166\141\154\x75\145\x3d\x22" . $jz . "\x22\x20\x2f\x3e";
        return implode($dW);
    }
}
