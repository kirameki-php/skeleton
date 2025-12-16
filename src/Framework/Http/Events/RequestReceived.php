<?php declare(strict_types=1);

namespace App\Framework\Http\Events;

use Kirameki\Event\Event;
use Kirameki\Http\HttpRequest;

class RequestReceived extends Event
{
    /**
     * @param HttpRequest $request
     */
    public function __construct(
        public readonly HttpRequest $request,
    ) {
    }
}
