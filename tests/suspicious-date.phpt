<?php

/**
 * Test of suspicious date (lbver 31.8.2012)
 *
 * @testCase
 */
require_once __DIR__ . '/bootstrap.php';

use Tester\Assert;

class SuspiciousDateTest extends Tester\TestCase
{

    private $subjects;

    public function setUp()
    {
        $html = file_get_contents('test_sources/suspicious-date.html');
        $bakalariMock = new BakalariGradesAPIMock($html);
        $this->subjects = $bakalariMock->getGrades();
    }

    public function testGrade()
    {
        Assert::type('array', $this->subjects);
        Assert::type('array', $this->subjects['Anglický jazyk']);
        Assert::type('array', $this->subjects['Anglický jazyk'][0]);
        Assert::same('1', $this->subjects['Anglický jazyk'][0]['grade']);
        Assert::same(null, $this->subjects['Anglický jazyk'][0]['date']);
    }

}

$testCase = new SuspiciousDateTest();
$testCase->run();
