<?php


defined("\x4a\120\x41\124\x48\x5f\x42\x41\x53\105") or die;
jimport("\x6a\157\x6f\155\154\x61\x2e\146\x6f\162\155\x2e\146\x6f\162\x6d\146\x69\x65\x6c\144");
class JFormFieldForeignKey extends JFormField
{
    protected $type = "\x66\157\162\x65\151\x67\156\x6b\x65\171";
    private $input_type;
    private $table;
    private $key_field;
    private $value_field;
    protected function getInput()
    {
        $this->input_type = $this->getAttribute("\151\x6e\160\165\x74\x5f\x74\x79\x70\x65");
        $this->table = $this->getAttribute("\x74\x61\142\x6c\145");
        $this->key_field = (string) $this->getAttribute("\x6b\x65\171\137\x66\x69\145\154\144");
        $this->value_field = (string) $this->getAttribute("\x76\x61\154\x75\x65\x5f\x66\151\x65\154\144");
        $dW = '';
        $dZ = JFactory::getDbo();
        $qH = $dZ->getQuery(true);
        $qH->select(array($dZ->quoteName($this->key_field), $dZ->quoteName($this->value_field)))->from($this->table);
        $dZ->setQuery($qH);
        $tk = $dZ->loadObjectList();
        $tN = "\143\154\141\x73\163\75\42" . $this->getAttribute("\x63\x6c\x61\x73\x73") . "\42";
        switch ($this->input_type) {
            case "\154\151\163\164":
            default:
                $wf = array();
                foreach ($tk as $bZ) {
                    $wf[] = JHtml::_("\x73\145\154\x65\x63\164\56\157\x70\x74\x69\157\156", $bZ->{$this->key_field}, $bZ->{$this->value_field});
                    Mi:
                }
                mT:
                $Uf = $this->value;
                if (is_string($Uf)) {
                    goto wl;
                }
                if (is_object($Uf)) {
                    goto TG;
                }
                goto zE;
                wl:
                $Uf = array($Uf);
                goto zE;
                TG:
                $Uf = get_object_vars($Uf);
                zE:
                if ($this->multiple) {
                    goto Yv;
                }
                array_unshift($wf, JHtml::_("\163\x65\x6c\x65\143\x74\x2e\x6f\160\x74\x69\x6f\x6e", '', ''));
                goto tZ;
                Yv:
                $tN .= "\x6d\x75\154\164\151\160\x6c\x65\75\42\x6d\x75\x6c\164\151\x70\154\145\42";
                tZ:
                $dW = JHtml::_("\x73\145\154\x65\x63\x74\56\147\x65\156\x65\x72\x69\143\154\x69\163\x74", $wf, $this->name, $tN, "\x76\x61\x6c\x75\145", "\164\145\x78\164", $Uf, $this->id);
                goto Y3;
        }
        CF:
        Y3:
        return $dW;
    }
    public function getAttribute($Xm, $M1 = null)
    {
        if (!empty($this->element[$Xm])) {
            goto D_;
        }
        return $M1;
        goto wL;
        D_:
        return $this->element[$Xm];
        wL:
    }
}
