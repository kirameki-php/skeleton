<?php

namespace Kirameki\Framework\Crypto;

use Hidehalo\Nanoid\Client;

class NanoIdGenerator
{
    /**
     * @param Client $client
     */
    public function __construct(
        protected Client $client = new Client(),
    ) {
    }

    /**
     * @param int $size
     * @return string
     */
    public function generate(int $size = 12): string
    {
        return $this->client->generateId($size, Client::MODE_DYNAMIC);
    }
}
