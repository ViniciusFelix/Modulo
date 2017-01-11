<?php
/**
 * $Id: Token.php,v 1.4 2008/09/03 13:51:16 charlles.sousa Exp $
 */
if (!class_exists('Validator_Token')) {
    if (!defined('__CLASS_PATH__')) {
        define('__CLASS_PATH__', realpath(dirname(__FILE__) . '/../'));
    }
    require_once __CLASS_PATH__ . '/Autoload.php';
    /**
     * validate a token
     * @author Matthieu MARY <matthieu@phplibrairies.com>
     * @license http://opensource.org/licenses/gpl-license.php GPL
     * @package validation
     */
    class Validator_Token extends Validator_Base
    {
        /**
         * @var string $_tokenToControl : the token value to control
         * @access protected
         */
        protected $_tokenToControl = '';
        /**
         * @var array $_source : the source containing the valid token value
         * @access protected
         */
        protected $_source = array ();
        /**
         * @param string $token_to_validate : the token value to control
         * @param array $source : the source array where match the expected
         * token
         * @access public
         * @return void
         */
        public function __construct($tokenToValidate, $source = array ())
        {
            $this->_tokenToControl = strval($tokenToValidate);
            $this->_source = & $source;
            parent :: __construct();
            $this->validate();
        }
        /**
         * validate the token
         * @access private
         * @return void
         */
        private function validate()
        {
            if (empty ($this->_tokenToControl)) {
                $this->setError('Token is empty');
            } else {
                if (!isset ($this->_source['token'])) {
                    $this->setError('token compare value is not set');
                } else {
                    if (!(strcasecmp($this->_source['token'], $this->_tokenToControl) == 0)) {
                        $this->setError('Token are differents');
                    }
                }
            }
        }
    }
}