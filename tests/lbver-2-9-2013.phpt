<?php

/**
 * Test for lbver 2.9.2013 (with weight)
 *
 * @testCase
 */
require_once __DIR__ . '/bootstrap.php';

use Tester\Assert;

class Lb130902Test extends Tester\TestCase
{

    private $subjects;

    public function setUp()
    {
        $html = file_get_contents('test_sources/lbver-2-9-2013-with-weight.htm');
        $bakalariMock = new BakalariGradesAPIMock($html);
        $this->subjects = $bakalariMock->getGrades();
    }

    public function testGrade()
    {
        Assert::type('array', $this->subjects);       
        Assert::type('array', $this->subjects['Český jazyk']);
        Assert::type('array', $this->subjects['Český jazyk'][2]);
        Assert::same('2', $this->subjects['Český jazyk'][2]['grade']);
        Assert::same('4', $this->subjects['Český jazyk'][2]['weight']);
        Assert::same('2014-06-11', $this->subjects['Český jazyk'][2]['date']);
        Assert::same('Zavěrečna pis prace', $this->subjects['Český jazyk'][2]['description']);
    }

}

$testCase = new Lb130902Test();
$testCase->run();
