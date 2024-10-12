<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Files</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
     <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
     <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
     <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.css">
     <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.js"></script>
     <style type="text/css">
         #alert{
             margin-top: 10px;
             color: red;
             font-weight: bolder;
             text-align: center;
             margin: 10px;
         }
         #attachments_table{
             margin-top: 10px;
         }
        .download_file{
             margin-left: 10px;
         }
         .delete_file{
             margin-left: 10px;
         }
         @media (max-width: 768px) {
            .download_file,.delete_file {
                 margin-left: 0;
             }
         }
         @media (max-width: 576px) {
            .btn {
                 width: 100%;
             }
         }
     </style>
</head>
</head>
<body>

<div class = 'container'>
    <div class = "row">
        <div class = "col-lg-12">
            <a href="<?=site_url('boxdev/file');?>" class = "btn btn-info">Back</a>
        </div>
    </div>

    <div class = "row">
        <div class = "col-lg-12" id = "alert">
            
        </div>
    </div>
    <div class = 'row'>
        <div class = 'col-lg-12'>
            <!-- Draw a DataTable to list files -->
             <table class = "table table-striped" id="attachments_table" class="display" width="100%">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Name</th>
                        <th>Size</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($attachments as $attachment):?>
                        <tr>
                            <td>
                                <div class = "btn btn-danger delete_file">Delete</div>
                                <div class = 'btn btn-success download_file_link' data-file_name = "<?=$attachment['name']?>" id = "<?=$attachment['file_id'];?>">Download File [Link]</div>
                                <div class = 'btn btn-success download_file' data-file_name = "<?=$attachment['name']?>" id = "<?=$attachment['file_id'];?>">Download File</div>

                            </td>
                            <td><?=$attachment['name']?></td>
                            <td><?=$attachment['size']?> bytes</td>
                        </tr>
                    <?php endforeach;?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#attachments_table').DataTable();
    });  

    $('.download_file').on('click', function() {
        const id = $(this).attr('id');
        const file_name = $(this).data('file_name');


        $.ajax({
            url: '<?=site_url('boxdev/download_file')?>',  // URL of your CodeIgniter 4 controller method
            type: 'POST',
            data: { file_id: id, file_name: file_name},  // Send the file ID or any other data if needed
            xhrFields: {
                responseType: 'blob'  // This is important for handling binary data (the file)
            },
            beforeSend: function(){
                $("#alert").html('Wait for the download to complete')
            },
            success: function(response) { 
                //Open the downloaded file in a new tab
                const blob = new Blob([response], { type: 'application/octet-stream' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = file_name;
                a.click();
            },
            error: function(xhr, status, error) {
                console.error('Download error:', error);
            },
            complete: function(){
                $("#alert").html('')
            }
        });

    });


    $('.download_file_link').on('click', function() {
        const id = $(this).attr('id');
        const file_name = $(this).data('file_name');
            
        // Make an AJAX request to download file via link
        $.ajax({
            url: '<?=site_url('boxdev/download_file_url')?>',
            type: 'POST',
            data: { file_id: id, file_name: file_name},
            beforeSend: function(){
                $("#alert").html('Wait for the download to complete')
            },
            success: function(fileURL) {
                window.location.href = fileURL
            },
            error: function(xhr, status, error) {
                $("#alert").html('An error occurred during the download')
            },
            complete: function(){
                $("#alert").html('')
            }
        });
    })
</script>
    
</body>
</html>