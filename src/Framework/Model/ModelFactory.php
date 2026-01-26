<?php declare(strict_types=1);

namespace Kirameki\Framework\Model;

use Kirameki\App\Models\User;
use Kirameki\Database\DatabaseManager;

class ModelFactory
{
    public function __construct(
        protected readonly ModelManager $modelManager,
        protected readonly DatabaseManager $db,
    ) {
    }

    /**
     * @return QueryBuilder<User>
     */
    public function users(): QueryBuilder
    {
        $this->users()->first()->id;
        return new QueryBuilder(new User($this->modelManager, $this->db));
    }
}
