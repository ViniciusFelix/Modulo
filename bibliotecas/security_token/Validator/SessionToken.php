<?php
/**
 * $Id: SessionToken.php,v 1.4 2008/09/03 13:51:16 charlles.sousa Exp $
 */
if (!class_exists('Validator_SessionToken')) {
    if (!defined('__CLASS_PATH__')) {
        define('__CLASS_PATH__', realpath(dirname(__FILE__) . '/../'));
    }
    require_once __CLASS_PATH__ . '/Autoload.php';
    /**
     * validate a token stored in session
     * @author Matthieu MARY <matthieu@phplibrairies.com>
     * @license http://opensource.org/licenses/gpl-license.php GPL
     * @package validation
     */
    class Validator_SessionToken extends Validator_Token
    {
        /**
         * constructor
         * @access public
         * @return void
         * @param string $tokenToValidate : the token to control
         */
        public function __construct($tokenToValidate)
        {
            parent :: __construct($tokenToValidate, $_SESSION);
        }
    }
}