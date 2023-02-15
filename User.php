<?php

declare(strict_types=1);

final class User
{
    private Connection $connection;
    private ?int $id;
    private ?string $firstName;
    private ?string $lastName;
    private ?DateTimeImmutable $dateOfBirth;
    private ?int $sex;
    private ?string $cityOfBirth;

    public static function getAgeByDateOfBirth(DateTimeImmutable $dateOfBrith): int
    {
        $dateOfBrithTimestamp = strtotime($dateOfBrith->format('Y-m-d'));
        $age = date('Y') - date('Y', $dateOfBrithTimestamp);

        if (date('md', $dateOfBrithTimestamp) > date('md')) {
            $age--;
        }

        return $age;
    }

    public static function getSexString(int $sex): string
    {
        return 0 === $sex ? 'муж' : 'жен';
    }

    public function __construct(
        Connection $connection,
        ?int $id = null,
        ?string $firstName = null,
        ?string $lastName = null,
        ?DateTimeImmutable $dateOfBirth = null,
        ?int $sex = null,
        ?string $cityOfBirth = null,
    ) {
        $this->connection = $connection;
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->dateOfBirth = $dateOfBirth;
        $this->sex = $sex;
        $this->cityOfBirth = $cityOfBirth;

        if (null === $this->id) {
            $this->save();
        } else {
            $this->restoreUserFromDB();
        }
    }

    public function format(bool $formatSex = false, bool $formatDateOfBirth = false): \stdClass
    {
        $formattedUser = new \stdClass();

        $formattedUser->firstName = $this->firstName;
        $formattedUser->lastName = $this->lastName;
        $formattedUser->cityOfBirth = $this->cityOfBirth;

        if ($formatSex) {
            $formattedUser->sex = self::getSexString($this->sex);
        } else {
            $formattedUser->sex = $this->sex;
        }

        if ($formatDateOfBirth) {
            $formattedUser->age = self::getAgeByDateOfBirth($this->dateOfBirth);
        } else {
            $formattedUser->dateOfBirth = $this->dateOfBirth;
        }

        return $formattedUser;
    }

    public function save(): void
    {
        if (null === $this->id && $this->firstName && $this->lastName && $this->dateOfBirth && null !== $this->sex && $this->cityOfBirth) {
            $this->id = $this->connection->insert(
                sprintf(
                    'INSERT user (firstName, lastName, dateOfBirth, sex, cityOfBirth) VALUES ("%s", "%s", "%s", %s, "%s");',
                    $this->firstName,
                    $this->lastName,
                    $this->dateOfBirth->format('Y-m-d'),
                    $this->sex,
                    $this->cityOfBirth
                )
            );
        }
    }

    public function delete(): void
    {
        if (null !== $this->id) {
            $this->connection->delete(sprintf('DELETE FROM user WHERE id = %s', $this->id));
        }
    }

    private function restoreUserFromDB(): void
    {
        $user = $this->connection->selectFirst(sprintf('SELECT * FROM user WHERE id = %s', $this->id));

        $this->firstName = $user['firstName'];
        $this->lastName = $user['lastName'];
        $this->dateOfBirth = new DateTimeImmutable($user['dateOfBirth']);
        $this->sex = (int) $user['sex'];
        $this->cityOfBirth = $user['cityOfBirth'];
    }
}
