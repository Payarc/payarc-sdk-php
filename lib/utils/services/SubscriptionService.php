<?php

namespace Payarc\PayarcSdkPhp\utils\services;

use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use Throwable;

class SubscriptionService extends BaseService
{

    public function cancel($subscription)
    {
        return $this->cancelSubscription($subscription);
    }

    public function list($params=[])
    {
        return $this->listSubscriptions($params);
    }

    public function update($subscription, $newData)
    {
        return $this->updateSubscription($subscription, $newData);
    }

    /**
     * @throws Exception
     */
    protected final function cancelSubscription($subscription): mixed
    {
        $data_id = $subscription['object_id'] ?? $subscription;
        try {
            $data_id = str_starts_with($data_id, 'sub_') ? substr($data_id, 4) : $data_id;
            $response = $this->client->request('PATCH', "subscriptions/$data_id/cancel", [], $this->headers);
            $data = json_decode($response->getBody()->getContents(), true);
            return $this->addObjectId($data['data']);
        } catch (ClientException|ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API cancel subscription'], $err, true), $err->getCode());
        } catch (GuzzleException|Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API cancel subscription'], $err), $err->getCode());
        }
    }

    /**
     * @throws Exception
     */
    protected final function updateSubscription($subscription, $newData): mixed
    {
        $data_id = $subscription['object_id'] ?? $subscription;
        try{
            $data_id = str_starts_with($data_id, 'sub_') ? substr($data_id, 4) : $data_id;
            $response = $this->client->request('PATCH', 'subscriptions/'.$data_id, [
                'json' => $newData,
            ], $this->headers);
            $data = json_decode($response->getBody()->getContents(), true);
            return $this->addObjectId($data['data']);
        } catch (ClientException|ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API update subscription'], $err, true), $err->getCode());
        } catch (GuzzleException|Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API update subscription'], $err), $err->getCode());
        }
    }

    /**
     * @throws Exception
     */
    private function listSubscriptions($params=[]): array
    {
        $params['limit'] = $params['limit'] ?? '99999';
        try{
            $response = $this->client->request('GET', 'subscriptions', [
                'query' => $params,
            ], $this->headers);
            $data = json_decode($response->getBody()->getContents(), true);
            $data = $data['data'] ?? [];
            $subscriptions = array_map(fn($plan) => $this->addObjectId($plan), $data);
            $pagination = $data['meta']['pagination'] ?? [];
            unset($data['meta']);
            return ['subscriptions'=> $subscriptions, 'pagination'=> $pagination];

        } catch (ClientException|ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API get all plans'], $err, true), $err->getCode());
        } catch (GuzzleException|Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API get all plans'], $err), $err->getCode());
        }
    }
}