<?php 

namespace App\UnitOfWork;

use React\Mysql\MysqlClient;

use function React\Async\await;

class MySqlUnitOfWork implements UnitOfWorkInterface
{
    public function __construct(
        private readonly MysqlClient $db
    ) {}

    public function run(callable $callback): mixed
    {
        await($this->db->query('START TRANSACTION'));

        try { 
            $result = $callback();
            await($this->db->query('COMMIT'));
            return $result;
        } catch (\Throwable $e) {
            await($this->db->query('ROLLBACK'));
            throw $e;
        }
    }
}