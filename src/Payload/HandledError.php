<?php

declare(strict_types=1);

namespace Katmore\ErrorHandling\Payload;

use Katmore\ErrorHandling\ {
    Component,
    Metadata
};

abstract class HandledError implements Component\ArraySerializableComponent
{
    use Component\ArraySerializableComponentTrait;

    public const DIGEST_ALGO = 'crc32';
    public const DIGEST_ALGO_ID = [
        'crc32'=>'01',
    ];

    public const REFERENCE_FORMAT = 'H%host-crc%-D%digest-algo-id%digest%-P%pid%-T%rot-timestamp%-U%uid%';

    /**
     * @var string unique id in lowercase hexits
     */
    protected $uid;

    /**
     * @var int unix timestamp of error occurance
     */
    protected $time;

    protected static function nrot(string $s, int $n, string $charset): string
    {
        $clen = strlen($charset);
        $n %= $clen;
        if (!$n) {
            return $s;
        }
        if ($n < 0) {
            $n += $clen;
        }
        $rep = substr($charset, $n) . substr($charset, 0, $n);
        return strtr($s, $charset, $rep);
    }

    protected static function str_rot(string $str): string
    {
        $str = static::nrot($str, 13, 'abcdefghijklmnopqrstuvwxyz');
        $str = static::nrot($str, 13, strtoupper('abcdefghijklmnopqrstuvwxyz'));
        $str = static::nrot($str, 5, '0123456789');
        return $str;
    }

    /**
     * Get the error unique id in lowercase hexits
     *
     * @return string The error unique id in lowercase hexits
     */
    public function getUid(): string
    {
        return $this->uid;
    }

    /**
     * Get the unix timestamp of error occurance
     *
     * @return int The unix timestamp of error occurance
     */
    public function getTime(): int
    {
        return $this->time;
    }

    /**
     * Get the error digest in lowercase hexits
     *
     * @return string The error digest in lowercase hexits
     */
    public function getDigest(): string
    {
        return hash(static::DIGEST_ALGO, json_encode($this, JSON_INVALID_UTF8_IGNORE));
    }

    /**
     * Get the error reference string
     *
     * @see HandledError::REFERENCE_FORMAT
     *
     * @return string The error reference string
     */
    public function getReference(): string
    {
        if (false === ($hostCrc = gethostname())) {
            // @codeCoverageIgnoreStart
            $hostCrc = 'unknown.local';
            // @codeCoverageIgnoreEnd
        }
        $hostCrc = hash('crc32', $hostCrc);
        if (false === $pid = getmypid()) {
            // @codeCoverageIgnoreStart
            $pid = -1;
            // @codeCoverageIgnoreEnd
        }

        $rotDigestAlgo = static::str_rot(static::DIGEST_ALGO);
        $digest = $this->getDigest();
        $rotTimestamp = static::str_rot((string) $this->time);
        $uid = $this->getUid();

        $ref = static::REFERENCE_FORMAT;

        $ref = str_replace('%host-crc%', $hostCrc, $ref);
        $ref = str_replace('%pid%', $pid, $ref);
        $ref = str_replace('%rot-digest-algo%', $rotDigestAlgo, $ref);
        $ref = str_replace('%digest%', $digest, $ref);
        $ref = str_replace('%rot-timestamp%', $rotTimestamp, $ref);
        $ref = str_replace('%uid%', $uid, $ref);

        return $ref;
    }
    
    /**
     * Get a brief description of the error type
     *
     * @return string A brief description of the error type
     */
    abstract public function getTypeDesc(): string;

    abstract public function getBacktrace(): Metadata\Backtrace;

    abstract protected function withBacktrace(Metadata\Backtrace $backtrace);
}
