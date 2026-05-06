<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(title: 'Test Payment System API', version: '1.0.0')]
#[OA\Server(url: '/api', description: 'API Server')]
abstract class Controller
{
}
