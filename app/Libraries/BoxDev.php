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

    function downloadFile($file_id){
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

        // log_message('error', $fileURL);

        return $fileURL;
    }

    // function downloadFile($file_id, $fileName){
    //     // Initialize cURL session
    //     $curl = curl_init();

    //     // Set the cURL options
    //     curl_setopt_array($curl, [
    //         CURLOPT_URL => "https://api.box.com/2.0/files/$file_id/content",
    //         CURLOPT_RETURNTRANSFER => true, // To return the response as a string
    //         CURLOPT_HTTPHEADER => [
    //             'Authorization: Bearer '.$this->accessToken(), // Add authorization header
    //         ],
    //         CURLOPT_CUSTOMREQUEST => 'GET', // Set the request method to GET
    //     ]);

    //     $fileContent = curl_exec($curl);

    //     // Check for errors
    //     if (curl_errno($curl)) {
    //         // Return an error message
    //         return ['error' => curl_error($curl)];
    //     }

    //     // Get the content type (for example, image/jpeg, application/pdf, etc.)
    //     $contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
    //     log_message('error', json_encode($fileContent));

    //     // Close the cURL session
    //     curl_close($curl);

    //     // Return the file content and content type
    //     return [
    //         'content' => $fileContent,
    //         'content_type' => $contentType,
    //         'file_name' => $fileName  // Default name; adjust or extract as needed
    //     ];
    // }

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
                'client_id' => 'j24rxqdh70kpp77f45dcokqpu8g008d3',
                'client_secret' => 'lB0Ad3NsYm8G6ZG9Q3Eo78YP9Q2IpzRz',
                'grant_type' => 'client_credentials',
                'box_subject_type' => 'enterprise',
                'box_subject_id' => '5051018',
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
    
}