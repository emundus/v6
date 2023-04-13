<?php


defined("\137\x4a\105\130\x45\x43") or die;
jimport("\152\157\x6f\155\x6c\x61\x2e\141\160\160\x6c\151\143\141\164\151\157\156\56\x63\x6f\155\160\157\x6e\145\x6e\164\56\x6d\157\x64\145\x6c\154\151\163\x74");
class Miniorange_samlModelMyaccounts extends JModelList
{
    protected function populateState($MC = null, $Om = null)
    {
        $aU = JFactory::getApplication("\x61\x64\x6d\x69\x6e\x69\163\x74\162\x61\x74\x6f\x72");
        $xf = $aU->getUserStateFromRequest($this->context . "\x2e\x66\151\x6c\164\145\x72\x2e\163\145\141\x72\143\x68", "\146\151\x6c\x74\145\x72\x5f\163\x65\141\x72\x63\x68");
        $this->setState("\x66\151\154\x74\145\162\56\x73\x65\x61\x72\143\x68", $xf);
        $wb = $aU->getUserStateFromRequest($this->context . "\56\x66\151\154\x74\145\162\56\x73\x74\x61\x74\x65", "\146\151\x6c\164\x65\162\x5f\160\x75\x62\x6c\151\x73\150\145\144", '', "\x73\164\x72\x69\x6e\147");
        $this->setState("\x66\151\154\164\x65\x72\56\x73\x74\x61\x74\145", $wb);
        $fH = JComponentHelper::getParams("\143\x6f\x6d\x5f\155\x69\x6e\151\x6f\x72\141\x6e\x67\x65\137\163\141\155\154");
        $this->setState("\160\141\x72\141\x6d\163", $fH);
        parent::populateState("\141\56\151\144", "\141\163\143");
    }
    protected function getStoreId($YH = '')
    {
        $YH .= "\72" . $this->getState("\146\x69\154\x74\145\162\x2e\x73\x65\141\x72\x63\150");
        $YH .= "\72" . $this->getState("\x66\151\x6c\x74\x65\x72\56\163\164\x61\x74\145");
        return parent::getStoreId($YH);
    }
    protected function getListQuery()
    {
        $i1 = $this->getDbo();
        $zO = $i1->getQuery(true);
        return $zO;
    }
    public function getItems()
    {
        $yz = parent::getItems();
        return $yz;
    }
}
