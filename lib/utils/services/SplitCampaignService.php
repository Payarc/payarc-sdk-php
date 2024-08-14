<?php

namespace Payarc\PayarcSdkPhp\utils\services;

use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use Throwable;

class SplitCampaignService extends BaseService
{
    /**
     * @throws Exception
     */
    public function create($data)
    {
        return $this->createCampaign($data);
    }

    /**
     * @throws Exception
     */
    public function list()
    {
        return $this->listCampaigns();
    }

    /**
     * @throws Exception
     */
    public function retrieve($key)
    {
        return $this->getCampaign($key);
    }

    /**
     * @throws Exception
     */
    public function update($data, $newData)
    {
        return $this->updateCampaign($data, $newData);
    }

    /**
     * @throws Exception
     */
    public function list_accounts()
    {
        return $this->listCampaignAccounts();
    }
    /**
     * @throws Exception
     */
    private function createCampaign($data)
    {
        try {
            $this->headers['Authorization'] = 'Bearer ' . $this->client->getBearerTokenAgent();
            $response = $this->client->request('POST', "agent-hub/campaigns", [
                'json' => $data,
            ], $this->headers);
            $data = json_decode($response->getBody()->getContents(), true);
            return $this->addObjectId($data['data']);
        }catch (ClientException|ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API Create campaign ...'], $err, true), $err->getCode());
        } catch (GuzzleException|Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API Create campaign ...'], $err), $err->getCode());
        }
    }

    /**
     * @throws Exception
     */
    private function listCampaigns()
    {
        try {
            $this->headers['Authorization'] = 'Bearer ' . $this->client->getBearerTokenAgent();
            $response = $this->client->request('GET', "agent-hub/campaigns", [
                'query' => ['limit' => 0],
            ], $this->headers);
            $data = json_decode($response->getBody()->getContents(), true);
            return $this->addObjectId($data['data']);
        } catch (ClientException|ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API list campaigns'], $err, true), $err->getCode());
        } catch (GuzzleException|Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API list campaigns'], $err), $err->getCode());
        }
    }

    /**
     * @throws Exception
     */
    protected final function getCampaign($key)
    {
        $key_id = $key['object_id'] ?? $key;
        if (str_starts_with($key_id, 'cmp_')) {
            $key_id = substr($key_id, 4);
        }

        try{
            $this->headers['Authorization'] = 'Bearer ' . $this->client->getBearerTokenAgent();
            $response = $this->client->request('GET', "agent-hub/campaigns/{$key_id}", [
                'query' => ['limit' => 0],
            ], $this->headers);
            $data = json_decode($response->getBody()->getContents(), true);
            return $this->addObjectId($data['data']);
        } catch (ClientException|ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API get campaign status'], $err, true), $err->getCode());
        } catch (GuzzleException|Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API get campaign status'], $err), $err->getCode());
        }
    }

    /**
     * @throws Exception
     */
    protected final function updateCampaign($data, $newData)
    {
       $data_id = $data['object_id'] ?? $data;
        if (str_starts_with($data_id, 'cmp_')) {
            $data_id = substr($data_id, 4);
        }

        try {
            $this->headers['Authorization'] = 'Bearer ' . $this->client->getBearerTokenAgent();
            $response = $this->client->request('PATCH', "agent-hub/campaigns/{$data_id}", [
                'json' => $newData,
            ], $this->headers);
            $data = json_decode($response->getBody()->getContents(), true);
            return $this->addObjectId($data['data']);
        } catch (ClientException|ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API update campaign status'], $err, true), $err->getCode());
        } catch (GuzzleException|Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API update campaign status'], $err), $err->getCode());
        }
    }

    /**
     * @throws Exception
     */
    public function listCampaignAccounts(): array
    {
            try {
                $this->headers['Authorization'] = 'Bearer ' . $this->client->getBearerTokenAgent();
                $response = $this->client->request('GET', "account/my-accounts", [
                    'query' => ['limit' => 0],
                ], $this->headers);
                $data = json_decode($response->getBody()->getContents(), true);
                $proc_merchants =  array_map(fn($p_m) => $this->addObjectId($p_m), $data);
                if(is_array($data)){
                    $pagination = $data['meta']['pagination'] ?? [];
                    $pagination = array_diff_key($pagination, ['links' => null]);
                }else{
                    $pagination = [];
                }
                return ['campaign_accounts' => $proc_merchants, 'pagination' => $pagination];
            } catch (ClientException|ServerException $err) {
                throw new Exception($this->manageError(['source' => 'API list campaign accounts'], $err, true), $err->getCode());
            } catch (GuzzleException|Throwable $err) {
                throw new Exception($this->manageError(['source' => 'API list campaign accounts'], $err), $err->getCode());
            }
    }
}