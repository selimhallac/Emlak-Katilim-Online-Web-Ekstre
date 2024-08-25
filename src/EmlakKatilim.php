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
                $res['statu'] = false;
                $res['response'] = 'cURL error: ' . $err;
            } else {
                $res['statu'] = true;
                $res['response'] = $response;
            }
            return json_encode($res);
        
        } catch (Throwable $e) {
            $res['statu'] = false;
            $res['response'] = 'Bağlantı problemi oluştu.';
            return json_encode($res);
        }
    }
}
