<?php 

namespace App\Libraries;
class BoxDev {
    private $write_path = null;
    public function __construct(){
        $this->write_path = WRITEPATH.'uploads/'; // Update this path accordingly
    }
  

    function folderItems($folder_id, $type = 'all'): array {
        // Initialize cURL
        $ch = curl_init();
    
        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, "https://api.box.com/2.0/folders/$folder_id/items");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken(),
        ]);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
    
        // Execute the request
        $response = curl_exec($ch);
    
        // Check for cURL errors
        if (curl_errno($ch)) {
            curl_close($ch);
            return [];
        }
    
        // Close the cURL handle
        curl_close($ch);
    
        // Decode the response
        $entries = json_decode($response, true)['entries'];
    
        // Filter by type if needed
        if ($type != 'all') {
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
        
        file_put_contents($this->write_path . $fileName, fopen($fileContentURL, 'r'));

        // Read the file content from the saved file
        $fileContent = file_get_contents($serverDownloadPath . $fileName);

        $content_type = mime_content_type($serverDownloadPath . $fileName);

        unlink($this->write_path . $fileName);
        // Return the file content and content type
        return [
            'content_type' => $content_type,
            'content' => $fileContent,
            'file_name' => $fileName  // Default name; adjust or extract as needed
        ];
    }

    function folderInfo($folder_id) {
        // Initialize cURL
        $ch = curl_init();
    
        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, "https://api.box.com/2.0/folders/$folder_id");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken(),
        ]);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
        // Execute the request
        $response = curl_exec($ch);
    
        // Check for cURL errors
        if (curl_errno($ch)) {
            curl_close($ch);
            return [];
        }
    
        // Close the cURL handle
        curl_close($ch);
    
        // Decode the response
        $folderInfo = json_decode($response, true);
    
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

    function deleteFile($file_id) {
        // Initialize cURL
        $ch = curl_init();
    
        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, "https://api.box.com/2.0/files/$file_id");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken(),
        ]);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
    
        // Execute the request
        $response = curl_exec($ch);
    
        // Check for cURL errors
        if (curl_errno($ch)) {
            curl_close($ch);
            return [];
        }
    
        // Close the cURL handle
        curl_close($ch);
    
        // Decode the response
        $deleteResponse = json_decode($response, true);
    
        return $deleteResponse;
    }
    
    
}