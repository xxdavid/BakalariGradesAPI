<?php

/**
 * Test for lbver 31.8.2012 (with weight)
 *
 * @testCase
 */
require_once __DIR__ . '/bootstrap.php';

use Tester\Assert;

class Lb120831Test extends Tester\TestCase
{

    private $subjects;

    public function setUp()
    {
        $html = file_get_contents('test_sources/lbver-31-8-2012-with-weight.html');
        $bakalariMock = new BakalariGradesAPIMock($html);
        $this->subjects = $bakalariMock->getGrades();
    }

    public function testGrade()
    {
        Assert::type('array', $this->subjects);
        Assert::type('array', $this->subjects['Základy společenských věd']);
        Assert::type('array', $this->subjects['Základy společenských věd'][3]);
        Assert::same('2', $this->subjects['Základy společenských věd'][3]['grade']);
        Assert::same('7', $this->subjects['Základy společenských věd'][3]['weight']);
        Assert::same('2012-12-19', $this->subjects['Základy společenských věd'][3]['date']);
        Assert::same('úvod do teorie práva', $this->subjects['Základy společenských věd'][3]['description']);
        Assert::same(null, $this->subjects['Dějepis'][0]['date']);
    }

}

$testCase = new Lb120831Test();
$testCase->run();
