<?php

namespace Payarc\PayarcSdkPhp\utils\services;

use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use Throwable;

class ApplicationService extends BaseService
{
    /**
     * @throws Exception
     */
    public function create($applicant = [])
    {
        return $this->addLead($applicant);
    }

    /**
     * @throws Exception
     */
    public function retrieve($applicant = [])
    {
        return $this->retrieveApplicant($applicant);
    }

    /**
     * @throws Exception
     */
    public function list()
    {
        return $this->applyApps();
    }

    /**
     * @throws Exception
     */
    public function update($obj, $newData = [])
    {
        return $this->updateApplicant($obj, $newData);
    }

    /**
     * @throws Exception
     */
    public function delete($applicant)
    {
        return $this->deleteApplicant($applicant);
    }

    /**
     * @throws Exception
     */
    public function add_document($applicant, $params = [])
    {
        return $this->addApplicantDocument($applicant, $params);
    }

    /**
     * @throws Exception
     */
    public function delete_document($document)
    {
        return $this->deleteApplicantDocument($document);
    }

    /**
     * @throws Exception
     */
    public function submit($applicant)
    {
        return $this->submitApplicantForSignature($applicant);
    }

    /**
     * @throws Exception
     */
    public function list_sub_agents()
    {
        return $this->subAgents();
    }
    /**
     * @throws Exception
     */
    public function addLead($applicant = [])
    {
        if (isset($applicant['agentId']) && str_starts_with($applicant['agentId'], 'usr_')) {
            $applicant['agentId'] = substr($applicant['agentId'], 4);
        }

        try {
            $this->headers['Authorization'] = 'Bearer ' . $this->client->getBearerTokenAgent();
            $response = $this->client->request('POST', "agent-hub/apply/add-lead", [
                'json' => $applicant,
            ], $this->headers);
            $data = json_decode($response->getBody()->getContents(), true);
            return $this->addObjectId($data);
        } catch (ClientException|ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API add lead'], $err, true), $err->getCode());
        } catch (GuzzleException|Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API add lead'], $err), $err->getCode());
        }
    }

    /**
     * @throws Exception
     */
    public function retrieveApplicant($applicant = [])
    {
        $applicantId = $applicant['object_id'] ?? $applicant;
        if (is_string($applicantId) && str_starts_with($applicantId, 'appl_')) {
            $applicantId = substr($applicantId, 5);
        }
        try {
            $this->headers['Authorization'] = 'Bearer ' . $this->client->getBearerTokenAgent();
            $response = $this->client->request('GET', 'agent-hub/apply-apps/' . $applicantId, [
                'query' => [
                ],
            ], $this->headers);
            $applicantData = json_decode($response->getBody()->getContents(), true);
            $docsResponse = $this->client->request('GET', "agent-hub/apply-documents/{$applicantId}", [
                'query' => ['limit' => 0],
            ], $this->headers);
            $docsData = json_decode($docsResponse->getBody(), true);

            unset($docsData['meta']);
            unset($applicantData['meta']);
            $applicantData['Documents'] = $docsData;
            if (!isset($applicantData['data']['object'])) {
                $applicantData['data']['object'] = 'ApplyApp';
            }
            return $this->addObjectId($applicantData);
        } catch (ClientException|ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API Apply apps status'], $err, true), $err->getCode());
        } catch (GuzzleException|Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API Apply apps status'], $err), $err->getCode());
        }
    }

    /**
     * @throws Exception
     */
    private function applyApps(): array
    {
        try {
            $this->headers['Authorization'] = 'Bearer ' . $this->client->getBearerTokenAgent();
            $response = $this->client->request('GET', 'agent-hub/apply-apps', [
                'query' => [
                    'limit' => 0,
                    'is_archived' => 0,
                ],
            ], $this->headers);
            $responseData = json_decode($response->getBody()->getContents(), true);
            $applications = array_map([$this, 'addObjectId'], $responseData['data']);
            $pagination = $responseData['meta']['pagination'] ?? [];
            unset($pagination['links']);
            return [
                'applications' => $applications,
                'pagination' => $pagination,
            ];
        } catch (ClientException|ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API list Apply apps'], $err, true), $err->getCode());
        } catch (GuzzleException|Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API list Apply apps'], $err), $err->getCode());
        }
    }

    /**
     * @throws Exception
     */
    public function updateApplicant($obj, $newData)
    {
        if (is_array($obj)) {
            $dataId = $obj['object_id'] ?? null;
        } else {
            $dataId = $obj;
        }

        if (str_starts_with($dataId, 'appl_')) {
            $dataId = substr($dataId, 5);
        }
        $defaultData = [
            'bank_account_type' => '01',
            'slugId' => 'financial_information',
            'skipGIACT' => true,
        ];

        $newData = array_merge($defaultData, $newData);

        try {
            $this->headers['Authorization'] = 'Bearer ' . $this->client->getBearerTokenAgent();
            $response = $this->client->request('PATCH', 'agent-hub/apply-apps/' . $dataId, [
                'json' => $newData,
            ], $this->headers);
            if ($response->getStatusCode() === 200) {
                return $this->retrieveApplicant($dataId);
            }
            $responseData = json_decode($response->getBody()->getContents(), true);
            return $this->addObjectId($responseData);
        } catch (ClientException|ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API update Application info'], $err, true), $err->getCode());
        } catch (GuzzleException|Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API update Application info'], $err), $err->getCode());
        }
    }

