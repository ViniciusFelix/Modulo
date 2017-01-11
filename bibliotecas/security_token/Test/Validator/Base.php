<?php
/**
 * $Id: Base.php,v 1.4 2008/09/03 13:51:16 charlles.sousa Exp $
 */
if (!class_exists('Test_Validator_Base')) {
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
     * test class for Validator_Base package
     * @package validation
     * @author matthieu <matthieu@phplibrairies.com>
     * @subpackage unit_test_case
     */
    class Test_Validator_Base extends ShellTestCase
    {
        /**
         * default constructor
         * @access public
         * @return void
         */
        public function __construct()
        {
            parent :: __construct();
        }
        /**
         * test the default validator
         * @acces public
         * @return void
         */
        public function testDefaultValidator()
        {
            $validator = new Validator_Base();
            $this->assertTrue($validator->isValid(),
             "Default validator must always return true");
        }
    }
}