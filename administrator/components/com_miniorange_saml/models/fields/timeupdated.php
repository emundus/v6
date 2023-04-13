<?php


defined("\x4a\x50\101\124\x48\137\x42\x41\x53\x45") or die;
jimport("\152\x6f\x6f\x6d\154\141\x2e\146\157\162\155\x2e\x66\157\x72\x6d\146\x69\145\154\144");
class JFormFieldTimeupdated extends JFormField
{
    protected $type = "\164\151\155\x65\x75\160\144\141\164\145\144";
    protected function getInput()
    {
        $wL = array();
        $cf = $this->value;
        $E2 = (bool) $this->element["\x68\x69\144\x64\x65\x6e"];
        if (!($E2 == null || !$E2)) {
            goto oQ;
        }
        if (!strtotime($cf)) {
            goto JM;
        }
        $Vd = new JDate($cf);
        $Y2 = $Vd->format(JText::_("\x44\101\124\x45\x5f\106\x4f\x52\x4d\x41\x54\x5f\x4c\x43\x32"));
        $wL[] = "\x3c\144\x69\x76\76" . $Y2 . "\x3c\x2f\144\x69\x76\x3e";
        goto tz;
        JM:
        $wL[] = "\x2d";
        tz:
        oQ:
        $i6 = JFactory::getDate("\x6e\157\167", JFactory::getConfig()->get("\x6f\x66\146\163\x65\164"))->toSql(true);
        $wL[] = "\x3c\x69\156\160\x75\x74\40\x74\x79\x70\145\x3d\x22\x68\151\144\x64\x65\156\x22\x20\156\x61\155\x65\x3d\42" . $this->name . "\42\40\x76\x61\154\165\145\75\x22" . $i6 . "\42\x20\x2f\76";
        return implode($wL);
    }
}
