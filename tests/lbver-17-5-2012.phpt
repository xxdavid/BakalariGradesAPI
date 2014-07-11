<?php

/**
 * Test for lbver 17.5.2012 (without weight)
 *
 * @testCase
 */
require_once __DIR__ . '/bootstrap.php';

use Tester\Assert;

class Lb120517Test extends Tester\TestCase
{

    private $subjects;

    public function setUp()
    {
        $html = file_get_contents('test_sources/lbver-17-5-2012-without-weight.html');
        $bakalariMock = new BakalariGradesAPIMock($html);
        $this->subjects = $bakalariMock->getGrades();
    }

    public function testGrade()
    {
        Assert::type('array', $this->subjects);
        Assert::type('array', $this->subjects['Český jazyk']);
        Assert::type('array', $this->subjects['Český jazyk'][0]);
        Assert::same('1', $this->subjects['Český jazyk'][0]['grade']);
        Assert::same(null, $this->subjects['Český jazyk'][0]['weight']);
        Assert::same('2013-02-18', $this->subjects['Český jazyk'][0]['date']);
        Assert::same('Pravopis vlast. jména', $this->subjects['Český jazyk'][0]['description']);
    }

}

$testCase = new Lb120517Test();
$testCase->run();
