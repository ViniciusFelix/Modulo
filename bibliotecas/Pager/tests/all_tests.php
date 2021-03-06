<?php
// $Id: all_tests.php,v 1.6 2008/05/14 10:52:47 charlles.sousa Exp $

require_once 'simple_include.php';
require_once 'pager_include.php';

define('TEST_RUNNING', true);

require_once './pager_tests.php';
require_once './pager_jumping_tests.php';
require_once './pager_sliding_tests.php';


class AllTests extends GroupTest {
    function AllTests() {
        $this->GroupTest('All PEAR::Pager Tests');
        $this->AddTestCase(new PagerTests());
        $this->AddTestCase(new PagerJumpingTests());
        $this->AddTestCase(new PagerSlidingTests());
    }
}

$test = &new AllTests();
$test->run(new HtmlReporter());
?>