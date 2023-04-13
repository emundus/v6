<?php


defined("\x4a\120\101\124\110\x5f\102\101\123\x45") or die;
jimport("\x6a\x6f\157\x6d\154\141\56\x66\157\x72\x6d\56\x66\157\x72\x6d\x66\151\145\154\144");
class JFormFieldTimecreated extends JFormField
{
    protected $type = "\x74\151\x6d\x65\x63\x72\x65\x61\164\x65\144";
    protected function getInput()
    {
        $wL = array();
        $Wk = $this->value;
        if (strtotime($Wk)) {
            goto h1;
        }
        $Wk = JFactory::getDate()->toSql();
        $wL[] = "\x3c\151\x6e\x70\165\x74\40\x74\x79\x70\145\75\42\x68\151\144\144\145\x6e\x22\40\156\141\x6d\145\x3d\42" . $this->name . "\x22\40\x76\x61\x6c\x75\x65\75\x22" . $Wk . "\x22\x20\x2f\x3e";
        h1:
        $E2 = (bool) $this->element["\150\151\144\144\145\156"];
        if (!($E2 == null || !$E2)) {
            goto fd;
        }
        $Vd = new JDate($Wk);
        $Y2 = $Vd->format(JText::_("\x44\101\x54\105\137\x46\x4f\x52\x4d\x41\124\x5f\x4c\103\x32"));
        $wL[] = "\x3c\144\x69\x76\x3e" . $Y2 . "\x3c\x2f\144\x69\166\76";
        fd:
        return implode($wL);
    }
}
