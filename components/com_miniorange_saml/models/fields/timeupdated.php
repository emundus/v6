<?php


defined("\x4a\x50\101\x54\110\x5f\102\x41\x53\x45") or die;
jimport("\152\x6f\x6f\x6d\154\x61\x2e\x66\x6f\162\x6d\x2e\146\x6f\x72\x6d\x66\x69\145\x6c\144");
class JFormFieldTimeupdated extends JFormField
{
    protected $type = "\x74\151\155\x65\x75\x70\x64\141\164\x65\144";
    protected function getInput()
    {
        $wL = array();
        $cf = $this->value;
        $E2 = (bool) $this->element["\x68\151\x64\x64\x65\x6e"];
        if (!($E2 == null || !$E2)) {
            goto RD;
        }
        if (!strtotime($cf)) {
            goto au;
        }
        $Vd = new JDate($cf);
        $Y2 = $Vd->format(JText::_("\104\x41\x54\105\x5f\x46\117\122\115\x41\x54\137\114\x43\x32"));
        $wL[] = "\x3c\x64\151\166\76" . $Y2 . "\x3c\57\x64\x69\x76\76";
        goto fJ;
        au:
        $wL[] = "\x2d";
        fJ:
        RD:
        $i6 = JFactory::getDate()->toSql();
        $wL[] = "\x3c\x69\156\160\x75\x74\40\x74\171\x70\x65\x3d\42\150\x69\x64\144\x65\156\x22\x20\156\141\155\x65\75\42" . $this->name . "\42\40\166\x61\x6c\x75\145\x3d\42" . $i6 . "\42\x20\57\76";
        return implode($wL);
    }
}
