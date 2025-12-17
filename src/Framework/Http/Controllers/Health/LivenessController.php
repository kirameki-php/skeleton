<?php declare(strict_types=1);

namespace Kirameki\Framework\Http\Controllers\Health;

use Kirameki\Framework\Http\Controllers\Controller;
use Kirameki\Http\HttpResponse;
use Override;

class LivenessController extends Controller
{
    #[Override]
    public function handle(): HttpResponse
    {
        return $this->response();
    }
}
