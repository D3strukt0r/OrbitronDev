<?php

namespace Controller;

use App\Store\ECBCurrencyConverter;
use Controller;

class TestController extends Controller
{
    public function indexAction()
    {
        $string = '';
        $string .= '<p>Testing page, just for development purposes!</p> <br />';
        $string .= ECBCurrencyConverter::convert(100, 'CHF', 'EUR', 2);

        return $string;
    }
}
