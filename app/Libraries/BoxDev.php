<?php 

namespace App\Libraries;
class BoxDev {
    private $client;
    public function __construct(){
        $this->client = \Config\Services::curlrequest();
    }
    function folderItems($folder_id, $type = 'all'): array  {
        // type can be all, file or folder
        $response = $this->client->request('GET', "https://api.box.com/2.0/folders/$folder_id/items", [
            'headers' => [
                'Authorization' => 'Bearer '.$this->accessToken(),
            ],
            'http_errors' => false,
        ]);

        $entriesObj = $response->getBody();
        $entries = json_decode($entriesObj, true)['entries'];

        if($type != 'all'){
            $entries = array_filter($entries, function($entry) use ($type) {
                return $entry['type'] === $type;
            });
        }
       
        return $entries;
    }

    function uploadFile($folder_id, $filePath){

        // Get the file name from the path 
        $fileName = basename($filePath);

        // Initialize cURL session
        $curl = curl_init();

        // Set the cURL options
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://upload.box.com/api/2.0/files/content",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer ".$this->accessToken(),
                "Content-Type: multipart/form-data"
            ],
            CURLOPT_POSTFIELDS => [
                "attributes" => json_encode([
                    "name" => $fileName,
                    "parent" => ["id" => $folder_id]
                ]),
                "file" => new \CURLFile($filePath)
            ],
        ]);

        // Execute the request and store the response
        $response = curl_exec($curl);

        // Check for cURL errors
        if (curl_errno($curl)) {
            echo 'Error:' . curl_error($curl);
        }

        // Close the cURL session
        curl_close($curl);

        // Unlink the file 
        unlink($filePath);

        // Output the response
        return $response;
    
    }

    function downloadFileURL($file_id){
        // Initialize cURL session
        $curl = curl_init();

        // Set the cURL options
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.box.com/2.0/files/$file_id/content",
            CURLOPT_RETURNTRANSFER => true, // To return the response as a string
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer '.$this->accessToken(), // Add authorization header
            ],
            CURLOPT_CUSTOMREQUEST => 'GET', // Set the request method to GET
        ]);

        // Execute the request and get the response
        $response = curl_exec($curl);

        // Check for cURL errors
        if (curl_errno($curl)) {
            echo 'cURL Error: ' . curl_error($curl);
        } 
        
        $fileURL = $info = curl_getinfo($curl)['redirect_url'];

        // Close the cURL session
        curl_close($curl);

        return $fileURL;
    }

    function downloadFile($file_id, $fileName, $serverDownloadPath){
        $fileContentURL = $this->downloadFileURL($file_id);
        
        file_put_contents(WRITEPATH . 'uploads/' . $fileName, fopen($fileContentURL, 'r'));

        // Read the file content from the saved file
        $fileContent = file_get_contents($serverDownloadPath . $fileName);

        $content_type = mime_content_type($serverDownloadPath . $fileName);

        unlink(WRITEPATH . 'uploads/' . $fileName);
        // Return the file content and content type
        return [
            'content_type' => $content_type,
            'content' => $fileContent,
            'file_name' => $fileName  // Default name; adjust or extract as needed
        ];
    }

    function folderInfo($folder_id){
        $response = $this->client->request('GET', "https://api.box.com/2.0/folders/$folder_id", [
            'headers' => [
                'Authorization' => 'Bearer '.$this->accessToken(),
            ],
            'http_errors' => false,
            'timeout' => 30,
        ]);

        $folderInfoObj = $response->getBody();
        $folderInfo = json_decode($folderInfoObj, true);
        return $folderInfo;
    }

    function accessToken(): string {
        // Initialize cURL session
        $curl = curl_init();
        
        // Set the cURL options
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.box.com/oauth2/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
            ],
            CURLOPT_POSTFIELDS => http_build_query([
                'client_id' => env('box.client_id'),
                'client_secret' => env('box.client_secret'),
                'grant_type' => 'client_credentials',
                'box_subject_type' => 'enterprise',
                'box_subject_id' => env('box.subject_id'),
            ]),
        ]);
        
        // Execute the request and store the response
        $response = curl_exec($curl);
        
        // Check for cURL errors
        if (curl_errno($curl)) {
            echo 'Error:' . curl_error($curl);
        }
        
        // Close the cURL session
        curl_close($curl);
        
        // Decode the response to extract the access token
        $responseObj = json_decode($response);
        $token = $responseObj->access_token ?? '';
        // log_message('error', $token);
        return $token;
    }

    function deleteFile($file_id){
        $response = $this->client->request('DELETE', "https://api.box.com/2.0/files/$file_id", [
            'headers' => [
                'Authorization' => 'Bearer '.$this->accessToken(),
            ],
            'http_errors' => false,
        ]);

        $deleteResponseObj = $response->getBody();
        $deleteResponse = json_decode($deleteResponseObj, true);
        return $deleteResponse;
    }
    
}