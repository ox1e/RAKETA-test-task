<?php

namespace src\Integration;

class DataProvider implements DataProviderInterface
{
    private string $host;
    private string $user;
    private string $password;

    public function __construct(string $host, string $user, string $password)
    {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
    }

    public function get(array $request): array
    {
        // returns a response from external service
        return [];
    }
}