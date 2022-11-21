<?php


defined("\x5f\112\x45\x58\x45\103") or die;
jimport("\x6a\157\x6f\155\154\141\x2e\141\160\x70\x6c\151\143\x61\164\151\157\x6e\56\143\x6f\x6d\160\157\156\145\x6e\164\x2e\x76\151\145\x77");
class Miniorange_samlViewMyaccounts extends JViewLegacy
{
    protected $items;
    protected $pagination;
    protected $state;
    public function display($Cg = null)
    {
        $this->state = $this->get("\x53\x74\x61\164\x65");
        if (!count($errors = $this->get("\x45\162\x72\157\x72\x73"))) {
            goto gV;
        }
        throw new Exception(implode("\xa", $errors));
        gV:
        Miniorange_samlHelpersMiniorange_saml::addSubmenu("\x6d\x79\141\x63\143\157\x75\x6e\x74\x73");
        $this->addToolbar();
        $this->sidebar = JHtmlSidebar::render();
        parent::display($Cg);
    }
    protected function addToolbar()
    {
        $vh = $this->get("\x53\164\141\x74\145");
        $k2 = Miniorange_samlHelpersMiniorange_saml::getActions();
        JToolBarHelper::title(JText::_("\155\151\156\151\74\x62\40\163\x74\x79\x6c\145\75\42\x63\x6f\x6c\157\162\72\157\162\141\156\x67\x65\x3b\42\x3e\x4f\74\x2f\x62\x3e\x72\141\156\x67\145\40\123\101\x4d\x4c\x20\123\120\40\105\x6e\164\x65\x72\x70\162\151\163\x65"), "\155\x6f\137\x73\x61\155\x6c\137\154\157\x67\x6f\x20\155\157\x5f\163\x61\155\x6c\137\151\x63\x6f\156");
        $YP = JPATH_COMPONENT_ADMINISTRATOR . "\x2f\166\x69\145\167\163\x2f\155\171\x61\x63\143\x6f\165\x6e\164";
        if (!file_exists($YP)) {
            goto di;
        }
        if (!$k2->get("\x63\157\x72\145\x2e\x63\x72\145\141\x74\145")) {
            goto jZ;
        }
        JToolBarHelper::addNew("\x6d\171\141\x63\x63\157\x75\x6e\164\x2e\141\x64\x64", "\112\x54\x4f\x4f\x4c\x42\x41\122\137\x4e\105\x57");
        JToolbarHelper::custom("\155\171\x61\x63\x63\157\x75\x6e\x74\163\56\144\x75\160\154\x69\143\x61\164\x65", "\x63\x6f\x70\x79\x2e\x70\x6e\x67", "\x63\157\160\171\x5f\146\62\56\160\x6e\x67", "\112\x54\x4f\117\x4c\x42\x41\x52\137\104\x55\x50\114\111\x43\x41\124\x45", true);
        jZ:
        if (!($k2->get("\x63\x6f\162\145\56\x65\x64\x69\x74") && isset($this->items[0]))) {
            goto Bf;
        }
        JToolBarHelper::editList("\155\x79\x61\143\143\x6f\165\156\x74\x2e\145\144\x69\x74", "\x4a\124\117\117\x4c\x42\101\122\137\x45\x44\x49\x54");
        Bf:
        di:
        if (!$k2->get("\143\x6f\x72\145\x2e\x65\144\x69\164\x2e\163\164\x61\164\x65")) {
            goto na;
        }
        if (isset($this->items[0]->state)) {
            goto qC;
        }
        if (isset($this->items[0])) {
            goto yf;
        }
        goto Mz;
        qC:
        JToolBarHelper::divider();
        JToolBarHelper::custom("\155\x79\x61\x63\x63\x6f\165\156\164\x73\x2e\x70\x75\142\154\x69\163\x68", "\x70\165\x62\x6c\151\163\x68\x2e\160\x6e\x67", "\x70\x75\142\154\151\x73\x68\x5f\146\62\x2e\160\x6e\147", "\x4a\124\x4f\117\x4c\x42\101\x52\x5f\x50\125\102\114\111\123\110", true);
        JToolBarHelper::custom("\155\x79\141\x63\x63\157\165\x6e\x74\163\56\165\x6e\160\165\x62\154\x69\x73\150", "\x75\156\160\x75\142\x6c\151\x73\150\x2e\160\156\x67", "\165\x6e\160\165\142\x6c\151\x73\x68\137\x66\62\x2e\160\156\147", "\112\x54\117\x4f\114\102\101\x52\137\x55\116\x50\125\x42\114\x49\123\110", true);
        goto Mz;
        yf:
        JToolBarHelper::deleteList('', "\155\171\x61\x63\x63\157\165\156\x74\x73\x2e\x64\145\x6c\145\164\145", "\112\124\117\x4f\x4c\x42\x41\x52\137\104\x45\x4c\105\124\105");
        Mz:
        if (!isset($this->items[0]->state)) {
            goto om;
        }
        JToolBarHelper::divider();
        JToolBarHelper::archiveList("\155\x79\141\143\143\x6f\165\156\x74\x73\56\x61\x72\x63\x68\151\166\145", "\112\x54\117\117\x4c\102\x41\x52\x5f\x41\x52\x43\110\111\126\x45");
        om:
        if (!isset($this->items[0]->checked_out)) {
            goto iy;
        }
        JToolBarHelper::custom("\155\171\141\x63\x63\157\165\156\x74\x73\x2e\143\x68\145\143\x6b\151\156", "\143\150\145\143\153\151\156\x2e\x70\x6e\147", "\x63\150\145\x63\153\x69\x6e\137\146\x32\56\160\156\147", "\112\x54\117\117\x4c\102\101\x52\x5f\x43\110\x45\x43\x4b\x49\116", true);
        iy:
        na:
        if (!isset($this->items[0]->state)) {
            goto QM;
        }
        if ($vh->get("\x66\x69\154\x74\145\x72\x2e\163\164\141\164\x65") == -2 && $k2->get("\x63\x6f\162\145\56\x64\x65\154\x65\164\145")) {
            goto t7;
        }
        if ($k2->get("\143\x6f\162\145\56\x65\x64\x69\164\x2e\163\164\x61\164\x65")) {
            goto eY;
        }
        goto hx;
        t7:
        JToolBarHelper::deleteList('', "\x6d\x79\x61\143\x63\x6f\165\x6e\x74\163\x2e\x64\145\x6c\145\164\145", "\112\x54\x4f\x4f\x4c\x42\x41\x52\137\105\115\120\124\131\x5f\x54\122\101\x53\110");
        JToolBarHelper::divider();
        goto hx;
        eY:
        JToolBarHelper::trash("\x6d\171\141\143\x63\157\165\156\x74\x73\56\164\162\141\x73\150", "\112\124\117\117\114\x42\x41\122\x5f\124\122\101\x53\110");
        JToolBarHelper::divider();
        hx:
        QM:
        if (!$k2->get("\x63\x6f\162\x65\56\x61\144\155\x69\156")) {
            goto uR;
        }
        JToolBarHelper::preferences("\x63\157\155\137\x6d\x69\x6e\x69\x6f\x72\141\x6e\147\145\137\163\141\155\154");
        uR:
        JHtmlSidebar::setAction("\x69\156\144\x65\170\56\x70\x68\160\77\157\160\164\151\x6f\156\x3d\x63\x6f\155\137\155\x69\x6e\151\x6f\x72\141\156\x67\x65\137\163\x61\155\154\46\166\151\145\167\x3d\x6d\x79\141\x63\143\157\165\x6e\164\x73");
        $this->extra_sidebar = '';
    }
    protected function getSortFields()
    {
        return array();
    }
}
