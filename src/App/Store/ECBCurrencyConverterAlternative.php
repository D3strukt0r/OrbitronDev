<?php

// TODO: Check if this works better
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

class ECBCurrencyConverterAlternative
{
    private $xml_file = 'www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';
    private $sInfoFileLoc;
    private $exchange_rates = array();

    /**
     * Load currency rates
     */
    function __construct()
    {
        $this->sInfoFileLoc = realpath('./app/data/currency/' . '/currency-data.xml');

        $this->checkLastUpdated();

        $sCurrencyData = file_get_contents($this->sInfoFileLoc);
        $oCurrencyData = new \SimpleXMLElement($sCurrencyData);

        foreach ($oCurrencyData->data[0]->currency as $aCurrency) {
            $this->exchange_rates[$aCurrency['currency']] = $aCurrency['rate'];
        }
    }

    /**
     * Perform the actual conversion
     *
     * @param        $iAmount   (Required) How much should be converted.
     * @param        $sFrom     (Required) From which currency should be converted.
     * @param string $sTo       (Optional) To which currency should be converted. Default is USD.
     * @param int    $iDecimals (Optional) How much decimals should the number have.
     *
     * @return string
     */
    function convert($iAmount, $sFrom, $sTo = 'USD', $iDecimals = 2)
    {
        $iNewCurrency = ($iAmount / $this->exchange_rates[$sFrom]) * $this->exchange_rates[$sTo];
        $iConvertedNumber = number_format($iNewCurrency, $iDecimals);
        return $iConvertedNumber;
    }

    /**
     * Check to see how long since the data was last updated
     */
    private function checkLastUpdated()
    {
        $sCurrencyData = file_get_contents($this->sInfoFileLoc);
        $oCurrencyData = new \SimpleXMLElement($sCurrencyData);

        $iDownloadTime = $oCurrencyData->info[0]->downloaded;
        if (time() > (strtotime($iDownloadTime) + (12 * 60 * 60))) {
            $this->downloadExchangeRates();
        }
    }

    /**
     * Download xml file, extract exchange rates and store values in xml file
     */
    private function downloadExchangeRates()
    {
        $sCurrencyDataDomain = substr($this->xml_file, 0, strpos($this->xml_file, '/'));
        $sCurrencyFile = substr($this->xml_file, strpos($this->xml_file, '/'));

        $oCurrencySocket = @fsockopen($sCurrencyDataDomain, 80, $errno, $errstr, 10);
        if ($oCurrencySocket) {
            $out = 'GET ' . $sCurrencyFile . ' HTTP/1.1' . "\r\n";
            $out .= 'Host: ' . $sCurrencyDataDomain . "\r\n";
            $out .= 'User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8) Gecko/20051111 Firefox/1.5' . "\r\n";
            $out .= 'Connection: Close' . "\r\n\r\n";
            fwrite($oCurrencySocket, $out);

            // Get currency data
            $sFileBuffer = '';
            while (!feof($oCurrencySocket)) {
                $sFileBuffer .= fgets($oCurrencySocket, 128);
            }
            fclose($oCurrencySocket);

            $sCurrencyPattern = "{<Cube\s*currency='(\w*)'\s*rate='([\d\.]*)'/>}is";
            preg_match_all($sCurrencyPattern, $sFileBuffer, $sXmlCurrencyData);
            array_shift($sXmlCurrencyData);

            // Save currency data
            for ($i = 0; $i < count($sXmlCurrencyData[0]); $i++) {
                $exchange_rate[$sXmlCurrencyData[0][$i]] = $sXmlCurrencyData[1][$i];
            }

            // Add data content
            $oNewCurrencyInfoFIle = new \SimpleXMLElement('<root />');
            $oInfoFileDataChild = $oNewCurrencyInfoFIle->addChild('data');
            foreach ($exchange_rate as $sCurrency => $iRate) {
                $oInfoFileCurrencyChild = $oInfoFileDataChild->addChild('currency');
                $oInfoFileCurrencyChild->addAttribute('currency', $sCurrency);
                $oInfoFileCurrencyChild->addAttribute('rate', $iRate);
            }

            // Add info content
            $oInfoFileDataChild = $oNewCurrencyInfoFIle->addChild('info');
            $oInfoFileDataChild->addChild('downloaded', date('Y-m-d'));

            // Save new data
            $sCurrencyInXml = $oNewCurrencyInfoFIle->asXML();
            file_put_contents($this->sInfoFileLoc, $sCurrencyInXml);
        }
    }
}