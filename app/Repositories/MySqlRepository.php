<?php 

namespace App\Repositories;

use React\Mysql\MysqlClient;
use React\Mysql\MysqlResult;
use React\Promise\PromiseInterface;

use function React\Async\await;

abstract class MySqlRepository
{
    public function __construct(
        protected readonly MysqlClient $db
    )
    {}

    protected function queryAsync(string $sql, array $params = []): PromiseInterface
    {
        return $this->db->query($sql, $params);
    }

    protected function query (string $sql, array $params = []): MysqlResult
    {
        return await($this->queryAsync($sql, $params));
    }

    protected function fetchOne(string $sql, array $params = []): ?array
    {
        $result = $this->query($sql, $params);
        return $result->resultRows[0] ?? null;
    }

    protected function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->resultRows;
    }

    protected function insert(string $sql, array $params = []): int
    {
        return $this->query($sql, $params)->insertId;
    }
}