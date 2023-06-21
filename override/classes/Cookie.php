<?php
/**
* 2007-2021 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class Cookie extends CookieCore
{

    /**
     * Setcookie according to php version
     */
    protected function _setcookie($cookie = null)
    {
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            if ($cookie) {
                $content = $this->_cipherTool->encrypt($cookie);
                $time = $this->_expire;
            } else {
                $content = 0;
                $time = 1;
            }
            if (PHP_VERSION_ID <= 50200) { 
                return setcookie($this->_name, $content, $time, $this->_path, $this->_domain, $this->_secure);
            } else {
                if (PHP_VERSION_ID >= 70300) {
                    return setcookie($this->_name, $content,
                        [
                            'expires'  => $time,
                            'path'     => $this->_path,
                            'domain'   => $this->_domain,
                            'samesite' => 'None',
                            'secure'   => $this->_secure,
                            'httponly' => true
                        ]
                    );
                } else {
                    
                    return setcookie($this->_name, $content, $time, $this->_path.'; SameSite=none', $this->_domain, true, true);
                }
            }
        } else {
            return $this->encryptAndSetCookie($cookie);
        }
    }

    protected function encryptAndSetCookie($cookie = null)
    {
        // Check if the content fits in the Cookie
        $length = (ini_get('mbstring.func_overload') & 2) ? mb_strlen($cookie, ini_get('default_charset')) : strlen($cookie);
        if ($length >= 1048576) {
            return false;
        }
        if ($cookie) {
            $content = $this->cipherTool->encrypt($cookie);
            $time = $this->_expire;
        } else {
            $content = 0;
            $time = 1;
        }

        if (PHP_VERSION_ID >= 70300) {
            return setcookie($this->_name, $content,
                [
                    'expires'  => $time,
                    'path'     => $this->_path,
                    'domain'   => $this->_domain,
                    'samesite' => 'None',
                    'secure'   => $this->_secure,
                    'httponly' => true
                ]
            );
        } else {
            
            return setcookie($this->_name, $content, $time, $this->_path.'; SameSite=none', $this->_domain, true, true);
        }
    }
}