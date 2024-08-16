<?php

namespace Payarc\PayarcSdkPhp\utils\services;
use DateTime;
use DateInterval;
use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use Throwable;

class DisputeService extends BaseService
{
    /**
     * @throws Exception
     */
    public function list($params=[])
   {
        return $this->listCases($params);
   }

    /**
     * @throws Exception
     */
    public function retrieve($params=[])
   {
        return $this->getCase($params);
   }

    /**
     * @throws Exception
     */
    public function add_document($dispute, $params=[])
   {
        return $this->addDocumentCase($dispute, $params);
   }

    /**
     * @throws Exception
     */
    private function listCases($params=[]): array
    {
        try{
            $formatDate = function (DateTime $date) {
                return $date->format('Y-m-d');
            };
            if (empty($params)) {
                $currentDate = new DateTime();
                $tomorrowDate = $formatDate((clone $currentDate)->add(new DateInterval('P1D')));  // Tomorrow
                $lastMonthDate = $formatDate((clone $currentDate)->sub(new DateInterval('P30D')));  // 30 days ago
                $params = [
                    'report_date[gte]' => $lastMonthDate,
                    'report_date[lte]' => $tomorrowDate,
                ];
            }

            $response = $this->client->request('GET', 'cases', [
                'query' => $params
            ], $this->headers);
            $data = json_decode($response->getBody()->getContents(), true);
            $cases = array_map(fn($case) => $this->addObjectId($case), $data['data']);
            $pagination = $responseData['meta']['pagination'] ?? [];
            unset($pagination['links']);
            return ['cases' => $cases, 'pagination' => $pagination];
        }catch (ClientException|ServerException $err) {
            throw new Exception($this->manageError(['source' => 'API get all disputes'], $err, true), $err->getCode());
        } catch (GuzzleException|Throwable $err) {
            throw new Exception($this->manageError(['source' => 'API get all disputes'], $err), $err->getCode());
        }
   }

    /**
     * @throws Exception
     */
    private function getCase($params=[])
   {
       $data_id = $params['object_id'] ?? $params;
       $data_id = str_starts_with($data_id, 'dis_') ? substr($data_id, 4) : $data_id;
         try {
              $response = $this->client->request('GET', 'cases/' . $data_id, [], $this->headers);
              $data = json_decode($response->getBody()->getContents(), true);
              $obj = $data['primary_case']['data'] ?? [];
              return $this->addObjectId($obj);
         } catch (ClientException|ServerException $err) {
              throw new Exception($this->manageError(['source' => 'API get dispute'], $err, true), $err->getCode());
         } catch (GuzzleException|Throwable $err) {
              throw new Exception($this->manageError(['source' => 'API get dispute'], $err), $err->getCode());
         }

   }

    /**
     * @throws Exception
     */
    private function addDocumentCase($dispute, $params=[])
   {
       $dispute_id = $dispute['object_id'] ?? $dispute;
       $dispute_id = str_starts_with($dispute_id, 'dis_') ? substr($dispute_id, 4) : $dispute_id;

       $headers = [];
       $form_data = '';
       $form_data_buffer = null;

       if (isset($params['DocumentDataBase64'])) {
           $binary_file = base64_decode($params['DocumentDataBase64']);
           $boundary = '----WebKitFormBoundary' . '3OdUODzy6DLxDNt8';
           $form_data .= "--$boundary\r\n";
           $form_data .= "Content-Disposition: form-data; name=\"file\"; filename=\"filename1.png\"\r\n";
           $form_data .= "Content-Type: " . ($params['mimeType'] ?? 'application/pdf') . "\r\n\r\n";
           $form_data .= $binary_file;
           $form_data .= "\r\n--$boundary--\r\n";

           if (isset($params['text'])) {
               $form_data .= "--$boundary\r\n";
               $form_data .= "Content-Disposition: form-data; name=\"text\"\r\n\r\n";
               $form_data .= $params['text'] . "\r\n";
               $form_data .= "--$boundary--\r\n";
           }

           $form_data_buffer = $form_data;
           $headers = [
               'Content-Type' => 'multipart/form-data; boundary=' . $boundary,
               'Content-Length' => strlen($form_data_buffer)
           ];
       }

       try {
           $response = $this->client->request('POST', 'cases/' . $dispute_id . '/evidence', [
               'body'    => $form_data_buffer  // Send the binary form data
           ], array_merge($this->headers, $headers));
           $sub_response = $this->client->request('POST', 'cases/' . $dispute_id . '/submit', [
               'json' => ['message' => $params['message'] ?? 'Case number#: xxxxxxxx, submitted by SDK']
           ], $this->headers);
           $data = json_decode($response->getBody()->getContents(), true);
           return $this->addObjectId($data);

       } catch (ClientException | ServerException $err) {
           throw new \Exception($this->manageError(['source' => 'API Dispute documents add'], $err, true), $err->getCode());
       } catch (GuzzleException | \Throwable $err) {
           throw new \Exception($this->manageError(['source' => 'API Dispute documents add'], $err), $err->getCode());
       }
   }
}