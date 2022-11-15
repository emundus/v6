<?php


defined("\137\112\105\x58\x45\103") or die;
jimport("\x6a\157\157\155\154\x61\x2e\141\160\160\154\151\143\x61\x74\151\x6f\x6e\56\143\x6f\x6d\160\x6f\x6e\145\156\164\x2e\x6d\157\144\x65\154\x6c\x69\x73\x74");
class Miniorange_samlModelMyaccounts extends JModelList
{
    protected function populateState($tG = null, $aV = null)
    {
        $Ju = JFactory::getApplication("\141\144\155\x69\x6e\151\x73\164\162\141\164\x6f\x72");
        $DB = $Ju->getUserStateFromRequest($this->context . "\x2e\146\151\154\x74\x65\x72\x2e\163\145\141\x72\143\x68", "\146\151\154\164\145\x72\x5f\163\145\x61\x72\x63\x68");
        $this->setState("\146\151\x6c\164\x65\x72\x2e\163\145\141\x72\x63\x68", $DB);
        $uy = $Ju->getUserStateFromRequest($this->context . "\x2e\146\151\154\164\x65\x72\x2e\163\x74\x61\x74\x65", "\x66\x69\x6c\x74\145\162\x5f\160\x75\142\154\x69\163\150\145\x64", '', "\x73\164\162\x69\x6e\147");
        $this->setState("\x66\x69\154\x74\x65\162\x2e\x73\164\x61\x74\145", $uy);
        $v0 = JComponentHelper::getParams("\143\157\x6d\137\x6d\151\x6e\x69\157\x72\x61\156\147\x65\x5f\163\141\155\x6c");
        $this->setState("\160\x61\x72\x61\x6d\163", $v0);
        parent::populateState("\x61\x2e\x69\x64", "\141\x73\143");
    }
    protected function getStoreId($ox = '')
    {
        $ox .= "\x3a" . $this->getState("\x66\x69\154\x74\x65\x72\x2e\163\x65\141\162\143\150");
        $ox .= "\72" . $this->getState("\146\x69\x6c\164\x65\x72\56\x73\164\x61\164\145");
        return parent::getStoreId($ox);
    }
    protected function getListQuery()
    {
        $dZ = $this->getDbo();
        $qH = $dZ->getQuery(true);
        return $qH;
    }
    public function getItems()
    {
        $Ng = parent::getItems();
        return $Ng;
    }
}
