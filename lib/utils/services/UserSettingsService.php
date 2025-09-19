<?php

namespace Payarc\PayarcSdkPhp\utils\services;

use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use Throwable;
class UserSettingsService extends BaseService
{

    /**
     * @throws Exception
     */
    public function retrieveMerchant(): array
    {
        return $this->retrieveUserSettingsMerchant();
    }

    /**
     * @throws Exception
     */
    public function retrieveAgent(): array
    {
        return $this->retrieveUserSettingsAgent();
    }

    /**
     * @throws Exception
     */
    public function setMerchant($userSettings=[])
    {
        return $this->createUserSettingMerchant($userSettings);
    }

    /**
     * @throws Exception
     */
    public function setAgent($userSettings = [])
    {
        return $this->createUserSettingAgent($userSettings);
    }


    /**
     * @throws Exception
     */
    public function retrieveUserSettingsMerchant()
    {
        try {
            $this->headers['Authorization'] = 'Bearer ' . $this->client->getBearerToken();
            $response = $this->client->request('GET', 'my-user-settings', ['query' => []], $this->headers);
            $data = json_decode($response->getBody(), true);
            return $this->addObjectId($data['data']);
        } catch (ClientException | ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API retrive user settings'], $err, true), $err->getCode());
        } catch (GuzzleException | Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API retrive user settings'], $err), $err->getCode());
        }
    }

    /**
     * @throws Exception
     */
    public function retrieveUserSettingsAgent()
    {
        try {
            $this->headers['Authorization'] = 'Bearer ' . $this->client->getBearerTokenAgent();
            $response = $this->client->request('GET', 'my-user-settings', ['query' => []], $this->headers);
            $data = json_decode($response->getBody(), true);
            return $this->addObjectId($data['data']);
        } catch (ClientException | ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API retrive user settings'], $err, true), $err->getCode());
        } catch (GuzzleException | Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API retrive user settings'], $err), $err->getCode());
        }
    }


    /**
     * @throws Exception
     */
    public function createUserSettingMerchant($userSettings = []): array
    {
        try {
            $this->headers['Authorization'] = 'Bearer ' . $this->client->getBearerToken();
            $response = $this->client->request('POST', 'my-user-settings', [
                'json' => $userSettings
            ], $this ->headers);
            $data = json_decode($response->getBody(), true);
            return $this->addObjectId($data['data']);
        } catch (ClientException | ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API create user setting'], $err, true), $err->getCode());
        } catch (GuzzleException | Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API create user setting'], $err), $err->getCode());
        }
    }

    /**
     * @throws Exception
     */
    public function createUserSettingAgent($userSettings = []): array
    {
        try {
            $this->headers['Authorization'] = 'Bearer ' . $this->client->getBearerTokenAgent();
            $response = $this->client->request('POST', 'my-user-settings', [
                'json' => $userSettings
            ], $this ->headers);
            $data = json_decode($response->getBody(), true);
            return $this->addObjectId($data['data']);
        } catch (ClientException | ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API create user setting'], $err, true), $err->getCode());
        } catch (GuzzleException | Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API create user setting'], $err), $err->getCode());
        }
    }




}