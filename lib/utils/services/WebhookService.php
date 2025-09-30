<?php

namespace Payarc\PayarcSdkPhp\utils\services;

use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use Throwable;
class WebhookService extends BaseService
{


    /**
     * @throws Exception
     */
    public function create($userSettings=[]): array
    {
        return $this->setWebhook($userSettings);
    }

    /**
     * @throws Exception
     */
    public function list(): array
    {
        return $this->listWebhooks();
    }
    /**
     * @throws Exception
     */
    public function update($userSettings = []): array
    {
        return $this->setWebhook($userSettings);
    }

    /**
     * @throws Exception
     */
    public function delete($userSettings = []): bool
    {
        return $this->deleteWebhookSetting($userSettings);
    }


    /**
     * @throws Exception
     */
    public function listWebhooks()
    {
        try {
            $this->headers['Authorization'] = 'Bearer ' . $this->client->getBearerTokenAgent();
            $response = $this->client->request('GET', 'my-user-settings', ['query' => []], $this->headers);
            $data = json_decode($response->getBody(), true);
            return $this->addObjectId($data['data']);
        } catch (ClientException | ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API list webhooks'], $err, true), $err->getCode());
        } catch (GuzzleException | Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API list webhooks'], $err), $err->getCode());
        }
    }

    /**
     * @param array $userSettings
     * @param null $newData
     * @return array
     * @throws Exception
     */
    public function setWebhook(array $userSettings = [], $newData = null): array
    {
        try {
            if ($newData !== null) {
                $userSettings['value'] = $newData;
            }
            $this->headers['Authorization'] = 'Bearer ' . $this->client->getBearerTokenAgent();
            $response = $this->client->request('POST', 'my-user-settings', [
                'json' => $userSettings
            ], $this ->headers);
            $data = json_decode($response->getBody(), true);
            return $this->addObjectId($data['data']);
        } catch (ClientException | ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API set webhook setting'], $err, true), $err->getCode());
        } catch (GuzzleException | Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API set webhook setting'], $err), $err->getCode());
        }
    }

    /**
     * @param array $userSettings
     * @return bool
     * @throws Exception
     */
    public function deleteWebhookSetting(array $userSettings = []): bool
    {
        try {
            $this->headers['Authorization'] = 'Bearer ' . $this->client->getBearerTokenAgent();
            $response = $this->client->request('DELETE', 'my-user-settings', [
                'json' => $userSettings
            ], $this ->headers);
            return $response->getStatusCode() === 204;
        } catch (ClientException | ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API delete webhook setting'], $err, true), $err->getCode());
        } catch (GuzzleException | Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API delete webhook setting'], $err), $err->getCode());
        }
    }

}