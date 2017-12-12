<?php
  require 'function.php';
?>


<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Feature Extraction</title>
<link rel="stylesheet" type="text/css" href="semantic/dist/semantic.min.css">
<script
  src="https://code.jquery.com/jquery-3.1.1.min.js"
  integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="
  crossorigin="anonymous"></script>
<script src="semantic/dist/semantic.min.js"></script>
</head>

<body>
  <div class="ui container">
    <div class="ui segment">
      <?php
        $folder = new DirectoryIterator('img');
        $total = 0;
        foreach ($folder as $fileInfo) {
          if($fileInfo->isDot()) 
            continue;
          else if(isInDatabase($fileInfo->getFilename())) continue;
          else if (!isset($nextfile)) $nextfile = $fileInfo->getFilename();
          $total++;
        }

        if ($total > 0){
          echo "<p>Found ".$total." unprocessed images</p>\n";
          echo "<p>Processing image ".$nextfile."..</p>\n";

          //start timer
          $start = microtime(true);

          ProcessImage($nextfile);
          $end = microtime(true) - $start;

          echo "<p>Done!</p>\n";
          echo "<p>Process time: ".number_format($end,2). " secs</p>";
        }
        else{
          echo "<p>No unprocessed images found</p>\n";
        }

        function isInDatabase($filename) {
          require 'config.php';

          $result = mysqli_query($connection,"SELECT count(*) FROM images WHERE filename='".$filename."'");

          while($row = mysqli_fetch_array($result))
            $imagesnum = $row[0];

          if ($imagesnum > 0) 
            return true;
          else 
            return false;
        }
      ?>

      <button class="ui button" onclick="window.open(&quot;index.php&quot;,&quot;_self&quot;); window.open(&quot;index.php&quot;,&quot;_self&quot;);">
        Back
      </button>
      <button class="ui primary button" onclick="window.open(&quot;train.php&quot;,&quot;_self&quot;); window.open(&quot;train.php&quot;,&quot;_self&quot;);">
        Go
      </button>
    </div>
  </div>
</body>

</html>