<?php


function mo_saml_local_support()
{
    $current_user = JFactory::getUser();
    $bZ = UtilitiesSAML::getCustomerDetails();
    $wI = $bZ["\x65\155\x61\151\154"];
    $Ue = $bZ["\141\144\155\x69\x6e\x5f\160\x68\157\x6e\145"];
    echo "\74\x64\x69\x76\40\x63\154\x61\163\163\75\42\155\157\x5f\163\x61\155\x6c\x5f\163\x75\160\160\x6f\x72\x74\42\40\x69\x64\75\42\155\157\137\163\x61\x6d\x6c\x5f\x73\x75\x70\x70\157\x72\x74\42\76\xa\11\74\144\x69\166\x20\x63\154\141\x73\163\75\42\x6d\x6f\137\142\x6f\x6f\x74\x5f\143\157\x6c\x2d\163\155\x2d\x31\62\x22\76\xa\x20\x20\40\40\40\x20\x20\x20\74\144\x69\166\40\143\x6c\141\163\x73\x3d\42\x6d\x6f\x5f\x62\x6f\x6f\164\x5f\162\x6f\167\42\x3e\xa\40\x20\x20\40\x20\x20\40\40\40\40\x20\40\x3c\x68\x33\x3e\106\145\x61\164\165\162\145\40\122\x65\x71\165\145\163\x74\x2f\x43\157\x6e\164\141\x63\164\40\125\x73\74\57\150\x33\76\12\x20\40\40\40\40\40\40\40\x3c\x2f\144\151\x76\76\74\150\x72\76\12\x9\74\x2f\144\151\166\76\xa\11\74\144\x69\x76\40\x63\154\x61\x73\x73\x3d\x22\155\157\x5f\142\x6f\157\x74\x5f\143\x6f\x6c\x2d\163\x6d\x2d\61\x32\x22\76\xa\x20\40\40\x20\x20\40\x20\x20\x3c\144\151\x76\40\x73\x74\x79\154\145\75\x22\x66\x6c\157\141\164\72\40\x6c\145\146\x74\73\167\151\x64\x74\x68\72\x20\x37\45\73\x6d\x61\162\147\151\x6e\x2d\164\157\x70\72\x20\55\x37\160\x78\73\42\x3e\74\151\155\147\x20\x73\162\143\x3d\42";
    echo JUri::base();
    echo "\x2f\143\x6f\x6d\160\157\156\145\156\164\163\x2f\143\x6f\155\137\155\151\x6e\151\x6f\x72\x61\156\x67\145\137\x73\141\155\x6c\x2f\141\163\163\145\x74\163\57\x69\155\141\147\x65\x73\x2f\x70\x68\157\156\145\56\163\166\147\x22\40\x77\x69\x64\x74\150\x3d\x22\x33\62\x22\40\150\145\151\147\x68\x74\75\42\x33\x32\x22\x3e\74\57\x64\151\x76\76\12\40\40\40\40\40\x20\x20\x20\x3c\x70\76\74\142\x3e\46\145\x6d\163\x70\73\116\x65\x65\144\x20\141\156\x79\x20\x68\x65\x6c\x70\x3f\40\112\165\163\164\x20\x67\151\166\x65\x20\165\x73\x20\x61\x20\143\x61\x6c\x6c\x20\141\164\40\x3c\163\160\141\x6e\40\163\x74\171\x6c\x65\75\42\143\157\154\157\162\72\x72\x65\144\42\76\53\61\40\x39\x37\70\40\66\x35\70\40\x39\x33\x38\x37\74\57\x73\x70\141\156\76\74\57\x62\76\74\x2f\160\76\74\142\x72\x3e\xa\x20\x20\x20\x20\40\x20\x20\40\x3c\x70\x3e\x57\145\40\x63\141\156\40\150\x65\154\x70\40\x79\x6f\165\40\167\x69\164\150\x20\143\x6f\156\x66\151\147\x75\x72\x69\x6e\147\x20\x79\x6f\x75\x72\40\x49\144\x65\156\164\151\164\x79\40\120\x72\157\166\151\144\x65\162\56\40\112\165\163\x74\40\163\x65\156\144\40\x75\x73\x20\141\40\x71\165\145\x72\171\40\x61\156\x64\x20\x77\x65\x20\167\x69\154\154\40\147\145\164\x20\142\141\x63\x6b\40\x74\157\40\171\x6f\165\40\x73\x6f\157\156\x2e\x3c\57\x70\76\xa\11\x3c\x2f\x64\x69\x76\x3e\xa\11\x3c\144\x69\x76\x20\x3e\12\11\11\74\146\x6f\162\155\x20\x6e\x61\155\x65\75\x22\x66\42\40\155\145\x74\x68\157\x64\75\42\160\x6f\x73\164\42\40\141\143\164\151\x6f\x6e\75\x22";
    echo JRoute::_("\151\156\144\x65\170\x2e\x70\x68\160\77\157\160\x74\x69\157\156\75\x63\x6f\155\137\x6d\x69\x6e\151\157\x72\141\156\x67\145\x5f\x73\x61\155\154\x26\x74\x61\x73\x6b\75\155\171\x61\143\143\x6f\x75\x6e\x74\x2e\x63\x6f\156\164\141\143\x74\x55\163");
    echo "\x22\76\xa\11\11\11\74\151\156\x70\x75\x74\x20\164\x79\x70\145\75\x22\145\155\x61\x69\154\42\40\x73\x74\171\154\x65\75\42\167\151\x64\164\150\72\61\60\x30\45\42\40\x63\x6c\x61\x73\x73\x3d\x22\155\157\137\163\141\155\154\x5f\x74\x61\x62\154\x65\137\164\x65\x78\x74\x62\x6f\x78\x20\x6d\x6f\137\142\x6f\157\x74\137\146\157\x72\x6d\x2d\x63\x6f\156\164\x72\x6f\154\x22\40\156\x61\x6d\x65\75\x22\161\x75\145\x72\x79\137\145\x6d\x61\x69\x6c\42\x20\x76\141\x6c\x75\x65\75\x22";
    echo $wI;
    echo "\42\40\x70\x6c\x61\143\145\150\x6f\154\144\145\162\x3d\42\105\156\164\x65\162\x20\x79\x6f\165\162\x20\x65\155\x61\151\x6c\x22\40\162\145\161\x75\151\162\x65\144\x2f\76\x3c\142\x72\76\xa\x9\11\x9\x3c\x69\156\x70\165\164\40\164\x79\x70\x65\x3d\x22\164\145\x78\164\42\40\x20\163\164\x79\x6c\145\75\x22\167\151\x64\x74\150\x3a\x31\60\60\45\42\x20\x70\141\164\x74\x65\x72\156\x3d\x22\133\134\x2b\135\x5b\60\55\71\135\173\67\54\x31\x35\x7d\42\40\x63\154\x61\163\x73\x3d\x22\x6d\x6f\137\x73\141\x6d\154\x5f\x74\x61\x62\x6c\x65\x5f\164\x65\170\164\x62\157\x78\40\155\157\137\142\157\x6f\x74\137\146\157\x72\155\55\143\x6f\x6e\164\x72\157\154\42\x20\x6e\141\x6d\x65\x3d\x22\161\x75\x65\162\x79\x5f\x70\x68\x6f\156\x65\42\40\x76\141\x6c\165\145\x3d\42";
    echo $Ue;
    echo "\42\40\x70\x6c\x61\x63\x65\150\x6f\x6c\x64\145\162\x3d\x22\105\x6e\x74\x65\162\40\171\157\x75\x72\40\x70\x68\x6f\x6e\145\40\x77\x69\164\x68\x20\x63\x6f\x75\x6e\x74\x72\171\40\143\x6f\144\x65\42\x2f\76\x3c\142\162\x3e\xa\x9\x9\x9\74\164\x65\x78\164\141\162\x65\x61\x20\x6e\x61\155\145\x3d\x22\161\x75\x65\x72\171\x22\40\143\x6c\x61\163\163\x3d\42\x6d\x6f\x5f\163\141\155\154\137\164\x61\142\x6c\145\x5f\164\x65\170\164\142\x6f\x78\42\40\163\164\x79\154\145\x3d\42\142\157\162\144\145\x72\55\x72\x61\x64\x69\x75\x73\x3a\64\160\x78\73\x72\145\x73\151\172\x65\72\40\166\145\x72\164\151\143\141\x6c\73\167\x69\144\x74\150\72\x31\60\x30\x25\73\40\142\157\x72\144\145\x72\72\40\x31\160\170\x20\163\x6f\x6c\151\144\x20\43\70\x36\70\x33\70\63\41\x69\155\x70\x6f\x72\164\x61\156\x74\73\x22\40\x63\157\154\x73\x3d\x22\x35\x32\x22\40\162\157\167\x73\75\42\66\x22\40\x70\154\x61\143\145\150\x6f\x6c\144\145\162\75\x22\x57\x72\151\x74\145\x20\171\157\x75\162\40\161\165\145\162\x79\40\150\145\x72\145\42\x20\x72\x65\x71\165\151\162\145\x64\76\x3c\57\164\x65\x78\164\x61\x72\x65\141\76\74\x62\x72\76\12\11\x9\x9\40\40\40\74\x6c\141\x62\x65\x6c\40\x63\154\141\163\163\x3d\42\155\157\x5f\163\165\160\160\x6f\x72\164\137\163\x77\151\x74\x63\150\x22\76\12\x20\x20\x20\40\x20\40\x20\x20\40\x20\40\x20\40\40\40\74\151\156\160\165\x74\40\x74\171\160\x65\75\42\x63\150\145\143\153\142\x6f\x78\x22\x20\x6e\141\155\145\x3d\x22\x73\145\x6e\144\x5f\x70\x6c\165\x67\151\x6e\137\x63\157\x6e\146\x69\x67\42\40\x76\141\154\x75\145\x3d\42\61\x22\x20\57\x3e\12\11\x9\x9\x20\x20\x20\x3c\163\x70\x61\x6e\x20\143\x6c\x61\163\163\x3d\42\155\x6f\137\x73\165\x70\160\x6f\x72\x74\x5f\163\154\x69\x64\x65\162\40\x72\x6f\165\x6e\x64\x22\76\x3c\x2f\163\x70\x61\x6e\76\xa\11\11\x9\x20\x20\40\x20\x20\x3c\57\x6c\x61\x62\145\x6c\x3e\xa\11\11\x9\x9\40\x3c\x73\x70\141\x6e\x20\163\x74\x79\154\145\75\42\160\x61\x64\x64\151\156\147\55\154\x65\146\x74\72\x35\160\170\x22\76\74\x62\x3e\123\145\x6e\144\x20\x70\x6c\165\x67\151\156\40\x63\x6f\156\x66\x69\x67\x75\x72\141\x74\x69\157\156\40\x77\x69\x74\150\40\x74\150\x65\40\x71\x75\145\x72\x79\x3c\x2f\142\76\x3c\x2f\x73\160\x61\156\x3e\12\11\x9\x9\11\x3c\142\x72\x3e\xa\11\x9\11\x9\74\x64\151\166\40\143\x6c\141\163\x73\75\x22\x74\145\170\x74\55\x63\x65\x6e\164\x65\x72\42\40\x73\164\171\154\145\75\42\x6d\x61\162\x67\x69\x6e\55\164\157\x70\x3a\x32\45\42\76\xa\x9\x9\x9\x9\x9\74\x69\x6e\x70\165\x74\x20\x74\171\x70\x65\75\x22\163\x75\142\155\x69\x74\x22\40\156\x61\155\145\x3d\x22\x73\145\156\144\137\161\165\145\x72\x79\42\x20\x73\164\171\154\145\75\x22\x6d\141\162\147\151\156\x2d\164\x6f\x70\72\x31\x30\160\170\x3b\x22\40\166\141\154\165\145\75\x22\x53\x75\142\x6d\151\164\x20\x51\165\x65\162\171\42\40\x63\154\141\163\x73\75\42\x6d\157\137\142\157\x6f\x74\137\142\x74\x6e\x20\155\x6f\x5f\142\157\157\x74\x5f\142\164\x6e\55\163\x75\x63\143\x65\x73\x73\42\57\76\12\x9\11\11\x9\x9\74\x61\x20\x68\162\x65\146\x3d\42\x68\164\164\160\163\x3a\57\57\146\157\x72\x75\x6d\x2e\x6d\x69\x6e\151\157\162\x61\x6e\x67\x65\x2e\143\x6f\x6d\x2f\x22\40\163\164\171\154\x65\75\42\155\141\162\147\x69\x6e\55\x74\157\x70\72\61\60\x70\x78\73\x22\x20\x63\154\x61\163\x73\x3d\x22\155\x6f\x5f\x62\x6f\157\164\137\142\x74\156\x20\x6d\x6f\x5f\142\x6f\157\x74\x5f\142\x74\x6e\x2d\x73\x75\143\143\145\163\163\42\40\x74\141\162\147\145\164\75\42\137\142\x6c\x61\x6e\153\x22\76\x41\163\x6b\x20\121\x75\x65\163\164\151\157\x6e\x73\40\x6f\x6e\x20\146\157\162\x75\155\74\57\x61\76\xa\11\x9\x9\11\74\x2f\144\151\166\x3e\12\11\11\x9\11\x9\xa\x9\11\x3c\57\146\157\x72\x6d\x3e\74\x68\162\x3e\xa\x9\x3c\57\144\151\x76\76\xa\11\x3c\144\x69\x76\40\x63\154\141\163\163\75\42\x6d\x6f\x5f\x62\x6f\x6f\x74\x5f\x63\157\154\x2d\x73\x6d\x2d\61\x32\x22\x3e\xa\11\x9\74\x70\76\xa\x9\11\11\x49\x66\x20\171\x6f\165\x20\x77\x61\156\164\x20\x63\x75\x73\x74\x6f\x6d\40\x66\145\141\164\165\x72\145\x73\x20\x69\x6e\40\x74\150\x65\40\x70\x6c\165\x67\x69\156\54\40\152\x75\x73\164\40\x64\162\x6f\160\x20\141\156\x20\145\x6d\141\151\154\x20\164\x6f\x20\12\x9\x9\x9\x3c\141\x20\x68\x72\145\x66\x3d\42\x6d\141\151\154\164\x6f\72\152\157\157\155\154\141\x73\165\x70\160\x6f\x72\164\x40\170\x65\x63\165\x72\x69\146\171\56\x63\x6f\x6d\x22\x3e\74\151\40\x73\164\x79\x6c\145\75\x22\167\x6f\162\144\55\167\162\x61\160\72\142\x72\x65\141\153\x2d\167\x6f\162\144\x22\x3e\x6a\157\157\x6d\154\x61\163\x75\x70\x70\x6f\162\164\100\170\x65\x63\165\x72\151\x66\171\56\143\x6f\155\x3c\57\x69\76\74\x2f\141\x3e\xa\11\11\74\x2f\x70\76\xa\x9\74\x2f\144\x69\166\76\12\74\x2f\144\x69\x76\76\12\40\x20\x20\x20\12";
}