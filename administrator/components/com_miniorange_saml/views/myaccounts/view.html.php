<?php


defined("\x5f\112\105\x58\105\x43") or die;
jimport("\152\x6f\x6f\155\x6c\141\56\141\160\160\x6c\151\143\141\164\x69\157\156\x2e\x63\157\x6d\x70\157\156\x65\x6e\164\x2e\x76\151\x65\167");
class Miniorange_samlViewMyaccounts extends JViewLegacy
{
    protected $items;
    protected $pagination;
    protected $state;
    public function display($oA = null)
    {
        $this->state = $this->get("\123\164\141\x74\x65");
        if (!count($errors = $this->get("\105\162\x72\x6f\x72\x73"))) {
            goto cZ;
        }
        throw new Exception(implode("\12", $errors));
        cZ:
        Miniorange_samlHelpersMiniorange_saml::addSubmenu("\155\171\x61\x63\x63\x6f\165\156\164\x73");
        $this->addToolbar();
        $this->sidebar = JHtmlSidebar::render();
        parent::display($oA);
    }
    protected function addToolbar()
    {
        $eo = $this->get("\123\164\141\x74\145");
        $T0 = Miniorange_samlHelpersMiniorange_saml::getActions();
        JToolBarHelper::title(JText::_("\x6d\x69\x6e\151\x3c\142\x20\163\164\x79\x6c\145\x3d\x22\143\x6f\154\157\162\x3a\x6f\162\141\156\147\x65\73\x22\x3e\117\74\57\x62\x3e\162\x61\x6e\147\145\40\x53\x41\x4d\x4c\40\123\120\x20\x45\x6e\164\x65\x72\x70\x72\151\x73\145"), "\x6d\x6f\137\x73\x61\155\x6c\x5f\154\157\x67\x6f\40\155\x6f\x5f\163\x61\x6d\x6c\137\x69\x63\x6f\156");
        $W2 = JPATH_COMPONENT_ADMINISTRATOR . "\x2f\166\x69\x65\x77\163\x2f\155\171\141\143\x63\x6f\165\156\x74";
        if (!file_exists($W2)) {
            goto s2;
        }
        if (!$T0->get("\x63\157\162\145\x2e\x63\162\145\x61\164\145")) {
            goto wu;
        }
        JToolBarHelper::addNew("\x6d\x79\x61\x63\143\x6f\x75\156\164\56\x61\144\x64", "\112\124\x4f\117\114\x42\x41\122\x5f\116\105\127");
        JToolbarHelper::custom("\x6d\171\x61\143\143\x6f\x75\156\164\x73\56\144\x75\160\x6c\151\143\x61\164\145", "\143\x6f\x70\171\x2e\160\x6e\x67", "\143\x6f\160\171\x5f\146\x32\56\160\x6e\x67", "\x4a\124\117\117\x4c\102\101\122\x5f\x44\125\120\x4c\x49\x43\101\x54\105", true);
        wu:
        if (!($T0->get("\143\157\162\145\56\x65\x64\x69\164") && isset($this->items[0]))) {
            goto j0;
        }
        JToolBarHelper::editList("\x6d\x79\x61\x63\x63\x6f\x75\x6e\x74\x2e\145\144\151\164", "\112\x54\x4f\117\x4c\x42\x41\122\x5f\x45\x44\x49\x54");
        j0:
        s2:
        if (!$T0->get("\143\x6f\162\145\x2e\x65\x64\151\164\x2e\x73\164\x61\x74\145")) {
            goto IZ;
        }
        if (isset($this->items[0]->state)) {
            goto kO;
        }
        if (isset($this->items[0])) {
            goto g1;
        }
        goto kW;
        kO:
        JToolBarHelper::divider();
        JToolBarHelper::custom("\x6d\x79\x61\143\143\157\x75\156\x74\x73\56\160\165\x62\154\x69\x73\150", "\x70\165\x62\x6c\151\x73\150\56\x70\x6e\x67", "\x70\165\x62\x6c\151\x73\150\137\x66\x32\56\x70\x6e\147", "\x4a\124\x4f\117\114\102\101\x52\137\120\125\102\114\111\x53\110", true);
        JToolBarHelper::custom("\x6d\171\x61\143\x63\157\x75\156\164\163\x2e\x75\156\x70\x75\142\154\x69\163\150", "\x75\156\x70\x75\142\x6c\x69\163\x68\x2e\x70\x6e\147", "\165\x6e\160\165\x62\154\x69\163\150\137\146\x32\x2e\160\156\147", "\x4a\x54\x4f\x4f\114\x42\101\122\137\x55\x4e\120\x55\x42\114\111\123\x48", true);
        goto kW;
        g1:
        JToolBarHelper::deleteList('', "\155\171\x61\143\x63\x6f\x75\156\x74\x73\56\x64\145\x6c\x65\x74\x65", "\112\x54\117\117\114\102\x41\x52\137\104\105\114\105\124\x45");
        kW:
        if (!isset($this->items[0]->state)) {
            goto tK;
        }
        JToolBarHelper::divider();
        JToolBarHelper::archiveList("\x6d\x79\x61\x63\143\x6f\x75\x6e\x74\163\56\x61\x72\x63\x68\151\x76\x65", "\x4a\124\x4f\117\x4c\x42\x41\122\137\x41\122\103\110\111\126\105");
        tK:
        if (!isset($this->items[0]->checked_out)) {
            goto ZT;
        }
        JToolBarHelper::custom("\x6d\x79\141\143\x63\157\x75\x6e\164\163\56\143\x68\x65\x63\x6b\151\x6e", "\143\x68\x65\x63\153\x69\x6e\x2e\160\156\147", "\x63\x68\145\143\x6b\151\156\x5f\x66\x32\x2e\160\156\x67", "\112\124\x4f\117\114\102\x41\x52\x5f\103\110\105\x43\x4b\111\x4e", true);
        ZT:
        IZ:
        if (!isset($this->items[0]->state)) {
            goto n_;
        }
        if ($eo->get("\x66\x69\x6c\164\x65\x72\56\x73\164\x61\164\145") == -2 && $T0->get("\143\157\162\x65\x2e\144\x65\x6c\145\x74\145")) {
            goto ea;
        }
        if ($T0->get("\143\x6f\162\x65\x2e\x65\144\x69\x74\x2e\163\164\141\164\x65")) {
            goto vs;
        }
        goto ng;
        ea:
        JToolBarHelper::deleteList('', "\x6d\171\x61\x63\143\x6f\165\x6e\x74\x73\x2e\144\145\154\145\164\x65", "\112\x54\x4f\117\x4c\x42\101\122\137\x45\115\120\124\x59\137\124\x52\x41\x53\x48");
        JToolBarHelper::divider();
        goto ng;
        vs:
        JToolBarHelper::trash("\x6d\171\141\143\143\157\x75\x6e\164\x73\56\x74\x72\141\163\x68", "\112\x54\117\117\114\x42\101\x52\x5f\x54\x52\101\x53\110");
        JToolBarHelper::divider();
        ng:
        n_:
        if (!$T0->get("\143\157\x72\145\56\x61\144\155\151\x6e")) {
            goto Zg;
        }
        JToolBarHelper::preferences("\143\157\155\137\155\x69\x6e\x69\x6f\x72\141\x6e\147\145\137\x73\x61\155\154");
        Zg:
        JHtmlSidebar::setAction("\x69\x6e\144\145\170\56\160\150\160\x3f\x6f\160\x74\x69\x6f\x6e\x3d\x63\157\155\137\155\151\156\x69\157\x72\141\156\147\145\x5f\x73\141\x6d\154\46\166\x69\145\x77\x3d\x6d\171\x61\x63\x63\x6f\165\x6e\164\163");
        $this->extra_sidebar = '';
    }
    protected function getSortFields()
    {
        return array();
    }
}
