<?php

namespace Payarc\PayarcSdkPhp\utils\services;

use InvalidArgumentException;

abstract class BaseService
{
    protected $client;
    protected array $headers = [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ];
    public function __construct($client)
    {
        $this->client = $client;
        $this->headers['Authorization'] = 'Bearer ' . $this->client->getBearerToken();
    }

    public function getClient()
    {
        return $this->client;
    }

    protected function request($method, $path, $params, $headers)
    {
        return $this->getClient()->request($method, $path, $params, $headers);
    }

    // Charges
    public function createCharge($obj, $charge_data=null){}
    public function refundCharge($charge, $params){}
    public function getCharge($chargeId){}

    //Customers
    public function updateCustomer($customer, $cust_data=[]){}
    public function addCardToCustomer($customerId, $cardData){}
    public function addBankAccToCustomer($customerId, $accData){}

    //Applications
    public function retrieveApplicant($applicant){}
    public function updateApplicant($obj, $newData){}
    public function deleteApplicant($applicant){}
    public function addApplicantDocument($applicant, $params){}
    public function deleteApplicantDocument($document){}
    public function submitApplicantForSignature($applicant){}
    public function subAgents(){}

    //Campaigns
    protected function getCampaign($key){}
    protected function updateCampaign($data, $newData){}

    //Plans
    protected function getPlan($plan){}
    protected function updatePlan($plan, $newData){}
    protected function deletePlan($params){}
    protected function createSubscription($plan, $newData){}

    //Subscriptions
    protected function cancelSubscription($subscription) {}
    protected function updateSubscription($subscription, $newData){}

