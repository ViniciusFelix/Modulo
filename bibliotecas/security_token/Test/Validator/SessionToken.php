<?php
/**
 * $Id: SessionToken.php,v 1.4 2008/09/03 13:51:16 charlles.sousa Exp $
 */
if (!class_exists('Test_Validator_SessionToken')) {
    if (!defined('__CLASS_PATH__')) {
        define('__CLASS_PATH__', realpath(dirname(__FILE__) . '/../../'));
    }
    if (!defined('__SIMPLETEST_PATH__')) {
        define('__SIMPLETEST_PATH__', realpath(__CLASS_PATH__ . '/simpletest/'));
    }
    require_once __CLASS_PATH__ . '/Autoload.php';
    require_once __SIMPLETEST_PATH__ . '/shell_tester.php';
    require_once __SIMPLETEST_PATH__ . '/reporter.php';
    /**
     * unit test case for ValidateSessionToken
     * @author matthieu <matthieu@phplibrairies.com>
     * @package validation
     * @subpackage unit_test_case
     */
    class Test_Validator_SessionToken extends ShellTestCase
    {
        /**
         * default constructor
         * @access public
         * @return void
         */
        public function __construct()
        {
            parent :: __construct();
            session_start();
        }
        /**
         * destructor
         * @access public
         * @return void
         */
        public function __destruct()
        {
            session_write_close();
        }
        /**
         * test for an empty token
         * @access public
         * @return void
         */
        public function testEmptyToken()
        {
            $token = new Security_Token();
            $token->save($_SESSION);
            $validator = new Validator_SessionToken('');
            $this->assertFalse($validator->isValid(),
             'Unexpected validation for an empty token');
            unset ($token);
            unset ($validator);
        }
        /**
         * test a valid token
         * @access public
         * @return void
         */
        public function testValidToken()
        {
            $token = new Security_Token();
            $token->save($_SESSION);
            $validator = new Validator_SessionToken($token->getToken());
            $this->assertTrue($validator->isValid(),
             'Token is not validate : ' . $validator->getError());
            unset ($token);
            unset ($validator);
        }
        /**
         * test an unvalid token
         * @access public
         * @return void
         */
        public function testUnvalidToken()
        {
            $token = new Security_Token();
            $token->save($_SESSION);
            $new_token = 'zazazazazazazazazazaz';
            $validator = new Validator_SessionToken($new_token);
            $this->assertFalse($validator->isValid(),
             'Unexpected validation for an a new token (' . $new_token . '), expected (' . $token->getToken() . ')');
            unset ($token);
            unset ($validator);
        }
    }
}