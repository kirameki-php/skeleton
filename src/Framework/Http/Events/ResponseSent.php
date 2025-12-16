<?php declare(strict_types=1);

namespace App\Framework\Http\Events;

use Kirameki\Event\Event;
use Kirameki\Http\HttpRequest;
use Kirameki\Http\HttpResponse;

class ResponseSent extends Event
{
    /**
     * @param HttpRequest $request
     * @param HttpResponse $response
     * @param float $elapsedSeconds
     */
    public function __construct(
        public readonly HttpRequest $request,
        public readonly HttpResponse $response,
        public readonly float $elapsedSeconds,
    ) {
    }
}
