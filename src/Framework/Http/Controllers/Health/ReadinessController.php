<?php declare(strict_types=1);

namespace Kirameki\Framework\Http\Controllers\Health;

use Kirameki\Framework\Http\Controllers\Controller;
use Kirameki\Http\HttpResponse;
use Kirameki\Http\StatusCode;
use Override;
use function file_exists;

class ReadinessController extends Controller
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function handle(): HttpResponse
    {
        return file_exists('/run/.kirameki')
            ? $this->response(StatusCode::OK)
            : $this->response(StatusCode::ServiceUnavailable);
    }
}
