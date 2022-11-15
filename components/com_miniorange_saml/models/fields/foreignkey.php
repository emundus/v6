<?php


defined("\112\120\x41\124\110\x5f\102\101\x53\x45") or die;
jimport("\152\157\157\155\x6c\141\x2e\x66\x6f\x72\x6d\x2e\x66\157\162\x6d\x66\151\145\154\x64");
class JFormFieldForeignKey extends JFormField
{
    protected $type = "\x66\x6f\162\x65\151\x67\x6e\153\x65\171";
    private $input_type;
    private $table;
    private $key_field;
    private $value_field;
    protected function getInput()
    {
        $this->input_type = $this->getAttribute("\151\x6e\x70\x75\x74\x5f\164\x79\160\x65");
        $this->table = $this->getAttribute("\x74\x61\x62\x6c\x65");
        $this->key_field = (string) $this->getAttribute("\153\x65\171\137\146\x69\145\x6c\x64");
        $this->value_field = (string) $this->getAttribute("\x76\x61\154\165\x65\x5f\146\151\145\154\144");
        $dW = '';
        $dZ = JFactory::getDbo();
        $qH = $dZ->getQuery(true);
        $qH->select(array($dZ->quoteName($this->key_field), $dZ->quoteName($this->value_field)))->from($this->table);
        $dZ->setQuery($qH);
        $tk = $dZ->loadObjectList();
        $tN = "\x63\x6c\x61\x73\x73\x3d\42" . $this->getAttribute("\x63\x6c\141\163\x73") . "\x22";
        switch ($this->input_type) {
            case "\154\x69\163\x74":
            default:
                $wf = array();
                foreach ($tk as $bZ) {
                    $wf[] = JHtml::_("\163\145\x6c\x65\143\164\56\x6f\160\x74\151\157\x6e", $bZ->{$this->key_field}, $bZ->{$this->value_field});
                    hL:
                }
                ba:
                $Uf = $this->value;
                if (is_string($Uf)) {
                    goto vG;
                }
                if (is_object($Uf)) {
                    goto Kp;
                }
                goto ZC;
                vG:
                $Uf = array($Uf);
                goto ZC;
                Kp:
                $Uf = get_object_vars($Uf);
                ZC:
                if ($this->multiple) {
                    goto aE;
                }
                array_unshift($wf, JHtml::_("\163\145\154\145\143\x74\x2e\x6f\160\x74\151\x6f\x6e", '', ''));
                goto R4;
                aE:
                $tN .= "\155\x75\x6c\164\x69\x70\154\145\75\x22\155\x75\154\164\151\x70\x6c\x65\x22";
                R4:
                $dW = JHtml::_("\163\145\154\145\x63\x74\x2e\147\x65\156\145\162\x69\x63\154\151\x73\x74", $wf, $this->name, $tN, "\x76\141\x6c\165\145", "\x74\145\x78\x74", $Uf, $this->id);
                goto cD;
        }
        Dp:
        cD:
        return $dW;
    }
    public function getAttribute($Xm, $M1 = null)
    {
        if (!empty($this->element[$Xm])) {
            goto aq;
        }
        return $M1;
        goto Zf;
        aq:
        return $this->element[$Xm];
        Zf:
    }
}
