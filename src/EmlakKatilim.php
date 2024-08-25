<?php

namespace Phpdev;

class EmlakKatilim
{
    private $username;
    private $password;
    private $wsdlUrl;

    public function __construct($username, $password, $wsdlUrl)
    {
        $this->username = $username;
        $this->password = $password;
        $this->wsdlUrl = $wsdlUrl;
    }

    public function getAccountStatement($beginDate, $endDate)
    {
        try {
            $xml = <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://boa.net/BOA.Integration.CoreBanking.Teller/Service" xmlns:boa="http://schemas.datacontract.org/2004/07/BOA.Integration.Base" xmlns:boa1="http://schemas.datacontract.org/2004/07/BOA.Integration.Model.CoreBanking.Teller">
   <soapenv:Header/>
   <soapenv:Body>
      <ser:GetAccountStatement>
         <ser:request>
            <boa:ExtUName>{$this->username}</boa:ExtUName>
            <boa:ExtUPassword>{$this->password}</boa:ExtUPassword>
            <boa1:BeginDate>{$beginDate}</boa1:BeginDate>
            <boa1:EndDate>{$endDate}</boa1:EndDate>
         </ser:request>
      </ser:GetAccountStatement>
   </soapenv:Body>
</soapenv:Envelope>
XML;

            $headers = array(
                'SOAPAction: http://boa.net/BOA.Integration.CoreBanking.Teller/Service/IAccountStatementService/GetAccountStatement',
                'Content-Type: text/xml;charset=UTF-8',
                'Accept-Encoding: gzip,deflate',
            );

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->wsdlUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $xml,
                CURLOPT_HTTPHEADER => $headers,
            ));
        
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
        
            if ($err) {
                return ['status' => false, 'response' => 'cURL error: ' . $err];
            } else {
                // XML Yanıtı Parse Et
                $responseXml = simplexml_load_string($response);
               
                $namespaces = $responseXml->getNamespaces(true);

                // GetAccountStatementResponse düğümüne erişim
                $body = $responseXml->children($namespaces['s'])->Body;
                $responseContent = $body->children($namespaces[''])->GetAccountStatementResponse->GetAccountStatementResult;
                //print_r($responseContent->children($namespaces['a']));
                $responseData = [];
                // Bilgiler Hatalı ise
                if (@!isset($responseContent->children($namespaces['a'])->Value->children($namespaces['b'])->AccountContract)){
                    return ['status' => false, 'response' => (String)$responseContent->children($namespaces['a'])->Results->Result->ErrorMessage];
                }
                
                
                foreach ($responseContent->children($namespaces['a'])->Value->children($namespaces['b'])->AccountContract as $accountContract) {
                    $accountDetails = [];
                    $accountDetails['AccountNumber'] = (string)$accountContract->AccountNumber;
                    $accountDetails['BranchName'] = (string)$accountContract->BranchName;
                    $accountDetails['Balance'] = (string)$accountContract->Balance;
                    $accountDetails['Currency'] = (string)$accountContract->FECName;
                    $accountDetails['Currency_name'] = (string)$accountContract->FECLongName;
                    $accountDetails['LastTranDate'] = (string)$accountContract->LastTranDate;
                    $accountDetails['OpenDate'] = (string)$accountContract->OpenDate;
                    // Transaction Details
                    foreach ($accountContract->Details->children($namespaces['b'])->TransactionDetailContract as $transactionDetail) {
                        $transaction = [];
                        $transaction['Amount'] = (string)$transactionDetail->Amount;
                        $transaction['Description'] = (string)$transactionDetail->Description;
                        $transaction['TranDate'] = (string)$transactionDetail->TranDate;
                        $transaction['BusinessKey'] = (string)$transactionDetail->BusinessKey;
                        $accountDetails['Transactions'][] = $transaction;
                        
                    } 

                    $responseData[] = $accountDetails;
                }   

                return ['status' => true, 'response' => $responseData];
            }
        
        } catch (Throwable $e) {
            return ['status' => false, 'response' => 'Bağlantı problemi oluştu.'];
        }
    }
}
