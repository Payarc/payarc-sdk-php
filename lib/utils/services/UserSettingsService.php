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
        return $this->retrieveUserSettings();
    }

    /**
     * @throws Exception
     */
    public function retrieveAgent(): array
    {
        return $this->retrieveUserSettings(false);
    }

    /**
     * @throws Exception
     */
    public function setMerchant($userSettings=[])
    {
        return $this->createUserSetting($userSettings);
    }

    /**
     * @throws Exception
     */
    public function setAgent($userSettings = [])
    {
        return $this->createUserSetting($userSettings, false);
    }


    /**
     * @throws Exception
     */
    public function deleteMerchant($userSettings = [])
    {
        return $this->deleteUserSetting($userSettings);
    }

    /**
     * @throws Exception
     */
    public function deleteAgent($userSettings = [])
    {
        return $this->deleteUserSetting($userSettings, false);
    }


    /**
     * @throws Exception
     */
    public function retrieveUserSettings($isMerchant = true)
    {
        try {
            $bearerToken = $isMerchant ? $this->client->getBearerToken() : $this->client->getBearerTokenAgent();
            $this->headers['Authorization'] = 'Bearer ' . $bearerToken;
            $response = $this->client->request('GET', 'my-user-settings', ['query' => []], $this->headers);
            $data = json_decode($response->getBody(), true);
            return $this->addObjectId($data['data']);
        } catch (ClientException | ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API retrieve user settings'], $err, true), $err->getCode());
        } catch (GuzzleException | Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API retrieve user settings'], $err), $err->getCode());
        }
    }

    /**
     * @throws Exception
     */
    public function createUserSetting($userSettings = [], $isMerchant = true): array
    {
        try {
            $bearerToken = $isMerchant ? $this->client->getBearerToken() : $this->client->getBearerTokenAgent();
            $this->headers['Authorization'] = 'Bearer ' . $bearerToken;
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
    public function deleteUserSetting($userSettings = [], $isMerchant = true)
    {
        try {
            $bearerToken = $isMerchant ? $this->client->getBearerToken() : $this->client->getBearerTokenAgent();
            $this->headers['Authorization'] = 'Bearer ' . $bearerToken;
            $response = $this->client->request('DELETE', 'my-user-settings', [
                'json' => $userSettings
            ], $this ->headers);
            return $response->getStatusCode() === 204;
        } catch (ClientException | ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API delete user setting'], $err, true), $err->getCode());
        } catch (GuzzleException | Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API delete user setting'], $err), $err->getCode());
        }
    }

}