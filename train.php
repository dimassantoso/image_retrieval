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

        echo "Scanning : ";

        $folder = new DirectoryIterator('img');
        $total = 0;
        foreach ($folder as $fileInfo) {
          if($fileInfo->isDot()) 
            continue;
          $total++;
        }
        // echo " Found ".$total." files<br>";
        
        //start timer
        $start = microtime(true);
        set_time_limit(0);

        $i=0;
        foreach ($folder as $fileInfo) {
          if($fileInfo->isDot()) continue;
          $i++;
          ProcessImage($fileInfo->getFilename());
          outputProgress($i,$total);
        }
        $end = microtime(true) - $start;

        echo "<p>Done!</p>";
        echo "<p>Total time: ".number_format($end,2). " secs / ".number_format($end/$total,2)." per image</p>";

        /**
         * Output span with progress.
         *
         * @param $current integer Current progress out of total
         * @param $total   integer Total steps required to complete
         */
        function outputProgress($current, $total) {
            echo "<span style='position: absolute;z-index:$current;background:#FFF;'> Found ".$total." Files (Processed " . round($current / $total * 100) . "%)</span>";
            myFlush();
        }

        /**
         * Flush output buffer
         */
        function myFlush() {
            echo(str_repeat(' ', 256));
            if (@ob_get_contents()) {
                @ob_end_flush();
            }
            flush();
        }
      ?>

      <button class="ui button" onclick="window.open(&quot;index.php&quot;,&quot;_self&quot;); window.open(&quot;index.php&quot;,&quot;_self&quot;);">
        Back
      </button>
    </div>
  </div>
</body>

</html>