    public function addObjectId(&$obj)
    {
        $handleObject = function (&$obj) use (&$handleObject) {
            if (isset($obj['id']) || isset($obj['customer_id'])) {
                switch ($obj['object']) {
                    case 'Charge':
                        $obj['object_id'] = "ch_" . $obj['id'];
                        $obj['create_refund'] = function ($params) use ($obj) {return $this->refundCharge($obj, $params);};
                        break;
                    case 'customer':
                        $obj['object_id'] = "cus_" . $obj['customer_id'];
                        $obj['update'] = function ($cust_data) use ($obj) {return $this->updateCustomer($obj, $cust_data);};
                        $obj['cards'] = [];
                        $obj['cards']['create'] = function ($cardData) use ($obj) {return $this->addCardToCustomer($obj, $cardData);};
                        if (!isset($obj['bank_accounts'])) {
                            $obj['bank_accounts'] = [];
                        }
                        $obj['bank_accounts']['create'] = function ($accData) use ($obj) {return $this->addBankAccToCustomer($obj, $accData);};
                        if (!isset($obj['charges'])) {
                            $obj['charges'] = [];
                        }
                        $obj['charges']['create'] = function ($charge_data) use ($obj) {return $this->createCharge($obj, $charge_data);};
                        break;
                    case 'Token':
                        $obj['object_id'] = "tok_" . $obj['id'];
                        break;
                    case 'Card':
                        $obj['object_id'] = "card_" . $obj['id'];
                        break;
                    case 'BankAccount':
                        $obj['object_id'] = "bnk_" . $obj['id'];
                        break;
                    case 'ACHCharge':
                        $obj['object_id'] = "ach_" . $obj['id'];
                        $obj['create_refund'] = function ($params) use ($obj) {return $this->refundCharge($obj, $params);};
                        break;
                    case 'ApplyApp':
                        $obj['object_id'] = "appl_" . $obj['id'];
                        $obj['retrieve'] = function () use ($obj) {return $this->retrieveApplicant($obj);};
                        $obj['delete'] = function () use ($obj) {return $this->deleteApplicant($obj);};
                        $obj['add_document'] = function ($params) use ($obj) {return $this->addApplicantDocument($obj, $params);};
                        $obj['submit'] = function () use ($obj) {return $this->submitApplicantForSignature($obj);};
                        $obj['update'] = function ($newData) use ($obj) {return $this->updateApplicant($obj, $newData);};
                        $obj['list_sub_agents'] = function () {return $this->subAgents();};
                        break;
                    case 'ApplyDocuments':
                        $obj['object_id'] = "doc_" . $obj['id'];
                        $obj['delete'] = function () use ($obj) {return $this->deleteApplicantDocument($obj);};
                        break;
                    case 'Campaign':
                        $obj['object_id'] = "cmp_" . $obj['id'];
                        $obj['update'] = function ($new_data) use ($obj) {return $this->updateCampaign($obj, $new_data);};
                        $obj['retrieve'] = function () use ($obj) {return $this->getCampaign($obj);};
                        break;
                    case 'User':
                        $obj['object_id'] = "usr_" . $obj['id'];
                        break;
                    case 'Subscription':
                        $obj['object_id'] = "sub_" . $obj['id'];
                        $obj['cancel'] = function () use ($obj) {return $this->cancelSubscription($obj);};
                        $obj['update'] = function ($newData) use ($obj) {return $this->updateSubscription($obj, $newData);};
                        break;
//                    case 'Cases':
//                        $obj['object'] = 'Dispute';
//                        $obj['object_id'] = "dis_" . $obj['id'];
//                        break;
                }
            }
           elseif (isset($obj['MerchantCode'])) {
                $obj['object_id'] = "appl_" . $obj['MerchantCode'];
                $obj['object'] = 'ApplyApp';
                unset($obj['MerchantCode']);
                $obj['retrieve'] = function () use ($obj) {return $this->retrieveApplicant($obj);};
                $obj['delete'] = function () use ($obj) {return $this->deleteApplicant($obj);};
                $obj['add_document'] = function ($params) use ($obj) {return $this->addApplicantDocument($obj, $params);};
                $obj['submit'] = function () use ($obj) {return $this->submitApplicantForSignature($obj);};
                $obj['update'] =  function ($newData) use ($obj) {return $this->updateApplicant($obj, $newData);};
                $obj['list_sub_agents'] = function () {return $this->subAgents();};
            } elseif (isset($obj['plan_id'])) {
                $obj['object_id'] = $obj['plan_id'];
                $obj['object'] = 'Plan';
                unset($obj['plan_id']);
                $obj['retrieve'] = function () use ($obj) {return $this->getPlan($obj);};
                $obj['update'] = function ($newData) use ($obj) {return $this->updatePlan($obj, $newData);};
                $obj['delete'] = function () use ($obj) {return $this->deletePlan($obj);};
                $obj['create_subscription'] = function ($newData) use ($obj) {return $this->createSubscription($obj, $newData);};
            }

            foreach ($obj as $key => &$value) {
                if (is_array($value)) {
                    if (array_keys($value) !== range(0, count($value) - 1)) {
                        // Associative array
                        $handleObject($value);
                    } else {
                        // Indexed array
                        foreach ($value as &$item) {
                            if (is_array($item)) {
                                $handleObject($item);
                            }
                        }
                    }
                }
            }
        };

        $handleObject($obj);
        return $obj;
    }

    public function manageError(array $seed = [], $error = null, $responseBody= false) {
        // Set default values for seed if not provided
        if($responseBody) {
            $error_json = json_decode($error->getResponse()->getBody());
        }

        $seed['object'] = "Error " . $this->getClient()->getVersion();
        $seed['type'] = 'TODO put here error type';
        // Determine error message
        $seed['errorMessage'] = $responseBody ? ($error_json->message ?? 'unKnown') : ($error->getMessage() ?? 'unKnown');
        // Determine error code
        $code = $error->getCode();
        $seed['errorCode'] = $code !== 0 ? $code : '500';
        // Determine error list
        $seed['errorList'] = $responseBody ? ($error_json->errors ?? []) : [];
        // Determine error exception
        $seed['errorException'] =$responseBody ? ($error_json->exception ?? 'unKnown') : 'unKnown';
        return json_encode($seed);
    }
}