<?php

declare(strict_types=1);

namespace Nsq;

use function array_map;
use function implode;
use function pack;
use function sprintf;
use const PHP_EOL;

final class Writer
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @psalm-suppress PossiblyFalseOperand
     */
    public function pub(string $topic, string $body): void
    {
        $size = pack('N', \strlen($body));

        $buffer = 'PUB '.$topic.PHP_EOL.$size.$body;

        $this->connection->write($buffer);
        $this->connection->read();
    }

    /**
     * @psalm-param array<mixed, mixed> $bodies
     *
     * @psalm-suppress PossiblyFalseOperand
     */
    public function mpub(string $topic, array $bodies): void
    {
        $num = pack('N', \count($bodies));

        $mb = implode('', array_map(static function ($body): string {
            return pack('N', \strlen($body)).$body;
        }, $bodies));

        $size = pack('N', \strlen($num.$mb));

        $buffer = 'MPUB '.$topic.PHP_EOL.$size.$num.$mb;

        $this->connection->write($buffer);
        $this->connection->read();
    }

    /**
     * @psalm-suppress PossiblyFalseOperand
     */
    public function dpub(string $topic, int $deferTime, string $body): void
    {
        $size = pack('N', \strlen($body));

        $buffer = sprintf('DPUB %s %s', $topic, $deferTime).PHP_EOL.$size.$body;

        $this->connection->write($buffer);
        $this->connection->read();
    }
}