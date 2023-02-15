<?php

declare(strict_types=1);

final class Connection
{
    private mysqli $connection;

    public function __construct()
    {
        $this->connection = new mysqli(
            Database::HOST,
            Database::USERNAME,
            Database::PASSWORD,
            Database::DATABASE,
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function selectFirst(string $query): array
    {
        $result = $this->select($query);

        if (0 === count($result)) {
            throw new RuntimeException('No results');
        }

        return $result[0];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function select(string $query): array
    {
        $result = $this->connection->query($query);

        if (false === $result) {
            throw new RuntimeException('Invalid select query');
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function insert(string $query): int
    {
        $this->connection->query($query);

        return $this->connection->insert_id;
    }

    public function delete(string $query): void
    {
        $this->connection->query($query);
    }
}