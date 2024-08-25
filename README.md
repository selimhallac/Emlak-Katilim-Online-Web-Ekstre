```php
use Phpdev\EmlakKatilim;

$username = "";
$password = "";
$wsdlUrl = "https://boa.emlakbank.com.tr/BOA.Integration.WCFService/BOA.Integration.AccountStatement/AccountStatementService.svc/Basic";

$emlakKatilim = new EmlakKatilim($username, $password, $wsdlUrl);

$beginDate = "2024-08-05";
$endDate = "2024-08-22";

$response = $emlakKatilim->getAccountStatement($beginDate, $endDate);

$responseArray = json_decode($response, true);

if ($responseArray['statu']) {
    print_r($responseArray['response']);
} else {
    echo "Hata: " . $responseArray['response'];
}
```
