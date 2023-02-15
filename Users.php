<?php

declare(strict_types=1);


final class Users
{
    private Connection $connection;
    /**
     * @var int[]
     */
    private array $userIds;

    public function __construct(Connection $connection, string $name, string $operator, string $value)
    {
        $this->connection = $connection;

        $this->restoreIdsFromDb($name, $operator, $value);
    }

    /**
     * @return User[]
     */
    public function getUsers(): array
    {
        return array_map(
            fn (int $userId): User => new User($this->connection, $userId),
            $this->userIds,
        );
    }

    public function deleteUsers(): void
    {
        foreach ($this->userIds as $userId) {
            $user = new User($this->connection, $userId);

            $user->delete();
        }

        $this->userIds = [];
    }

    private function restoreIdsFromDb(string $name, string $operator, string $value): void
    {
        $users = $this->connection->select(sprintf('SELECT * FROM user WHERE %s %s "%s"', $name, $operator, $value));

        $this->userIds = array_map(
            static fn (array $user): int => (int) $user['id'],
            $users,
        );
    }
}
