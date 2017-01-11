<?php
// $Id: simple_include.php,v 1.6 2008/05/14 10:52:47 charlles.sousa Exp $
//
// This testsuite requires SimpleTest.
// You can find it here:
// http://www.lastcraft.com/simple_test.php
//
if (!defined('SIMPLE_TEST')) {
    define('SIMPLE_TEST', '../simpletest/');
}

require_once(SIMPLE_TEST . 'unit_tester.php');
require_once(SIMPLE_TEST . 'reporter.php');
require_once(SIMPLE_TEST . 'mock_objects.php');
require_once(SIMPLE_TEST . 'web_tester.php');
require_once(SIMPLE_TEST . 'reporter.php');
?>