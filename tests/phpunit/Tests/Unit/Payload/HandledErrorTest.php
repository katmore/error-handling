<?php

declare(strict_types=1);

namespace Katmore\ErrorHandling\Tests\Unit\Payload;

use Katmore\ErrorHandling\Metadata;
use Katmore\ErrorHandling\Payload;
use Katmore\ErrorHandling\TestCase;

class HandledErrorTest extends TestCase\Unit
{
    public function nrotNCharsetProvider(): array
    {
        return [
            [
                129,
                'asd',
                'foo',
                'foo',
            ],
            [
                -13,
                'abcdefghijklmnopqrstuvwxyz',
                'foo',
                'sbb',
            ]
        ];
    }

    /**
     * @dataProvider nrotNCharsetProvider
     */
    public function testNrotNCharset(int $n, string $charset, string $rotFrom, string $expectedRot): void
    {
        $handledError = new class($rotFrom, $n, $charset) extends Payload\HandledError {
            public $actualRot;

            public function __construct(string $rotFrom, int $n, string $charset)
            {
                $this->actualRot = static::nrot($rotFrom, $n, $charset);
            }

            public function getBacktrace(): Metadata\Backtrace
            {
            }

            protected function withBacktrace(Metadata\Backtrace $backtrace): void
            {
            }

            public function toArray(): array
            {
            }
        };
        $this->assertSame($expectedRot, $handledError->actualRot);
    }

    public function rotValuesProvider(): array
    {
        return [
            [
                'aha',
                'nun'
            ],
            [
                'ant',
                'nag'
            ],
            [
                'clerk',
                'pyrex'
            ],
            [
                '123456789',
                '678901234'
            ],
            [
                '1000000',
                '6555555'
            ]
        ];
    }

    /**
     * @dataProvider rotValuesProvider
     */
    public function testStrRot(string $rotFrom, string $expectedRot): void
    {
        $handledError = new class($rotFrom) extends Payload\HandledError {
            public $actualRot;

            public function __construct(string $rotFrom)
            {
                // var_dump($rotFrom);
                $this->actualRot = static::str_rot($rotFrom);
                // $this->actualRot = static::str_rot($this->actualRot,13);
            }

            public function getBacktrace(): Metadata\Backtrace
            {
            }

            protected function withBacktrace(Metadata\Backtrace $backtrace): void
            {
            }

            public function toArray(): array
            {
            }
        };

        $this->assertSame($expectedRot, $handledError->actualRot);
    }

    public function handledErrorUidProvider(): array
    {
        $data = [];

        $uid = uniqid();

        $handledError = new class($uid) extends Payload\HandledError {
            public function __construct(string $uid)
            {
                $this->uid = $uid;
            }

            public function getBacktrace(): Metadata\Backtrace
            {
            }

            protected function withBacktrace(Metadata\Backtrace $backtrace): void
            {
            }

            public function toArray(): array
            {
            }
        };

        $data[] = [
            $handledError,
            $uid
        ];

        return $data;
    }

    /**
     * @dataProvider handledErrorUidProvider
     */
    public function testUid(Payload\HandledError $handledError, string $uid): void
    {
        $this->assertSame($uid, $handledError->getUid());
    }

    public function handledErrorTimeProvider(): array
    {
        $data = [];

        $time = time();

        $handledError = new class($time) extends Payload\HandledError {
            public function __construct(int $time)
            {
                $this->time = $time;
            }

            public function getBacktrace(): Metadata\Backtrace
            {
            }

            protected function withBacktrace(Metadata\Backtrace $backtrace): void
            {
            }

            public function toArray(): array
            {
            }
        };

        $data[] = [
            $handledError,
            $time
        ];

        return $data;
    }

    /**
     * @dataProvider handledErrorTimeProvider
     */
    public function testTime(Payload\HandledError $handledError, int $time): void
    {
        $this->assertSame($time, $handledError->getTime());
    }

    public function handledErrorDigestProvider(): array
    {
        $data = [];

        $handledErrorArray = [
            'foo' => 'bar'
        ];
        $digest = hash('crc32', json_encode($handledErrorArray, JSON_INVALID_UTF8_IGNORE));

        $handledError = new class($handledErrorArray) extends Payload\HandledError {
            private $handledErrorArray;

            public function __construct(array $handledErrorArray)
            {
                $this->handledErrorArray = $handledErrorArray;
            }

            public function getBacktrace(): Metadata\Backtrace
            {
            }

            protected function withBacktrace(Metadata\Backtrace $backtrace): void
            {
            }

            public function toArray(): array
            {
                return $this->handledErrorArray;
            }
        };

        $data[] = [
            $handledError,
            $digest
        ];

        return $data;
    }

    /**
     * @dataProvider handledErrorDigestProvider
     */
    public function testDigest(Payload\HandledError $handledError, string $digest): void
    {
        $this->assertSame($digest, $handledError->getDigest());
    }

    public function handledErrorReferenceProvider(): array
    {
        $data = [];

        $handledErrorArray = [
            'foo' => 'bar'
        ];
        $digest = hash('crc32', json_encode($handledErrorArray, JSON_INVALID_UTF8_IGNORE));

        $hostCrc = hash('crc32', gethostname());

        $pid = getmypid();

        $rotDigestAlgo = 'pep87';

        $uid = '5d79ba4fac0ec';

        $time = 1568258543;
        $rotTime = 6013703098;

        $reference = "$hostCrc-D$rotDigestAlgo-$digest-P$pid-T$rotTime-$uid";

        $handledError = new class($handledErrorArray, $time, $uid) extends Payload\HandledError {
            private $handledErrorArray;

            public function __construct(array $handledErrorArray, int $time, string $uid)
            {
                $this->handledErrorArray = $handledErrorArray;
                $this->time = $time;
                $this->uid = $uid;
            }

            public function getBacktrace(): Metadata\Backtrace
            {
            }

            protected function withBacktrace(Metadata\Backtrace $backtrace): void
            {
            }

            public function toArray(): array
            {
                return $this->handledErrorArray;
            }
        };

        $data[] = [
            $handledError,
            $reference
        ];

        return $data;
    }

    /**
     * @dataProvider handledErrorReferenceProvider
     */
    public function testReference(Payload\HandledError $handledError, string $reference): void
    {
        $this->assertSame($reference, $handledError->getReference());
        
    }
}
