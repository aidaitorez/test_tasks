<?php

require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Predis\Client as RedisClient;

//strategy pattern 

interface SecretKeyStorageInterface
{
    public function getSecretKey(): string;
}

//retrieve the secret key from file
class FileSecretKeyStorage implements SecretKeyStorageInterface
{
    public function getSecretKey(): string
    {
        return file_get_contents('path/to/secret_key.txt');
    }
}

//retrieve the secret key from DB
class DatabaseSecretKeyStorage implements SecretKeyStorageInterface
{
    public function getSecretKey(): string
    {
        // Logic to retrieve the secret key from a database
        // Example implementation:
        $pdo = new PDO('connection_string');
        $stmt = $pdo->prepare('SELECT secret_key FROM secret_table WHERE id = :id');
        $stmt->bindValue(':id', 1);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['secret_key'];
    }
}
//retrieve the secret key from Redis
class RedisSecretKeyStorage implements SecretKeyStorageInterface
{
    private $redis;

    public function __construct()
    {
        $this->redis = new RedisClient([
            'scheme' => 'tcp',
            'host' => 'localhost',
            'port' => 6379,
        ]);
    }

    public function getSecretKey(): string
    {
        return $this->redis->get('secret_key');
    }
}


class Concept
{
    private $client;
    private $secretKeyStorage;

    public function __construct(SecretKeyStorageInterface $secretKeyStorage)
    {
        $this->client = new Client();
        $this->secretKeyStorage = $secretKeyStorage;
    }

    public function getUserData()
    {
        $params = [
            'auth' => ['user', 'pass'],
            'token' => $this->secretKeyStorage->getSecretKey()
        ];

        $request = new Request('GET', 'https://api.method', $params);
        $promise = $this->client->sendAsync($request)->then(function ($response) {
            $result = $response->getBody();
        });

        $promise->wait();
    }
}

// Example usage with FileSecretKeyStorage
$secretKeyStorage = new FileSecretKeyStorage();
$concept = new Concept($secretKeyStorage);
$concept->getUserData();

// Example usage with DatabaseSecretKeyStorage
$secretKeyStorage = new DatabaseSecretKeyStorage();
$concept = new Concept($secretKeyStorage);
$concept->getUserData();
