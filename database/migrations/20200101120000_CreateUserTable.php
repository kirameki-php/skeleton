<?php

use Kirameki\Database\Migration\Migration;
use Kirameki\Database\Schema\Builders\CreateTableBuilder;

class CreateUserTable extends Migration
{
    public function up(): void
    {
        $this->using('main')
            ->createTable('User')->tap(function(CreateTableBuilder $t) {
                $t->uuid('id')->primaryKey()->notNull();
                $t->uuid('token');
                $t->timestamps();
                $t->index('token')->unique();
            });
    }

    public function down(): void
    {
        $this->using('main')
            ->dropTable('User');
    }
}
