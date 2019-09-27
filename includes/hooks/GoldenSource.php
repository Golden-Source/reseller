<?php
/**
 * GoldenSource module by Amirhossein Matini (matiniamirhossein@gmail.com) 
 * © All right reserved for GoldenSource Team (GoldenSource.ir)
 */
use WHMCS\View\Menu\Item as MenuItem;
add_hook('ClientAreaPrimaryNavbar', 1, function (MenuItem $primaryNavbar)
{
    global $_LANG;
    if (!is_null($primaryNavbar->getChild('Services'))) {
        $primaryNavbar->getChild('Services')->addChild('MyLicenses', array(
            'label' => 'لایسنس اشتراکی',
            'uri' => 'clientarea.php?action=services&module=GoldenSource',
            'order' => '10',
        ));
    }
});
