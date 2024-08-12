<?php

namespace Payarc\PayarcSdkPhp\utils\services;

use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use Throwable;

class CustomerService extends BaseService
{
    /**
     * @throws Exception
     */
    public function create($customer_data=[])
   {
       return $this->createCustomer($customer_data);
   }

    /**
     * @throws Exception
     */
    public function retrieve($customer_id)
    {
       return $this->retrieveCustomer($customer_id);
    }
    /**
     * @throws Exception
     */
    public function update($customer, $cust_data=[]){
       return $this->updateCustomer($customer, $cust_data);
    }

    /**
     * @throws Exception
     */
    public function list($searchData=[])
    {
        return $this->listCustomers($searchData);
    }
    /**
     * @throws Exception
     */
    private function createCustomer($customer_data=[])
   {
      try {
          $response = $this->client->request('POST', "customers", [
              'json' => $customer_data,
          ], $this->headers);
          $data = json_decode($response->getBody()->getContents(), true);
          $customer = $this->addObjectId($data['data']);

          if (isset($customer_data['cards']) && count($customer_data['cards']) > 0) {
              $cardTokens = [];
              foreach ($customer_data['cards'] as $cardData) {
                  $cardTokens[] = $this->genTokenForCard($cardData);
              }

              if (!empty($cardTokens)) {
                  foreach ($cardTokens as $token) {
                      $this->updateCustomer($customer['customer_id'], ['token_id' => $token['id']]);
                  }
                  return $this->retrieveCustomer($customer['object_id']);
              }
          }
          return $customer;
      } catch (ClientException|ServerException $err) {
          throw new Exception($this->manageError(['source' => 'API Create customers'], $err, true), $err->getCode());
      } catch (GuzzleException|Throwable $err) {
          throw new Exception($this->manageError(['source' => 'API Create customers'], $err), $err->getCode());
      }
   }

    /**
     * @throws Exception
     */
    private function genTokenForCard($token_data=[])
   {
       try{
              $response = $this->client->request('POST', "tokens", [
                'json' => $token_data,
              ], $this->headers);
              $data = json_decode($response->getBody()->getContents(), true);
              return $data['data'];
         } catch (ClientException|ServerException $err) {
              throw new Exception($this->manageError(['source' => 'API for tokens'], $err, true), $err->getCode());
         } catch (GuzzleException|Throwable $err) {
              throw new Exception($this->manageError(['source' => 'API for tokens'], $err), $err->getCode());
       }
   }

    /**
     * @throws Exception
     */
    public function addCardToCustomer($customerId, $cardData)
    {
        try {
            $customerId = is_array($customerId) && isset($customerId['object_id']) ? $customerId['object_id'] : $customerId;
            if (str_starts_with($customerId, 'cus_')) {
                $customerId = substr($customerId, 4);
            }
            $cardToken = $this->genTokenForCard($cardData);
            $this->updateCustomer($customerId, ['token_id' => $cardToken['id']]);
            return $this->addObjectId($cardToken['card']['data']);
        }catch (ClientException|ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API add card to customer'], $err, true), $err->getCode());
        }catch (GuzzleException|Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API add card to customer'], $err), $err->getCode());
        }
    }

    /**
     * @throws Exception
     */
    public function addBankAccToCustomer($customerId, $accData){
          try{
              $customerId = is_array($customerId) && isset($customerId['object_id']) ? $customerId['object_id'] : $customerId;
              if (str_starts_with($customerId, 'cus_')) {
                  $customerId = substr($customerId, 4);
              }
              // Add customer_id to account data
              $accData['customer_id'] = $customerId;
              $response = $this->client->request('POST', 'bankaccounts', [
                  'json' => $accData
              ], $this->headers);
              $responseData = json_decode($response->getBody()->getContents(), true);
              return $this->addObjectId($responseData['data']);
          }catch (ClientException|ServerException $err) {
              throw new Exception($this->manageError(['source' => 'API BankAccount to customer'], $err, true), $err->getCode());
          }catch (GuzzleException|Throwable $err) {
              throw new Exception($this->manageError(['source' => 'API BankAccount to customer'], $err), $err->getCode());
          }
    }
    /**
     * @throws Exception
     */
    public function updateCustomer($customer, $cust_data=[]){
        $customer = is_array($customer) ? ($customer['object_id'] ?? $customer) : $customer;
        if(str_starts_with($customer, 'cus_')){
            $customer = substr($customer, 4);
        }
        try{
            $response = $this->client->request('PATCH', "customers/$customer", [
                'json' => $cust_data,
            ], $this->headers);
            $data = json_decode($response->getBody()->getContents(), true);
            return $this->addObjectId($data['data']);
        } catch (ClientException|ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API Update customers'], $err, true), $err->getCode());
        } catch (GuzzleException|Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API Update customers'], $err), $err->getCode());
        }
    }

    /**
     * @throws Exception
     */
    private function retrieveCustomer($customer_id)
    {
         if(str_starts_with($customer_id, 'cus_')) {
             $customer_id = substr($customer_id, 4);
         }
            try {
                $response = $this->client->request('GET', "customers/$customer_id", [], $this->headers);
                $data = json_decode($response->getBody()->getContents(), true);
                return $this->addObjectId($data['data']);
            } catch (ClientException|ServerException $err) {
                throw new Exception($this->manageError(['source' => 'PI retrieve customer info'], $err, true), $err->getCode());
            } catch (GuzzleException|Throwable $err) {
                throw new Exception($this->manageError(['source' => 'PI retrieve customer info'], $err), $err->getCode());
            }
    }

    /**
     * @throws Exception
     */
    private function listCustomers($searchData=[]): array
    {
        $limit = $searchData['limit'] ?? 25;
        $page = $searchData['page'] ?? 1;
        $constraint = $searchData['constraint'] ?? [];

        try {
            $response = $this->client->request('GET', 'customers', [
                'query' => array_merge(['limit' => $limit, 'page' => $page], $constraint),
            ], $this->headers);
            $data = json_decode($response->getBody(), true);
            $customers = array_map([$this, 'addObjectId'], $data['data']);
            $pagination = $data['meta']['pagination'] ?? [];
            unset($pagination['links']);
            return [
                'customers' => $customers,
                'pagination' => $pagination
            ];
        } catch (ClientException|ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API List customers'], $err, true), $err->getCode());
        } catch (GuzzleException|Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API List customers'], $err), $err->getCode());
        }
    }
}