<?php

namespace Bdf\Serializer;

use PHPUnit\Framework\TestCase;

/**
 * @group Bdf_Serializer
 */
class RecursionTest extends TestCase
{
    /**
     *
     */
    public function test_serialize()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = [
            'name' => 'root',
            'nodes' => [
                [
                    'name' => '1',
                    'nodes' => [],
                ],
                [
                    'name' => '2',
                    'nodes' => [
                        [
                            'name' => '3',
                            'nodes' => [],
                        ],
                    ],
                ],
            ],
        ];

        $root = new Node('root');
        $root->nodes[] = new Node('1');
        $root->nodes[] = new Node('2');
        $root->nodes[1]->nodes[] = new Node('3');

        $result = $serializer->serialize($root, 'json');

        $this->assertEquals(json_encode($data), $result);
    }

    /**
     *
     */
    public function test_deserialize()
    {
        $serializer = SerializerBuilder::create()->build();

        $data = [
            'name' => 'root',
            'nodes' => [
                [
                    'name' => '1',
                    'nodes' => [],
                ],
                [
                    'name' => '2',
                    'nodes' => [
                        [
                            'name' => '3',
                            'nodes' => [],
                        ],
                    ],
                ],
            ],
        ];

        $root = new Node('root');
        $root->nodes[] = new Node('1');
        $root->nodes[] = new Node('2');
        $root->nodes[1]->nodes[] = new Node('3');

        $this->assertEquals($root, $serializer->fromArray($data, Node::class));
    }
}

//---------

class Node
{
    public $name;

    /**
     * @var Node[]
     */
    public $nodes = [];

    public function __construct($name)
    {
        $this->name = $name;
    }
}