    /**
     * @throws Exception
     */
    public function deleteApplicant($applicant)
    {
        $applicantId = $applicant['object_id'] ?? $applicant;
        if (is_string($applicantId) && str_starts_with($applicantId, 'appl_')) {
            $applicantId = substr($applicantId, 5);
        }
        try {
            $this->headers['Authorization'] = 'Bearer ' . $this->client->getBearerTokenAgent();
            $response = $this->client->request('DELETE', 'agent-hub/apply/delete-lead', [
                'json' => ['MerchantCode' => $applicantId]
            ], $this->headers);
            $data = json_decode($response->getBody()->getContents(), true);
            return $this->addObjectId($data['data']);
        } catch (ClientException|ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API Apply apps delete'], $err, true), $err->getCode());
        } catch (GuzzleException|Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API Apply apps delete'], $err), $err->getCode());
        }
    }

    /**
     * @throws Exception
     */
    public function addApplicantDocument($applicant, $params)
    {
        if (is_array($applicant)) {
            $applicantId = $applicant['object_id'] ?? null;
        } else {
            $applicantId = $applicant;
        }
        if (str_starts_with($applicantId, 'appl_')) {
            $applicantId = substr($applicantId, 5);
        }
        $data = [
            'MerchantCode' => $applicantId,
            'MerchantDocuments' => [$params],
        ];

        try {
            $this->headers['Authorization'] = 'Bearer ' . $this->client->getBearerTokenAgent();
            $response = $this->client->request('POST', 'agent-hub/apply/add-documents', [
                'json' => $data,
            ], $this->headers);
            $data = json_decode($response->getBody()->getContents(), true);
            return $this->addObjectId($data);
        } catch (ClientException|ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API Apply documents add'], $err, true), $err->getCode());
        } catch (GuzzleException|Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API Apply documents add'], $err), $err->getCode());
        }
    }

    /**
     * @throws Exception
     */
    public function deleteApplicantDocument($document)
    {
        $documentId = $document['object_id'] ?? $document;
        if (is_string($documentId) && str_starts_with($documentId, 'doc_')) {
            $documentId = substr($documentId, 4);
        }
        try {
            $this->headers = [];
            $this->headers['Authorization'] = 'Bearer ' . $this->client->getBearerTokenAgent();
            $response = $this->client->request('DELETE', 'agent-hub/apply/delete-documents', [
                'json' => [
                    'MerchantDocuments' => [
                        ['DocumentCode' => $documentId]
                    ],
                ],
            ], $this->headers);
            $data = json_decode($response->getBody()->getContents(), true);
            return $this->addObjectId($data);
        } catch (ClientException|ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API Apply document delete'], $err, true), $err->getCode());
        } catch (GuzzleException|Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API Apply document delete'], $err), $err->getCode());
        }
    }

    /**
     * @throws Exception
     */
    public function submitApplicantForSignature($applicant)
    {
        $applicantId = $applicant['object_id'] ?? $applicant;
        if (is_string($applicantId) && str_starts_with($applicantId, 'appl_')) {
            $applicantId = substr($applicantId, 5);
        }
        try {
            $this->headers['Authorization'] = 'Bearer ' . $this->client->getBearerTokenAgent();
            $response = $this->client->request('POST', 'agent-hub/apply/submit-for-signature', [
                'json' => ['MerchantCode' => $applicantId]
            ], $this->headers);
            $data = json_decode($response->getBody()->getContents(), true);
            return $this->addObjectId($data);
        } catch (ClientException|ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API Apply submit for signature'], $err, true), $err->getCode());
        } catch (GuzzleException|Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API Apply submit for signature'], $err), $err->getCode());
        }
    }

    /**
     * @throws Exception
     */
    public function subAgents()
    {
        try {
            $this->headers['Authorization'] = 'Bearer ' . $this->client->getBearerTokenAgent();
            $response = $this->client->request('GET', 'agent-hub/sub-agents', [
                'query' => [
                    'limit' => 0,
                ],
            ], $this->headers);
            $responseData = json_decode($response->getBody()->getContents(), true);
            $subAgents = array_map([$this, 'addObjectId'], $responseData['data']);
            $pagination = $responseData['meta']['pagination'] ?? [];
            unset($pagination['links']);
            return [
                'sub_agents' => $subAgents,
                'pagination' => $pagination,
            ];
        } catch (ClientException|ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API list sub agents'], $err, true), $err->getCode());
        } catch (GuzzleException|Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API list sub agents'], $err), $err->getCode());
        }
    }
}