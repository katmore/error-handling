<?php

declare(strict_types=1);

namespace Katmore\ErrorHandling\Tests\Unit\Metadata;

use Katmore\ErrorHandling\Metadata;
use Katmore\ErrorHandling\TestCase;

class BacktraceTest extends TestCase\Unit
{
    public function backtraceArrayProvider(): array
    {
        $backtrace = [
            [
                'file' => '/tmp/a.php',
                'line' => 10,
                'function' => 'fooA',
                'args' => [
                    'barA'
                ]
            ],
            [
                'file' => '/tmp/b.php',
                'line' => 20,
                'function' => 'fooB',
                'args' => [
                    'barB'
                ]
            ],
            [
                'file' => '/tmp/c.php',
                'line' => 30,
                'function' => 'include',
                'args' => [
                    '/tmp/d.php'
                ]
            ],
            [
                'file' => '/tmp/d.php',
                'line' => 40,
                'function' => 'require',
                'args' => [
                    '/tmp/e.php'
                ]
            ]
        ];

        return [
            [
                $backtrace
            ]
        ];
    }

    /**
     * @dataProvider backtraceArrayProvider
     */
    public function testCreateBacktrace(array $backtraceArray): Metadata\Backtrace
    {
        $factory = new Metadata\BacktraceFactory();

        $factory->setBacktraceArray($backtraceArray);

        $backtrace = $factory->createBacktrace();

        $this->assertSame(count($backtraceArray), count($backtrace));

        return $backtrace;
    }
}
