<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use CodeIgniter\HTTP\RequestInterface;


class BoxDev extends BaseController
{
    protected $client;
    protected $folder_id;
    public function __construct(){
        $this->client = \Config\Services::curlrequest();
        $this->folder_id = env('box.root_folder_id'); //'288788317902'; // Replace with your folder id.
    }
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);
    }
    public function getIndex()
    {
        // Get all files from attachments model and passw the result to view 
        $attachmentsModel = new \App\Models\Attachments();
        $attachments = $attachmentsModel->findAll();
        return view('list_files', ['attachments' => $attachments]);
    }

    public function getFile(){
        return view('new_file');
    }

    public function postDownload_file(){
        $post =$this->request->getPost();
        $fileId = $post['file_id'];
        $fileName = $post['file_name'];

        // log_message('error', json_encode($post));

        $server_download_path = WRITEPATH . 'uploads/';

        $boxDev = new \App\Libraries\BoxDev();
        $fileData = $boxDev->downloadFile($fileId, $fileName, $server_download_path);

        // Check if an error occurred
        if (isset($fileData['error'])) {
            return $this->response->setJSON(['error' => $fileData['error']]);
        }

        return $this->response
            ->setHeader('Content-Type', $fileData['content_type'])  // Automatically detect the content type
            ->setHeader('Content-Disposition', 'attachment; filename="' . $fileData['file_name'] . '"')  // Set the filename for download
            ->setBody($fileData['content']);  // Send the file content as the response body
    }

    public function postDownload_file_url(){
        $post = $this->request->getPost();
        $fileId = $post['file_id'];

        $boxDev = new \App\Libraries\BoxDev();
        $downloadURL = $boxDev->downloadFileURL($fileId);

        return $downloadURL;
    }

    public function postUpload_file(){
        $file = $this->request->getFile('file');
    
        if (!$file->isValid()) {
            return $this->response->setJSON(['error' => 'Invalid file']);
        }
    
        // Generate a new filename using the current date and time, keeping the original file extension
        $newFileName = date('Ymd_His') . '.' . $file->getExtension();
    
        // Move the file to the uploads folder with the new name
        $file->move(WRITEPATH . 'uploads', $newFileName);
    
        // Prepare the file path with the new name
        $filePath = WRITEPATH . 'uploads/' . $newFileName;
    
        // Instantiate your BoxDev library and upload the renamed file
        $boxDev = new \App\Libraries\BoxDev();
        $response = $boxDev->uploadFile($this->folder_id, $filePath);
    
        // Insert the file details to attachments table 
        $this->createAttachmentRecord(json_decode($response));
    
        // Redirect to the boxdev/index route after processing
        return redirect()->to('boxdev');
    }
    

    function createAttachmentRecord(object $createdFileResponse){
        $entries = $createdFileResponse->entries;
 
        $insertData = [];
        foreach($entries as $entry){
         $insertData[] = [
             'file_id' => $entry->id,
             'name' => $entry->name,
             'size' => $entry->size,
             'json_data' => json_encode($entry)
         ];
        }
 
        $attachmentModel = new \App\Models\Attachments();
        $attachmentModel->insertBatch($insertData);
     }

    function getFolder_items($folder_id, $type = 'all'): ResponseInterface{
        $boxDev = new \App\Libraries\BoxDev();
        $response = $boxDev->folderItems($folder_id, $type);

        return $this->response->setJSON($response);
    }

    function getFolder_info($folder_id){
        $boxDev = new \App\Libraries\BoxDev();
        $response = $boxDev->folderInfo($folder_id);

        return $this->response->setJSON($response);
    }

    function postDelete_file(){
        $post = $this->request->getPost();
        $fileId = $post['file_id'];

        $boxDev = new \App\Libraries\BoxDev();
        $response = $boxDev->deleteFile($fileId);

        // Delete record from attachments
        $attachmentModel = new \App\Models\Attachments();
        $attachmentModel->where('file_id', $fileId)->delete();

        return $this->response->setJSON(['success' => true]);
    }

}
