<?php
/**
 * $Id: Token.php,v 1.4 2008/09/03 13:51:15 charlles.sousa Exp $
 */
if (!class_exists('Security_Token')) {
    if (!defined('__CLASS_PATH__')) {
        define('__CLASS_PATH__', realpath(dirname(__FILE__) . '/../'));
    }
    require_once __CLASS_PATH__ . '/Autoload.php';
    /**
     * @author Matthieu MARY <matthieu@phplibrairies.com>
     * @license http://opensource.org/licenses/gpl-license.php GNU Public License
     */
    class Security_Token
    {
        /**
         * @access private
         * @var string $_token
         */
        private $_token = '';
        /**
         * constructor
         * @access public
         * @return void
         */
        public function __construct($token = null)
        {
            $this->_token = (is_null($token)) ? md5(time()) : $token;
        }
        /**
         * @access public
         * @return string
         */
        public function getToken()
        {
            return $this->_token;
        }
        /**
         * @access public
         * @param array $destination : the destination array
         * @access public
         */
        public function save(& $destination)
        {
            $destination['token'] = $this->_token;
        }
        /**
         * serialise the token
         * @access public
         * @return string
         */
        public function __toString()
        {
            $formHidden = new Form_Hidden('token');
            $formHidden->setValue($this->_token);
            return $formHidden->__toString();
        }
    }
}