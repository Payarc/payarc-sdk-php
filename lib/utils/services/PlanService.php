<?php

namespace Payarc\PayarcSdkPhp\utils\services;

use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use Throwable;

class PlanService extends BaseService
{
    /**
     * @throws Exception
     */
    public function create($data)
    {
        return $this->create_plan($data);
    }

    /**
     * @throws Exception
     */
    public function list($params=[])
    {
        return $this->list_plans($params);
    }

    /**
     * @throws Exception
     */
    public function retrieve($params)
    {
        return $this->getPlan($params);
    }

    /**
     * @throws Exception
     */
    public function update($params, $new_data)
    {
        return $this->updatePlan($params, $new_data);
    }

    /**
     * @throws Exception
     */
    public function delete($params)
    {
        return $this->deletePlan($params);
    }

    /**
     * @throws Exception
     */
    public function create_subscription($params, $new_data)
    {
        return $this->createSubscription($params, $new_data);
    }


    /**
     * @throws Exception
     */
    private function create_plan($data=[])
    {
        $data['currency'] = $data['currency'] ?? 'usd';
        $data['plan_type'] = $data['plan_type'] ?? 'digital';
        try{
            $response = $this->client->request('POST', 'plans', [
                'json' => $data,
            ], $this->headers);
            $data = json_decode($response->getBody()->getContents(), true);
            return $this->addObjectId($data['data']);
        } catch (ClientException|ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API Create plan ...'], $err, true), $err->getCode());
        } catch (GuzzleException|Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API Create plan ...'], $err), $err->getCode());
        }

    }

    /**
     * @throws Exception
     */
    private function list_plans($params=[]): array
    {
        $params['limit'] = $params['limit'] ?? '99999';
        try{
            $response = $this->client->request('GET', 'plans', [
                'query' => $params,
            ], $this->headers);
            $data = json_decode($response->getBody()->getContents(), true);
            $data = $data['data'] ?? [];
            $plans = array_map(fn($plan) => $this->addObjectId($plan), $data);
            $pagination = $data['meta']['pagination'] ?? [];
            unset($data['meta']);
            return ['plans'=> $plans, 'pagination'=> $pagination];

        } catch (ClientException|ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API get all plans'], $err, true), $err->getCode());
        } catch (GuzzleException|Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API get all plans'], $err), $err->getCode());
        }
    }

    /**
     * @throws Exception
     */
    protected final function getPlan($plan=[])
    {
           $data = $plan['object_id'] ?? $plan;
           try{
                $response = $this->client->request('GET', 'plans/'.$data, [], $this->headers);
                $data = json_decode($response->getBody()->getContents(), true);
                return $this->addObjectId($data['data']);
              } catch (ClientException|ServerException $err) {
                throw new Exception($this->manageError(['source' => 'API get plan details'], $err, true), $err->getCode());
              } catch (GuzzleException|Throwable $err) {
                throw new Exception($this->manageError(['source' => 'API get plan details'], $err), $err->getCode());
           }
    }

    /**
     * @throws Exception
     */
    protected final function updatePlan($plan, $newData)
    {
        $data_id = $plan['object_id'] ?? $plan;
            try{
                $response = $this->client->request('PATCH', 'plans/'.$data_id, [
                    'json' => $newData,
                ], $this->headers);
                $data = json_decode($response->getBody()->getContents(), true);
                return $this->addObjectId($data['data']);
            } catch (ClientException|ServerException $err) {
                throw new Exception($this->manageError(['source' => 'API update customer info'], $err, true), $err->getCode());
            } catch (GuzzleException|Throwable $err) {
                throw new Exception($this->manageError(['source' => 'API update customer info'], $err), $err->getCode());
            }
    }

    /**
     * @throws Exception
     */
    protected final function deletePlan($params)
    {
        $data_id = $params['object_id'] ?? $params;
        try{
            $response = $this->client->request('DELETE', 'plans/'.$data_id, [], $this->headers);
            $data = json_decode($response->getBody()->getContents(), true);
            return $this->addObjectId($data['data']);
        } catch (ClientException|ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API delete plan'], $err, true), $err->getCode());
        } catch (GuzzleException|Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API delete plan'], $err), $err->getCode());
        }
    }

    /**
     * @throws Exception
     */
    protected final function createSubscription($plan, $newData=[])
    {
        $data_id = $plan['object_id'] ?? $plan;
        try{
            $newData['plan_id'] = $data_id;
            $newData['customer_id'] = str_starts_with($newData['customer_id'], 'cus_') ? substr($newData['customer_id'], 4) : $newData['customer_id'];
            $response = $this->client->request('POST', 'subscriptions', [
                'json' => $newData,
            ], $this->headers);
            $data = json_decode($response->getBody()->getContents(), true);
            return $this->addObjectId($data['data']);
        } catch (ClientException|ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API create subscription'], $err, true), $err->getCode());
        } catch (GuzzleException|Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API create subscription'], $err), $err->getCode());
        }
    }
}