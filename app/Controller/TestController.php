<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TestController
{
    public function index(Request $request)
    {
        $response = render_template($request);
        return $response;
    }

    public function date(?string $date = '2012')
    {
        // $date = $request->attributes->get('date');
        $response =  new Response($date ?? date('Y-m-d H:i:s')  . '----' . rand() . '----');
        $response->setTtl(10);
        return $response;
    }
}