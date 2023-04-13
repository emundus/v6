<?php


defined("\112\x50\x41\x54\110\x5f\x42\x41\123\105") or die;
jimport("\x6a\x6f\157\x6d\154\141\x2e\146\157\x72\x6d\x2e\x66\x6f\x72\x6d\146\151\145\x6c\144");
class JFormFieldForeignKey extends JFormField
{
    protected $type = "\146\x6f\x72\x65\x69\x67\156\x6b\145\x79";
    private $input_type;
    private $table;
    private $key_field;
    private $value_field;
    protected function getInput()
    {
        $this->input_type = $this->getAttribute("\151\x6e\x70\x75\x74\x5f\164\171\x70\x65");
        $this->table = $this->getAttribute("\x74\141\142\x6c\x65");
        $this->key_field = (string) $this->getAttribute("\x6b\x65\x79\x5f\146\x69\145\154\x64");
        $this->value_field = (string) $this->getAttribute("\166\141\x6c\x75\145\x5f\146\x69\145\x6c\x64");
        $wL = '';
        $i1 = JFactory::getDbo();
        $zO = $i1->getQuery(true);
        $zO->select(array($i1->quoteName($this->key_field), $i1->quoteName($this->value_field)))->from($this->table);
        $i1->setQuery($zO);
        $u8 = $i1->loadObjectList();
        $Z0 = "\x63\154\x61\163\x73\75\42" . $this->getAttribute("\x63\x6c\x61\x73\x73") . "\x22";
        switch ($this->input_type) {
            case "\154\x69\163\x74":
            default:
                $aF = array();
                foreach ($u8 as $mb) {
                    $aF[] = JHtml::_("\x73\x65\154\145\x63\x74\56\157\x70\x74\x69\x6f\156", $mb->{$this->key_field}, $mb->{$this->value_field});
                    sD:
                }
                mr:
                $Gt = $this->value;
                if (is_string($Gt)) {
                    goto xk;
                }
                if (is_object($Gt)) {
                    goto o_;
                }
                goto T4;
                xk:
                $Gt = array($Gt);
                goto T4;
                o_:
                $Gt = get_object_vars($Gt);
                T4:
                if ($this->multiple) {
                    goto Gz;
                }
                array_unshift($aF, JHtml::_("\x73\145\154\145\143\164\56\157\160\x74\151\x6f\156", '', ''));
                goto e9;
                Gz:
                $Z0 .= "\x6d\165\154\164\x69\160\154\x65\x3d\x22\155\165\x6c\164\x69\160\x6c\x65\x22";
                e9:
                $wL = JHtml::_("\x73\145\154\145\x63\x74\56\x67\145\x6e\145\162\x69\x63\x6c\151\x73\164", $aF, $this->name, $Z0, "\166\x61\x6c\165\145", "\x74\x65\x78\164", $Gt, $this->id);
                goto ui;
        }
        OI:
        ui:
        return $wL;
    }
    public function getAttribute($eI, $x8 = null)
    {
        if (!empty($this->element[$eI])) {
            goto Qb;
        }
        return $x8;
        goto m3;
        Qb:
        return $this->element[$eI];
        m3:
    }
}
