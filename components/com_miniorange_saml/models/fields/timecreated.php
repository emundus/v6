<?php


defined("\112\120\101\124\110\x5f\x42\x41\123\x45") or die;
jimport("\x6a\157\157\155\x6c\141\56\x66\x6f\162\x6d\56\146\157\x72\x6d\x66\151\x65\x6c\144");
class JFormFieldTimecreated extends JFormField
{
    protected $type = "\x74\x69\155\145\x63\x72\x65\141\164\x65\144";
    protected function getInput()
    {
        $dW = array();
        $jn = $this->value;
        if (strtotime($jn)) {
            goto uX;
        }
        $jn = JFactory::getDate()->toSql();
        $dW[] = "\74\151\x6e\160\x75\x74\40\x74\171\x70\x65\x3d\42\x68\151\144\144\x65\156\42\x20\x6e\141\x6d\145\75\42" . $this->name . "\x22\x20\166\141\154\x75\145\x3d\x22" . $jn . "\x22\x20\57\76";
        uX:
        $xm = (bool) $this->element["\x68\x69\x64\144\145\156"];
        if (!($xm == null || !$xm)) {
            goto NA;
        }
        $wJ = new JDate($jn);
        $AE = $wJ->format(JText::_("\104\101\124\105\x5f\x46\x4f\x52\x4d\x41\124\x5f\x4c\103\x32"));
        $dW[] = "\x3c\x64\151\x76\76" . $AE . "\74\x2f\x64\x69\166\x3e";
        NA:
        return implode($dW);
    }
}
