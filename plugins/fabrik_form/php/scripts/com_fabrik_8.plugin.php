<?php
class jc_com_fabrik_8 extends JCommentsPlugin
{
    function getObjectTitle($id)
    {
        $title = "Assistenza ".$id;
        return $title;
    }
	
    function getObjectLink($id)
    {
        $link = JRoute::_('index.php?option=com_fabrik&amp;view=form&formid=8&rowid=' .$id. '&listid=8');        
        return $link;
    }

    function getObjectOwner($id)
    {
        $user =& JFactory::getUser();
        return $user->get('id');
    }	
	

}
?>

