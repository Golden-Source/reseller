<?php
/**
 * GoldenSource module by Amirhossein Matini (matiniamirhossein@gmail.com) 
 * © All right reserved for GoldenSource Team (GoldenSource.ir)
 */
require dirname(dirname(dirname(__DIR__))) . '/servers/GoldenSource/include/bootstrap.php';
function GoldenSource_getAssetPath($file = null){
    return '/modules/addons/GoldenSource/assets/' . $file;
}
