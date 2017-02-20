<?php
header('Content-Type: text/html; charset=utf-8');
require 'parameters.php';

//connect to db and query for the files

if (file_exists('lookuphash.db')){
$db = new SQLite3('lookuphash.db'); 
$sql = "SELECT hash, filename, file_timestamp FROM lookup"; 
$results = $db->query($sql);
$files= array();


while ($res= $results->fetchArray(1))
{
//insert row into array
array_push($files, $res);

}


if (!empty($files))
{

	// sort the files array based on the modification time - usort is used

	usort($files,
	function ($a, $b)
	{
		if (($a['file_timestamp']) == ($b['file_timestamp']))
		{
			return 0;
		}
		else
		if (($a['file_timestamp']) > ($b['file_timestamp']))
		{
			return -1;
		}
		else
		{
			return 1;
		}
	}); //usort end
} // if !empty end
}// if filename exists

else 

{
echo "Δεν υπάρχει αρχείο βάσης δεδομένων";
echo "<br>Δοκιμάστε να μεταφορτώσετε μερικά αρχεία απο το script της python</br>";
echo "Η βάση δεδομένων θα δημιουργηθεί αυτόματα";
exit;


}

$page_directory = dirname($_SERVER["PHP_SELF"]);
$db->close();

?>
<html>
   <head><meta charset="utf-8" />
       <link href="http://mottie.github.io/tablesorter/css/theme.default.css" rel="stylesheet">
       <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script> 
       <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.28.1/js/jquery.tablesorter.min.js"></script>
       <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.28.1/js/extras/jquery.tablesorter.pager.min.js"></script> 
    <script>
        $(function(){
          $("#files_list").tablesorter({widthFixed: true, widgets: ['zebra']}) 
             .tablesorterPager({container: $("#pager")}); });
    </script>
       
     <style>
    table, th, td {border: 1px solid black;}
         .tablesorter  {width: auto;}


#ajaxSpinnerImage {
          display: none;
     }

.results_div {

 background-color: #00ff00;


}

    </style>
       
    </head>
   
    <body>
   <h1>
       Ανακοινώσεις σχολείου 2016-2017
        </h1>
           <table id='files_list' class="tablesorter"> 
               
               <thead>
                 <tr> 
                   <th>Σύνδεσμος</th> 
                    <th>Όνομα Αρχείου</th> 
                    <th>Ημερομηνία Αλλαγής</th> 
    
                  </tr>   
                   
                   
               </thead>
               <tbody>
                  <?php
			   for  ($x=0 ; $x< count($files); $x++)
			   {
			   
			   ?>
               <tr>
                 <td>
                     <a href="<?php 
			
			$link = 'get_file.php?id=' . (string)($files[$x]['hash']);
                        echo $link;						?>"> Download </a>
						
                 </td>
                
                <td>
                     <?php echo ($files[$x]['filename']);  ?>
                    
                </td>
                   <td>
                      
			       <?php echo date ("F d Y H:i:s.", $files[$x]['file_timestamp']);  ?>
                   </td>
               </tr>
			   
			    <?php }?>
			   
           </tbody></table>
           
          <div id="pager" class="pager">
	<form>
		  <img src="http://mottie.github.com/tablesorter/addons/pager/icons/first.png" class="first"/> 
          <img src="http://mottie.github.com/tablesorter/addons/pager/icons/prev.png" class="prev"/> 
		<input type="text" class="pagedisplay"/>
		  <img src="http://mottie.github.com/tablesorter/addons/pager/icons/next.png" class="next"/> 
        <img src="http://mottie.github.com/tablesorter/addons/pager/icons/last.png" class="last"/> 
		<select class="pagesize">
			<option selected="selected"  value="10">10</option>
			<option value="20">20</option>
			<option value="30">30</option>
			<option  value="40">40</option>
		</select>
	</form>
</div> 
<br>
     <div id="ziparch"	> 

	  <button type="button">Δημιουργία Zip αρχείου</button> 
	 
</div>	 
<div id="ajaxSpinnerContainer">
     <img src="ajax-loader.gif" id="ajaxSpinnerImage" title="working..." />
<span id='results'>

</span>

</div>


<script type="text/javascript">

$( document ).ready(function() {
   
   var itemsCount = $("#files_list tbody tr").length;
  if (itemsCount==0) {
      $('#files_list').html('<h3> Δεν υπάρχουν αρχεία προς δημοσίευση </h3>');
      $('#pager').hide();
      $('#ziparch').hide();
  }
   
   
    $('#spinner').addClass('loadinggif');
    $('#results').toggle();
   
   
   $(document)
          .ajaxStart(function () {
               $("#ajaxSpinnerImage").show();
          })
          .ajaxStop(function () {
               $("#ajaxSpinnerImage").hide();
          });


   
   
    $('#ziparch').click(function(){


      
       $.ajax({
          url: 'all_files.php',
          success: function(data){
              $('#spinner').removeClass('loadinggif');
             $('#results').addClass('results_div').show().html(data);
            $( "#download" ).on( "click", function() {
 
});
    }
});
});
});
	  


	  </script>
    
   </body>
</html>
