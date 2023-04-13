<?php


defined("\112\x50\x41\x54\110\x5f\102\x41\x53\105") or die;
jimport("\x6a\x6f\x6f\155\154\141\x2e\x66\157\162\155\56\x66\x6f\162\x6d\x66\151\145\154\144");
class JFormFieldForeignKey extends JFormField
{
    protected $type = "\x66\x6f\x72\x65\x69\147\x6e\153\x65\x79";
    private $input_type;
    private $table;
    private $key_field;
    private $value_field;
    protected function getInput()
    {
        $this->input_type = $this->getAttribute("\151\x6e\160\x75\x74\137\x74\x79\160\145");
        $this->table = $this->getAttribute("\164\141\142\154\145");
        $this->key_field = (string) $this->getAttribute("\x6b\145\x79\x5f\146\151\x65\154\x64");
        $this->value_field = (string) $this->getAttribute("\166\141\154\165\x65\x5f\x66\x69\x65\154\144");
        $wL = '';
        $i1 = JFactory::getDbo();
        $zO = $i1->getQuery(true);
        $zO->select(array($i1->quoteName($this->key_field), $i1->quoteName($this->value_field)))->from($this->table);
        $i1->setQuery($zO);
        $u8 = $i1->loadObjectList();
        $Z0 = "\143\154\x61\163\x73\75\42" . $this->getAttribute("\143\154\141\163\163") . "\x22";
        switch ($this->input_type) {
            case "\154\x69\x73\164":
            default:
                $aF = array();
                foreach ($u8 as $mb) {
                    $aF[] = JHtml::_("\x73\x65\x6c\x65\143\x74\x2e\157\160\164\151\157\156", $mb->{$this->key_field}, $mb->{$this->value_field});
                    gy:
                }
                QN:
                $Gt = $this->value;
                if (is_string($Gt)) {
                    goto Bi;
                }
                if (is_object($Gt)) {
                    goto JF;
                }
                goto UW;
                Bi:
                $Gt = array($Gt);
                goto UW;
                JF:
                $Gt = get_object_vars($Gt);
                UW:
                if ($this->multiple) {
                    goto GT;
                }
                array_unshift($aF, JHtml::_("\x73\x65\154\145\x63\x74\56\x6f\x70\x74\x69\157\x6e", '', ''));
                goto yx;
                GT:
                $Z0 .= "\x6d\165\x6c\164\x69\160\154\145\75\42\155\x75\x6c\x74\x69\x70\x6c\x65\x22";
                yx:
                $wL = JHtml::_("\x73\145\x6c\x65\143\x74\56\147\145\156\x65\x72\151\x63\154\151\x73\164", $aF, $this->name, $Z0, "\x76\141\x6c\x75\x65", "\164\x65\x78\164", $Gt, $this->id);
                goto Cc;
        }
        Wa:
        Cc:
        return $wL;
    }
    public function getAttribute($eI, $x8 = null)
    {
        if (!empty($this->element[$eI])) {
            goto Jn;
        }
        return $x8;
        goto Z_;
        Jn:
        return $this->element[$eI];
        Z_:
    }
}
