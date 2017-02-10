<?php

/*
 * British Pounds = GBP,
 * US Dollars = USD,
 * Euros = EUR,
 * Australian Dollars = AUD,
 * Bulgarian Leva = BGN,
 * Canadian Dollars = CAD,
 * Swiss Francs = CHF,
 * Chinese Yuan Renminbi = CNY,
 * Cyprian Pounds = CYP,
 * Czech Koruny = CZK,
 * Danish Kroner = DKK,
 * Estonian Krooni = EEK,
 * Hong Kong,
 * Dollars = HKD,
 * Croatian Kuna = HRK,
 * Hungarian Forint = HUF,
 * Indonesian Rupiahs = IDR,
 * Icelandic Kronur = ISK,
 * Japanese Yen = JPY,
 * South Korean Won = KRW,
 * Lithuanian Litai = LTL,
 * Latvian Lati = LVL,
 * Malta Liri = MTL,
 * Malaysian Ringgits = MYR,
 * Norwegian Krone = NOK,
 * New Zealand Dollars = NZD,
 * Philippine Pesos = PHP,
 * Polish Zlotych = PLN,
 * Romanian New Lei = RON,
 * Russian Rubles = RUB,
 * Swedish Kronor = SEK,
 * Slovenian Tolars = SIT,
 * Slovakian Koruny = SKK,
 * Thai Baht = THB,
 * Turkish New Lira = TRY,
 * South African Rand = ZAR
 */

namespace App\Store;

use Kernel;

class ECBCurrencyConverter
{
    private static $sXmlFile = 'http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';
    private static $sCachedFile = '/app/data/currency/euro.xml';

    private static function setupDir()
    {
        self::$sCachedFile = Kernel::$kernel->getRootDir() . '/app/data/currency/euro.xml';
    }

    public static function update()
    {
        self::setupDir();
        if (!file_exists(self::$sCachedFile)) {
            self::download(self::$sCachedFile);
            return true;
        }

        $oCurrencyDataLocal = simplexml_load_file(self::$sCachedFile);
        $oCurrencyDataHosted = simplexml_load_file(self::$sXmlFile);

        if ($oCurrencyDataLocal->Cube->Cube['time'] != $oCurrencyDataHosted->Cube->Cube['time']) {
            self::download(self::$sCachedFile);
            return true;
        }
        return false;
    }

    private static function download($save_to)
    {
        self::setupDir();
        file_put_contents($save_to, fopen(self::$sXmlFile, 'r'));
    }

    /***********************************************************************************/

    private function getRate($currency)
    {
        self::setupDir();
        $oXmlFile = simplexml_load_file(self::$sCachedFile);
        foreach ($oXmlFile->Cube->Cube->Cube as $aRate) {
            if (strtoupper($currency) == strtoupper($aRate['currency'])) {
                return $aRate['rate'];
            }
        }
        return 'currency_does_not_exists';
    }

    public static function convert($amount, $from, $to, $decimals)
    {
        self::setupDir();
        if (!file_exists(self::$sCachedFile)) {
            self::download(self::$sCachedFile);
        }

        $fNewCurrency = (float)($amount / self::getRate($from)) * self::getRate($to);
        $fConvertedNumber = (float)number_format($fNewCurrency, $decimals);
        return $fConvertedNumber;
    }
}