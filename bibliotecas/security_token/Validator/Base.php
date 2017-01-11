<?php
/**
 * $Id: Base.php,v 1.4 2008/09/03 13:51:16 charlles.sousa Exp $
 */
if (!class_exists('Validator_Base')) {
    /**
     * generic validator class
     * @author matthieu <matthieu@phplibrairies.com>
     * @package validation
     * @license http://opensource.org/licenses/gpl-license.php GPL
     */
    class Validator_Base
    {
        /**
         * @var array $_errorsMessages the error messages
         * @access private
         */
        private $_errorsMessages = array ();
        /**
         * validator constructor
         * @access public
         * @return void
         */
        public function __construct()
        {
            $this->_errorsMessages = array ();
            $this->validate();
        }
        /**
         * validate an object
         * @access private
         * @return void
         * for the superclass, does nothing
         */
        private function validate()
        {
        }
        /**
         * check if is valid
         * @return boolean
         * @access public
         */
        public function isValid()
        {
            return empty ($this->_errorsMessages);
        }
        /**
         * pop the last error message
         * @access public
         * @return string
         */
        public function getError()
        {
            return array_pop($this->_errorsMessages);
        }
        /**
         * add an error
         * @param string $errorMessage: the error to set
         * @access protected
         * @return void
         */
        protected function setError($errorMessage)
        {
            $this->_errorsMessages[] = $errorMessage;
        }
    }
}