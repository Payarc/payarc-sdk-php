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

    public function addObjectId(&$obj)
    {
        $handleObject = function (&$obj) use (&$handleObject) {
            if (isset($obj['id']) || isset($obj['customer_id'])) {
                switch ($obj['object']) {
                    case 'Charge':
                        $obj['object_id'] = "ch_" . $obj['id'];
                        $obj['create_refund'] = function ($params) use ($obj) {$this->refundCharge($obj, $params);};
                        break;
//                    case 'customer':
//                        $obj['object_id'] = "cus_" . $obj['customer_id'];
//                        $obj['update'] = function () use ($obj) {$this->updateCustomer($obj);};
//                        $obj['cards'] = [];
//                        $obj['cards']['create'] = function () use ($obj) {$this->addCardToCustomer($obj);};
//                        if (!isset($obj['bank_accounts'])) {
//                            $obj['bank_accounts'] = [];
//                        }
//                        $obj['bank_accounts']['create'] = function () use ($obj) {$this->addBankAccToCustomer($obj);};
//                        if (!isset($obj['charges'])) {
//                            $obj['charges'] = [];
//                        }
//                        $obj['charges']['create'] = function () use ($obj) {$this->createCharge($obj);};
//                        break;
//                    case 'Token':
//                        $obj['object_id'] = "tok_" . $obj['id'];
//                        break;
//                    case 'Card':
//                        $obj['object_id'] = "card_" . $obj['id'];
//                        break;
//                    case 'BankAccount':
//                        $obj['object_id'] = "bnk_" . $obj['id'];
//                        break;
                    case 'ACHCharge':
                        $obj['object_id'] = "ach_" . $obj['id'];
                        $obj['create_refund'] = function ($params) use ($obj) {$this->refundCharge($obj, $params);};
                        break;
//                    case 'ApplyApp':
//                        $obj['object_id'] = "appl_" . $obj['id'];
//                        $obj['retrieve'] = function () use ($obj) {$this->retrieveApplicant($obj);};
//                        $obj['delete'] = function () use ($obj) {$this->deleteApplicant($obj);};
//                        $obj['add_document'] = function () use ($obj) {$this->addApplicantDocument($obj);};
//                        $obj['submit'] = function () use ($obj) {$this->submitApplicantForSignature($obj);};
//                        $obj['update'] = function () use ($obj) {$this->updateApplicant($obj);};
//                        $obj['list_sub_agents'] = function () use ($obj) {$this->subAgents($obj);};
//                        break;
//                    case 'ApplyDocuments':
//                        $obj['object_id'] = "doc_" . $obj['id'];
//                        $obj['delete'] = function () use ($obj) {$this->deleteApplicantDocument($obj);};
//                        break;
//                    case 'Campaign':
//                        $obj['object_id'] = "cmp_" . $obj['id'];
//                        $obj['update'] = function () use ($obj) {$this->updateCampaign($obj);};
//                        $obj['retrieve'] = function () use ($obj) {$this->getCampaign($obj);};
//                        break;
//                    case 'User':
//                        $obj['object_id'] = "usr_" . $obj['id'];
//                        break;
//                    case 'Subscription':
//                        $obj['object_id'] = "sub_" . $obj['id'];
//                        $obj['cancel'] = function () use ($obj) {$this->cancelSubscription($obj);};
//                        $obj['update'] = function () use ($obj) {$this->updateSubscription($obj);};
//                        break;
//                    case 'Cases':
//                        $obj['object'] = 'Dispute';
//                        $obj['object_id'] = "dis_" . $obj['id'];
//                        break;
                }
            }
//           elseif (isset($obj['MerchantCode'])) {
//                $obj['object_id'] = "appl_" . $obj['MerchantCode'];
//                $obj['object'] = 'ApplyApp';
//                unset($obj['MerchantCode']);
//                $obj['retrieve'] = function () use ($obj) {
//                    $this->retrieveApplicant($obj);
//                };
//                $obj['delete'] = function () use ($obj) {
//                    $this->deleteApplicant($obj);
//                };
//                $obj['add_document'] = function () use ($obj) {
//                    $this->addApplicantDocument($obj);
//                };
//                $obj['submit'] = function () use ($obj) {
//                    $this->submitApplicantForSignature($obj);
//                };
//                $obj['update'] = function () use ($obj) {
//                    $this->updateApplicant($obj);
//                };
//                $obj['list_sub_agents'] = function () use ($obj) {
//                    $this->subAgents($obj);
//                };
//            } elseif (isset($obj['plan_id'])) {
//                $obj['object_id'] = $obj['plan_id'];
//                $obj['object'] = 'Plan';
//                unset($obj['plan_id']);
//                $obj['retrieve'] = function () use ($obj) {
//                    $this->getPlan($obj);
//                };
//                $obj['update'] = function () use ($obj) {
//                    $this->updatePlan($obj);
//                };
//                $obj['delete'] = function () use ($obj) {
//                    $this->deletePlan($obj);
//                };
//                $obj['create_subscription'] = function () use ($obj) {
//                    $this->createSubscription($obj);
//                };
//            }

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