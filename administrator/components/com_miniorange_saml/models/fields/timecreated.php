<?php


defined("\x4a\120\x41\x54\110\137\x42\x41\123\105") or die;
jimport("\152\x6f\x6f\x6d\x6c\141\x2e\x66\157\162\x6d\56\146\x6f\x72\155\x66\151\145\154\x64");
class JFormFieldTimecreated extends JFormField
{
    protected $type = "\x74\x69\x6d\x65\143\x72\x65\x61\x74\145\x64";
    protected function getInput()
    {
        $dW = array();
        $jn = $this->value;
        if (strtotime($jn)) {
            goto Iu;
        }
        $jn = JFactory::getDate("\156\157\x77", JFactory::getConfig()->get("\x6f\146\x66\x73\x65\164"))->toSql(true);
        $dW[] = "\x3c\151\x6e\160\x75\x74\x20\x74\171\x70\145\75\x22\150\x69\x64\144\145\x6e\42\x20\156\x61\x6d\145\x3d\42" . $this->name . "\42\40\166\141\154\x75\x65\75\x22" . $jn . "\42\40\57\x3e";
        Iu:
        $xm = (bool) $this->element["\x68\x69\x64\x64\145\x6e"];
        if (!($xm == null || !$xm)) {
            goto Ke;
        }
        $wJ = new JDate($jn);
        $AE = $wJ->format(JText::_("\104\x41\124\x45\137\106\x4f\122\x4d\101\124\137\x4c\x43\x32"));
        $dW[] = "\74\144\151\x76\76" . $AE . "\74\x2f\144\151\166\x3e";
        Ke:
        return implode($dW);
    }
}
