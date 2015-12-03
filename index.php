<?php
session_start();

if (!isset($_SESSION['UserData']['Username'])) {
	header("location:login.php");
}

require 'lib/aws-autoloader.php';

$msg = "";
$s3 = Aws\S3\S3Client::factory(['credentials' => array(
	'key' => '*********************',
	'secret' => '*********************'), 
	'region' => 'eu-central-1', 'version' => 'latest']);

if (isset($_FILES['file'])) {
	$file = $_FILES['file'];
	$name = $file['name'];
	$tmp_name = $file['tmp_name'];

	$tmp_file_path = "files/{$name}";
	move_uploaded_file($tmp_name, $tmp_file_path);
	
	$result = $s3->putObject(['Bucket' => 'bauboxbucket', 'Key' => $name, 'Body' => fopen($tmp_file_path, 'rb') , 'ACL' => 'public-read']);
	
	if ($result) {
		$msg = "<span><strong>Success!</strong> File uploaded succefuly</span>";
	} else {
		$msg = "<span style='color:red'>ERROR</span>";
		}
}

$iterator = $s3->getIterator('ListObjects', ['Bucket' => "bauboxbucket"]);


if (isset($_GET["delete"])) {
	$key = $_GET["delete"];
	$result = $s3->deleteObject(array(
		'Bucket' => 'bauboxbucket',
		'Key' => $key
	));
	
	if ($result) {
		$msg = "<span><strong></strong> File deleted succefuly</span>";
	}
	
	unlink("files/".$key);
}
	
   
if (isset($_GET['presigned'])) {
    
    $t = $_POST['time'];
    if(isset($t) && $t > 0 && $t < 1000){
    $time = "+".$t."minutes";
    $cmd = $s3->getCommand('GetObject', [
        'Bucket' => 'bauboxbucket',
        'Key'    => $_POST['key']
    ]);
    
    $request = $s3->createPresignedRequest($cmd, $time);
    
    
    echo "<p class='well'>".(string) $request->getUri()."</p>";
    
    }
    else{
        echo "<p class='alert alert-danger'>Enter a valid number</p>";
    }
    exit();
    }
?>
    <html>

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link href="https://bootswatch.com/paper/bootstrap.min.css" rel="stylesheet">
        <script type="text/javascript" src="src/bootstrap-filestyle.min.js"> </script>

        <link href="fileinput/css/fileinput.min.css" media="all" rel="stylesheet" type="text/css" />
        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

        <script src="fileinput/js/plugins/canvas-to-blob.min.js" type="text/javascript"></script>
        <script src="fileinput/js/fileinput.min.js"></script>

        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js" type="text/javascript"></script>

        <script src="fileinput/js/fileinput_locale_<lang>.js"></script>

        <title>File Upload</title>

        <style type="text/css">
        .cntr {
            margin-top: 120px;
        }
       .row-centered {
            text-align:center;
        }
       .col-centered {
            display:inline-block;
            float:none;
            text-align:left;
            margin-right:-4px;
        }

        </style>

    </head>

    <body>
        <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <a class="navbar-brand" href="index.php">Cloud Computing Project</a>
        </div>
        <div id="navbar">
          <ul class="nav navbar-nav navbar-right">
            <li><a href="logout.php">Logout</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>
        <div class="container">
            <div class="row">

                <div class="col-md-12 cntr">


                    <?php if($msg != ""){ ?>
                    <div class="row row-centered">
                    <div class="alert alert-success col-md-6 col-centered">
                        <?php echo  $msg;?>
                    </div>
                    </div>
                    <?php } ?>
                    
                    <div class="row row-centered" style="margin-bottom: 48px;">
                        <div class="col-md-6 col-centered">
                           <form action="index.php" method='post' enctype="multipart/form-data">
                                <label class="control-label"><strong>Select File</strong></label>
                                <input type="file" name="file"  class="file" data-preview-file-type="text">
                            </form> 
                        </div>
                    </div>
                    

                    <table class="table table-hover ">
                        <thead>
                            <tr>
                                <th>File</th>
                                <th>Size</th>
                                <th>Last Modified</th>
                                <th>Pre-Signed URL</th>
                                <th>Delete</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($iterator as $object): ?>
                           
                          
                            <tr>
                                <td><a href="<?php echo $s3->getObjectUrl('bauboxbucket',$object['Key']); ?>" download="<?php $object['Key']; ?>" ><?php echo $object['Key'];?></a></td>
                                <td><?php echo substr($object['Size'], 0, 3); ?> <?php $size = ""; if($object['Size'] < 1000){ $size = "Byte";} elseif($object['Size'] > 1000 && $object['Size'] < 1000000){$size="Kb";} else{$size = "Mb";} echo $size; ?></td>
                                <td><?php echo substr($object['LastModified'], 0, 10); ?></td>
                                <td><a class="modalOpen" data-toggle="modal" data-target=".presignedModal" data-key="<?php echo $object['Key'];?>" href="#"> Pre-Signed URL </a></td>
                                <td><a href="index.php?delete=<?php echo $object['Key'];?>"> Delete </a></td>
                            </tr>
                        <?php endforeach;?>
                    </tbody>
                </table>
                
            <div class="modal fade presignedModal" role="dialog">
                <div class="modal-dialog">
            
                  <div class="modal-content">
                    <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal">&times;</button>
                      <h4 class="modal-title">Pre-Signed URL</h4>
                    </div>
                    <div class="modal-body">
                      
                      <form role="form" id="pre-signed">
                          <div class="form-group">
                            <input type="text" class="form-control time" name="time" placeholder="Enter time in minutes">
                            <input type="hidden" id="key" name="key" value="" />
                          </div>
                          <button type="submit" class="btn btn-primary">Submit</button>
                        </form>
                        <div id="result"></div>
                    </div>
                    <div class="modal-footer">
                      <button id="modalClose" type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                    </div>
                  </div>
                  
                </div>
              </div>
              
              
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#pre-signed').submit(function(e) {
                e.preventDefault();
                
                $.ajax({
                    type: "post",
                    url: "index.php?presigned",
                    data: $(this).serialize(),
                    dataType: "html",
                    success: function(data) {
                        $('#result').html(data);
                    },
                    error: function(){
                          alert('Error');
                    }
                });
            });
        });
        
        $("#modalClose").click(function () {
             $(".modal-body .time").val("");
             $("#result").html(" ");
             
        });
        
        $('.modalOpen').click(function() {
             $(".modal-body #key").val( $(this).data('key') );
        });
    </script>
</body>
</html>


