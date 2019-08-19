<?php
/**
 * GoldenSource module by Amirhossein Matini (matiniamirhossein@gmail.com) 
 * © All right reserved for GoldenSource Team (GoldenSource.Pro)
 */
namespace GoldenSourceUI;

use WHMCS\Database\Capsule;
class SessionHelper
{
    public function getChecker(){
        $val = $this->get('checker');
        if(!$val){
            $this->changeChecker();
            return $this->getChecker();
        }
        return $val;
    }

    public function changeChecker(){
        $this->set('checker', substr(sha1(microtime()), 0, 8));
    }

    public function get($key, $default = null)
    {
        $key = sprintf('GoldenSource_%s', $key);
        return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $default;
    }

    public function set($key, $value)
    {
        $key = sprintf('GoldenSource_%s', $key);
        $_SESSION[$key] = $value;
    }
}
