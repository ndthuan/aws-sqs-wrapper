<?php
declare(strict_types=1);

namespace Ndthuan\AwsSqsWrapper\Queue;

class ResultMetadata
{
    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var string
     */
    private $effectiveUri;

    /**
     * @var array
     */
    private $headers;

    /**
     * @var array
     */
    private $transferStats;

    /**
     * @param array $metadata
     *
     * @return static
     */
    public static function fromArray(array $metadata)
    {
        return new self(
            $metadata['statusCode'] ?? 0,
            $metadata['effective'] ?? '',
            $metadata['headers'] ?? [],
            $metadata['transferStats'] ?? []
        );
    }

    /**
     * ResultMetadata constructor.
     *
     * @param int    $statusCode
     * @param string $effectiveUri
     * @param array  $headers
     * @param array  $transferStats
     */
    public function __construct(int $statusCode, string $effectiveUri, array $headers, array $transferStats)
    {
        $this->statusCode    = $statusCode;
        $this->effectiveUri  = $effectiveUri;
        $this->headers       = $headers;
        $this->transferStats = $transferStats;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return string
     */
    public function getEffectiveUri(): string
    {
        return $this->effectiveUri;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return array
     */
    public function getTransferStats(): array
    {
        return $this->transferStats;
    }
}
