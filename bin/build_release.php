<?php
require_once '../vendor/autoload.php';
use Guzzle\Http\Client;

// Create a client and provide a base URL
$client = new Client('https://api.github.com');