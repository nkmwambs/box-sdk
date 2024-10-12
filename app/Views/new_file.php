<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload File</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
     <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
     <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>

<div class = 'container'>
<div class = "row">
        <div class = "col-lg-12">
            <a href="<?=site_url('boxdev');?>" class = "btn btn-info">List Files</a>
        </div>
    </div>
    <div class = 'row'>
        <div class = 'col-xs-12'>
            <!-- Upload a file form. Use forn-group -->
             <form id = 'upload_form' action="<?=site_url('boxdev/upload_file')?>" method='post' enctype='multipart/form-data'>
                <div class="form-group">
                    <label for="file">Select File:</label>
                    <input type="file" class="form-control-file" id="file" name="file">
                </div>
                <button type="submit" class="btn btn-primary">Upload</button>
            </form>
             
        </div>
    </div>
</div>
    
</body>
</html>