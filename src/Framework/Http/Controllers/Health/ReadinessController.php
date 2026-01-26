<?php declare(strict_types=1);

namespace Kirameki\Framework\Http\Controllers\Health;

use Kirameki\App\Models\User;
use Kirameki\Framework\Http\Controllers\Controller;
use Kirameki\Framework\Http\HealthCheck;
use Kirameki\Framework\Model\ModelManager;
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
        $user = new User($this->container->make(ModelManager::class));
        $user->id = 2;
        dump($user);

        return file_exists(HealthCheck::READINESS_FILE)
            ? $this->response(StatusCode::OK)
            : $this->response(StatusCode::ServiceUnavailable);
    }
}
