<?php 
	require 'function.php';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Image Retrieval</title>
<link rel="stylesheet" type="text/css" href="semantic/dist/semantic.min.css">
<script
  src="https://code.jquery.com/jquery-3.1.1.min.js"
  integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="
  crossorigin="anonymous"></script>
<script src="semantic/dist/semantic.min.js"></script>
<style type="text/css">
	body{
		padding:50px;
	}
	.unselected{
		opacity: 0.4;
	}
	.unselected:hover{
		opacity:1.0;
    	cursor:pointer;
	}
	.selected{
    	opacity:1.0;
    	cursor:pointer;
   	}
</style>
</head>

<body>
	<div class="ui container" style="margin-bottom: 10px;">
		<div class="ui header"><center>Image Retrieval : Fruits and Vegetables</center></div>
		<div class="ui segment">
  			<h3 class="ui header">Query Image : </h3>
  			<div class="ui tiny bordered images">
  				<div class="ui ten column grid">
  				<?php
  					foreach (new DirectoryIterator('query') as $fileInfo) {
  						if($fileInfo->isDot()) 
  							continue;
  						else if (isset($_GET["file"]) && $_GET["file"] == $fileInfo->getFilename()) 
  							$class = "selected";
  						else 
  							$class = "unselected";

  						echo "<div class='column'>
  								<a href='index.php?file=".$fileInfo->getFilename()."&page=1'><img class='".$class."' src='query/".$fileInfo->getFilename()."'></a>
  							</div>";
      				}
      			?>
      			</div>
			</div>
  		</div>
  		<?php if (isset($_GET["file"]) && strlen($_GET["file"]) > 0){
  			  		

  		?>
  		<div class="ui segment">
  			<h3 class="ui header">Result : </h3>
  			<div class="ui three column grid">
  				<div class="three column centered row">
    				<div class="column">
    					<?php
    						// paging
			        if ($_GET["page"] == 1)
			        	$prev_page = 1;
			        else
			        	$prev_page = $_GET["page"] - 1;

			        $next_page = $_GET["page"] + 1;

			        echo "
			        <div class='ui two column centered grid'><div class='ui buttons centered' style='margin-bottom:10px;'>
			        	<a class='ui labeled icon button' href='index.php?file=".$_GET["file"]."&page=".$prev_page."'><i class='left arrow icon'></i>Previous</a>
			        	<a class='ui right labeled icon button' href='index.php?file=".$_GET["file"]."&page=".$next_page."'><i class='right arrow icon'></i>Next</a>
					</div></div>
			        ";
    					?>
    				</div>
  				</div>
  				<?php
			      
			        //start timer
			        $start = microtime(true);
			        // Parse image to get y,i,q values
			        list($y_values, $i_values, $q_values) = ParseImage("query/".$_GET["file"]);
			        // Dempose and trunctate
			        DecomposeImage($y_values);
			        $y_trunc = TruncateCoeffs($y_values, $COEFFNUM);
			        DecomposeImage($i_values);
			        $i_trunc = TruncateCoeffs($i_values, $COEFFNUM);
			        DecomposeImage($q_values);
			        $q_trunc = TruncateCoeffs($q_values, $COEFFNUM);
			        require 'config.php';

			        // Initialize scores and filenames
			        $result = mysqli_query($connection,"SELECT * FROM images");
			        while($image = mysqli_fetch_array($result)){
			          $scores[$image['image_id']] = $w['Y'][0]*ABS($y_values[0][0] - $image['Y_average'])
			          + $w['I'][0]*ABS($i_values[0][0] - $image['I_average']) 
			          + $w['Q'][0]*ABS($q_values[0][0] - $image['Q_average']);
			          $filenames[$image['image_id']] = $image['filename'];
			        }
			        // compare query coefficients with database
			        for ($i = 0; $i < $COEFFNUM; $i++) {
			          $query = "SELECT * FROM coeffs_y WHERE X = ".$y_trunc['x'][$i]." AND Y = ".$y_trunc['y'][$i]." AND SIGN = '".$y_trunc['sign'][$i]."'";
			          $result = mysqli_query($connection,$query);  
			          while($coeff_y = mysqli_fetch_array($result)){
			            $scores[$coeff_y['image']] -= $w['Y'][bin($coeff_y['X'],$coeff_y['Y'])];
			          }
			          $query = "SELECT * FROM coeffs_i WHERE X = ".$i_trunc['x'][$i]." AND Y = ".$i_trunc['y'][$i]." AND SIGN = '".$i_trunc['sign'][$i]."'";
			          $result = mysqli_query($connection,$query);  
			          while($coeff_i = mysqli_fetch_array($result)){
			            $scores[$coeff_i['image']] -= $w['I'][bin($coeff_i['X'],$coeff_i['Y'])];
			          }
			          $query = "SELECT * FROM coeffs_q WHERE X = ".$q_trunc['x'][$i]." AND Y = ".$q_trunc['y'][$i]." AND SIGN = '".$q_trunc['sign'][$i]."'";
			          $result = mysqli_query($connection,$query);  
			          while($coeff_q = mysqli_fetch_array($result)){
			            $scores[$coeff_q['image']] -= $w['Q'][bin($coeff_q['X'],$coeff_q['Y'])];
			          }
			        }
			        mysqli_close($connection);
			        asort($scores,SORT_NUMERIC);

			        // show results
			        $i = 0;
			        foreach($scores as $key => $value){
			          if ($i >= 9*($_GET["page"]-1) && $i <= (9*$_GET["page"])-1){
			            echo 
			            	"<div class='column'>
			            		<div class='ui fluid card'>
  								<div class='image'>
  									<img class='' src='img/".$filenames[$key]."'>
  								</div>
  								<div class='content'>
  									<a class='header' href='#'>$filenames[$key]</a>
								    <div class='meta'>
								      <a>$value</a>
								    </div>
  								</div>
  								</div>
  							</div>
  							";

			          }
			          $i++;
			        }

			        // echo "
			        // <a class='ui labeled icon button' href='index.php?file=".$_GET["file"]."&page=".$prev_page."'><i class='left arrow icon'></i>Previous</a>
			        // <a class='ui right labeled icon button' href='index.php?file=".$_GET["file"]."&page=".$next_page."'><i class='right arrow icon'></i>Next</a>";
			        echo "
			        <div class='ui two column centered grid'><div class='ui buttons centered' style='margin-bottom:10px;'>
			        	<a class='ui labeled icon button' href='index.php?file=".$_GET["file"]."&page=".$prev_page."'><i class='left arrow icon'></i>Previous</a>
			        	<a class='ui right labeled icon button' href='index.php?file=".$_GET["file"]."&page=".$next_page."'><i class='right arrow icon'></i>Next</a>
					</div></div>
			        ";
			        // echo " </td></tr>\n";
			        // echo " <tr><td>Execution time: ".number_format(microtime(true) - $start,2). "</td>\n";
			        // echo " <td class='right'>Images ".(18*($_GET["page"]-1)+1)." to ".(18*$_GET["page"])."</tr>\n";
			      
			    ?>
			</div>
			<?php }?>
  		</div>
	</div>
</body>

</html>