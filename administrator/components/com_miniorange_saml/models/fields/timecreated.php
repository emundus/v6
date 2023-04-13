<?php


defined("\x4a\x50\x41\124\110\137\x42\x41\x53\x45") or die;
jimport("\x6a\157\x6f\155\154\141\56\x66\x6f\162\x6d\x2e\x66\x6f\162\x6d\146\151\x65\x6c\144");
class JFormFieldTimecreated extends JFormField
{
    protected $type = "\x74\x69\x6d\x65\143\162\x65\141\x74\145\x64";
    protected function getInput()
    {
        $wL = array();
        $Wk = $this->value;
        if (strtotime($Wk)) {
            goto eE;
        }
        $Wk = JFactory::getDate("\156\x6f\x77", JFactory::getConfig()->get("\x6f\146\x66\163\x65\x74"))->toSql(true);
        $wL[] = "\x3c\151\x6e\x70\165\x74\x20\164\171\160\x65\75\x22\150\151\x64\144\x65\156\x22\40\x6e\x61\x6d\x65\x3d\x22" . $this->name . "\x22\x20\x76\141\x6c\165\145\x3d\42" . $Wk . "\42\40\57\76";
        eE:
        $E2 = (bool) $this->element["\x68\x69\144\x64\x65\156"];
        if (!($E2 == null || !$E2)) {
            goto V3;
        }
        $Vd = new JDate($Wk);
        $Y2 = $Vd->format(JText::_("\x44\101\124\105\137\x46\117\122\115\101\x54\x5f\114\103\x32"));
        $wL[] = "\x3c\144\x69\166\x3e" . $Y2 . "\74\57\x64\151\166\76";
        V3:
        return implode($wL);
    }
}
