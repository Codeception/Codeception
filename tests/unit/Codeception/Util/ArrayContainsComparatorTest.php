<?php
namespace Codeception\Util;

class ArrayContainsComparatorTest extends \Codeception\Test\Unit
{
    /**
     * @var ArrayContainsComparator
     */
    protected $ary;

    protected function _before()
    {
        $this->ary = new ArrayContainsComparator(
            [
                'ticket' => [
                    'title'  => 'Bug should be fixed',
                    'user'   => ['name' => 'Davert'],
                    'labels' => null
                ]
            ]
        );
    }

    // tests
    public function testInclusion()
    {
        $this->assertTrue($this->ary->containsArray(['name' => 'Davert']));
        $this->assertTrue($this->ary->containsArray(['user' => ['name' => 'Davert']]));
        $this->assertTrue($this->ary->containsArray(['ticket' => ['title' => 'Bug should be fixed']]));
        $this->assertTrue($this->ary->containsArray(['ticket' => ['user' => ['name' => 'Davert']]]));
        $this->assertTrue($this->ary->containsArray(['ticket' => ['labels' => null]]));
    }

    /**
     * @Issue https://github.com/Codeception/Codeception/issues/2070
     */
    public function testContainsArrayComparesArrayWithMultipleZeroesCorrectly()
    {
        $comparator = new ArrayContainsComparator([
            'responseCode' => 0,
            'message' => 'OK',
            'data' => [9, 0, 0],
        ]);

        $expectedArray = [
            'responseCode' => 0,
            'message' => 'OK',
            'data' => [0, 0, 0],
        ];

        $this->assertFalse($comparator->containsArray($expectedArray));
    }

    public function testContainsArrayComparesArrayWithMultipleIdenticalSubArraysCorrectly()
    {
        $comparator = new ArrayContainsComparator([
            'responseCode' => 0,
            'message' => 'OK',
            'data' => [[9], [0], [0]],
        ]);

        $expectedArray = [
            'responseCode' => 0,
            'message' => 'OK',
            'data' => [[0], [0], [0]],
        ];

        $this->assertFalse($comparator->containsArray($expectedArray));
    }

    public function testContainsArrayComparesArrayWithValueRepeatedMultipleTimesCorrectlyNegativeCase()
    {
        $comparator = new ArrayContainsComparator(['foo', 'foo', 'bar']);
        $expectedArray = ['foo', 'foo', 'foo'];
        $this->assertFalse($comparator->containsArray($expectedArray));
    }

    public function testContainsArrayComparesArrayWithValueRepeatedMultipleTimesCorrectlyPositiveCase()
    {
        $comparator = new ArrayContainsComparator(['foo', 'foo', 'bar']);
        $expectedArray = ['foo', 'bar', 'foo'];
        $this->assertTrue($comparator->containsArray($expectedArray));
    }
    /**
     * @issue https://github.com/Codeception/Codeception/issues/2630
     */
    public function testContainsArrayComparesNestedSequentialArraysCorrectlyWhenSecondValueIsTheSame()
    {
        $array = [
            ['2015-09-10', 'unknown-date-1'],
            ['2015-10-10', 'unknown-date-1'],
        ];
        $comparator = new ArrayContainsComparator($array);
        $this->assertTrue($comparator->containsArray($array));
    }

    /**
     * @issue https://github.com/Codeception/Codeception/issues/2630
     * @codingStandardsIgnoreStart
     */
    public function testContainsArrayComparesNestedSequentialArraysCorrectlyWhenSecondValueIsTheSameButOrderOfItemsIsDifferent()
    {
        // @codingStandardsIgnoreEnd
        $comparator = new ArrayContainsComparator([
            [
                "2015-09-10",
                "unknown-date-1"
            ],
            [
                "2015-10-10",
                "unknown-date-1"
            ]
        ]);
        $expectedArray = [
            ["2015-10-10", "unknown-date-1"],
            ["2015-09-10", "unknown-date-1"],
        ];
        $this->assertTrue($comparator->containsArray($expectedArray));
    }

