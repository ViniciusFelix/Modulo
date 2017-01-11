<?php
// $Id: pager_tests.php,v 1.6 2008/05/14 10:52:47 charlles.sousa Exp $

require_once 'simple_include.php';
require_once 'pager_include.php';

class PagerTests extends GroupTest {
    function PagerTests() {
        $this->GroupTest('Pager Tests');
        $this->addTestFile('pager_test.php');
        $this->addTestFile('pager_noData_test.php');
    }
}

if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new PagerTests();
    $test->run(new HtmlReporter());
}
?>