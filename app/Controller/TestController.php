<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;

class TestController
{
    public function index(Request $request)
    {
        $response = render_template($request);
        return $response;
    }
}