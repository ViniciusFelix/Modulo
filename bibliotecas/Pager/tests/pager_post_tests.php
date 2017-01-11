<?php
// $Id: pager_post_tests.php,v 1.6 2008/05/14 10:52:47 charlles.sousa Exp $

require_once 'simple_include.php';
require_once 'pager_include.php';

$test = &new GroupTest('Pager POST tests');
$test->addTestFile('pager_post_test.php');
exit ($test->run(new HTMLReporter()) ? 0 : 1);

?>