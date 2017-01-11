<?php
/**
 * $Id: Base.php,v 1.4 2008/09/03 13:51:16 charlles.sousa Exp $
 */
if (!defined('__CLASS_PATH__')) {
    define('__CLASS_PATH__', realpath(dirname(__FILE__) . '/../../'));
}
require_once __CLASS_PATH__. '/Autoload.php';
/**
 * launcher for unit test case Test_Validator_Base
 * @author Matthieu MARY <matthieu@phplibrairies.com>
 * @package validation
 * @subpackage unit_test_case
 */
 $test = new Test_Validator_Base();
 $test->run(new TextReporter());
?>
