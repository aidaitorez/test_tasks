<?php

interface HttpServiceInterface
{
    public function request(string $url, string $method, array $options = []);
}

class XMLHttpService implements HttpServiceInterface
{
    // Implement the request method
    public function request(string $url, string $method, array $options = [])
    {
        // Implementation details for XML HTTP request
    }
}

class Http
{
    private $service;

    public function __construct(HttpServiceInterface $service)
    {
        $this->service = $service;
    }

    public function get(string $url, array $options)
    {
        $this->service->request($url, 'GET', $options);
    }

    public function post(string $url)
    {
        $this->service->request($url, 'POST');
    }
}