    /**
     * @issue https://github.com/Codeception/Codeception/issues/2630
     */
    public function testContainsArrayComparesNestedSequentialArraysCorrectlyWhenSecondValueIsDifferent()
    {
        $comparator = new ArrayContainsComparator([
            [
                "2015-09-10",
                "unknown-date-1"
            ],
            [
                "2015-10-10",
                "unknown-date-2"
            ]
        ]);
        $expectedArray = [
            ["2015-09-10", "unknown-date-1"],
            ["2015-10-10", "unknown-date-2"],
        ];
        $this->assertTrue($comparator->containsArray($expectedArray));
    }

    /**
     * @issue https://github.com/Codeception/Codeception/issues/2630
     */
    public function testContainsArrayComparesNestedSequentialArraysCorrectlyWhenJsonHasMoreItemsThanExpectedArray()
    {
        $comparator = new ArrayContainsComparator([
            [
                "2015-09-10",
                "unknown-date-1"
            ],
            [
                "2015-10-02",
                "unknown-date-1"
            ],
            [
                "2015-10-10",
                "unknown-date-2"
            ]
        ]);
        $expectedArray = [
            ["2015-09-10", "unknown-date-1"],
            ["2015-10-10", "unknown-date-2"],
        ];
        $this->assertTrue($comparator->containsArray($expectedArray));
    }

    /**
     * @issue https://github.com/Codeception/Codeception/pull/2635
     */
    public function testContainsMatchesSuperSetOfExpectedAssociativeArrayInsideSequentialArray()
    {
        $comparator = new ArrayContainsComparator([[
                'id' => '1',
                'title' => 'Game of Thrones',
                'body' => 'You are so awesome',
                'created_at' => '2015-12-16 10:42:20',
                'updated_at' => '2015-12-16 10:42:20',
            ]]);
        $expectedArray = [['id' => '1']];
        $this->assertTrue($comparator->containsArray($expectedArray));
    }

    /**
     * @issue https://github.com/Codeception/Codeception/issues/2630
     */
    public function testContainsArrayWithUnexpectedLevel()
    {
        $comparator = new ArrayContainsComparator([
            "level1" => [
                "level2irrelevant" => [],
                "level2" => [
                    [
                        "level3" => [
                            [
                                "level5irrelevant1" => "a1",
                                "level5irrelevant2" => "a2",
                                "level5irrelevant3" => "a3",
                                "level5irrelevant4" => "a4",
                                "level5irrelevant5" => "a5",
                                "level5irrelevant6" => "a6",
                                "level5irrelevant7" => "a7",
                                "level5irrelevant8" => "a8",
                                "int1" => 1
                            ]
                        ],
                        "level3irrelevant" => [
                            "level4irrelevant" => 1
                        ]
                    ],
                    [
                        "level3" => [
                            [
                                "level5irrelevant1" => "b1",
                                "level5irrelevant2" => "b2",
                                "level5irrelevant3" => "b3",
                                "level5irrelevant4" => "b4",
                                "level5irrelevant5" => "b5",
                                "level5irrelevant6" => "b6",
                                "level5irrelevant7" => "b7",
                                "level5irrelevant8" => "b8",
                                "int1" => 1
                            ]
                        ],
                        "level3irrelevant" => [
                            "level4irrelevant" => 2
                        ]
                    ]
                ]
            ]
        ]);

        $expectedArray = [
            'level1' => [
                'level2' => [
                    [
                        'int1' => 1,
                    ],
                    [
                        'int1' => 1,
                    ],
                ]
            ]
        ];

        $this->assertTrue(
            $comparator->containsArray($expectedArray),
            "- <info>" . var_export($expectedArray, true) . "</info>\n"
            . "+ " . var_export($comparator->getHaystack(), true)
        );
    }

    /**
     * Simplified testcase for issue reproduced by testContainsArrayWithUnexpectedLevel
     */
    public function testContainsArrayComparesSequentialArraysHavingDuplicateSubArraysCorrectly()
    {
        $comparator = new ArrayContainsComparator([[1],[1]]);
        $expectedArray = [[1],[1]];
        $this->assertTrue(
            $comparator->containsArray($expectedArray),
            "- <info>" . var_export($expectedArray, true) . "</info>\n"
            . "+ " . var_export($comparator->getHaystack(), true)
        );
    }
}
