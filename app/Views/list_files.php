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
</head>
</head>
<body>

<div class = 'container'>
    <div class = "row">
        <div class = "col-lg-12">
            <a href="<?=site_url('boxdev/file');?>" class = "btn btn-info">Back</a>
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
                                <div class = "btn btn-danger">Delete</div>
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
        // alert(id)

        
        // $.ajax({
        //     url: '<?=site_url('boxdev/download_file')?>',  // URL of your CodeIgniter 4 controller method
        //     type: 'POST',
        //     data: { file_id: id, file_name: file_name},  // Send the file ID or any other data if needed
        //     xhrFields: {
        //         responseType: 'blob'  // This is important for handling binary data (the file)
        //     },
        //     success: function(blob, status, xhr) {
        //         // Create a download link
        //         var link = document.createElement('a');
        //         link.href = window.URL.createObjectURL(blob);

        //         // Set the file name using the `Content-Disposition` header, or set it manually
        //         var fileName = xhr.getResponseHeader('Content-Disposition').split('filename=')[1];
        //         link.download = fileName || 'downloaded_file';  // Default name if not available

        //         // Trigger the download
        //         document.body.appendChild(link);
        //         link.click();
        //         document.body.removeChild(link);
        //     },
        //     error: function(xhr, status, error) {
        //         console.error('Download error:', error);
        //     }
        // });

        // Make an AJAX request to download file
        $.ajax({
            url: '<?=site_url('boxdev/download_file')?>',
            type: 'POST',
            data: { file_id: id, file_name: file_name},
            success: function(response) {
                // alert(response)
                window.location.href = response
                // Open the downloaded file in a new tab
                // const blob = new Blob([response], { type: 'application/octet-stream' });
                // const url = URL.createObjectURL(blob);
                // const a = document.createElement('a');
                // a.href = url;
                // a.download = 'downloaded_file.txt';
                // a.click();
            }
        });
    })
</script>
    
</body>
</html>