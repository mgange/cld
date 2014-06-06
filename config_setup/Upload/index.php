




<?php
/**
 *------------------------------------------------------------------------------
 * Upload images png
 *  *------------------------------------------------------------------------------
 *
 */
require_once('../../includes/pageStart.php');



if(isset($_FILES['png'])) {
    
    $filename = moveUploadsame('png', '../../status/image/', '/image\/.*/');
    
        if($filename) {
            session_flash('success', 'Your file was successfully uploaded.');
          //  die(header('Location: ../'));
        }else{
            session_flash('error', 'There was an error uploading your file.');
          //  die(header('Location: ./'));
        }
   }




require_once('../../includes/header.php');



$Entry = array();
$IFileName = array();
$path="c:/wamp/www/cld/status/image/";

// Opens the folder
$folder = opendir($path);
        // Loop trough all files in the folder
    $i=0;
    while (($Entry[$i] = readdir($folder)) != "") { 
           if (($Entry[$i] !=".") and ($Entry[$i]!=".."))  {$i++; }
           $j=$i;
	}       
   
	// Close folder
$folder = closedir($folder);
// define java script array
$js_array=json_encode($Entry);
?>

<div class="page-title">
    <h3 align="center">Upload New System or Exchange Loop Image File</h3>
    <h4 align="center">Expected File format is .png</h4>
</div>

<form method="POST" enctype="multipart/form-data">
    <div class="row file-hider">
        <button class="trigger btn span4 offset4" data-input="hidden-file" onclick='defjsarray(this);'>
            Choose Image File to Upload
   
        </button>
       <em class="filename align-center span4 offset4">&nbsp;</em> 
    </div>
   <?php
   
   
   
   
   ?>
    <br><br><br>

    <div class="row">
        <div class="span4 offset4">
            <button class="btn btn-large btn-block btn-info" type="submit">Upload</button>
        </div>
    </div>
    <input class="hidden hidden-file" id="PDF" type="file" name="png" accept="application/png">
</form>

<?php
//}else{
 //   session_flash('error', 'There was an error finding the ingredient');
  //  //die(header('Location: ../'));
//}

require_once('../../includes/footer.php');
?>
