<?php

namespace Controller;

use App\Store\ECBCurrencyConverter;
use Controller;

class TestController extends Controller
{
    public function indexAction()
    {
        echo '<p>Testing page, just for development purposes</p> <br />';
        echo ECBCurrencyConverter::convert(100, 'CHF', 'EUR', 2);
    }
}