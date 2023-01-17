<?
$version="0.2";

  /* showstats: Spiros Ioannou 2009
     version 0.1: read queries and display them in boxes.
   */

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>

  <meta name="generator" content="vim">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <link rel="stylesheet" type="text/css" href="stats.css" />
</head>
<body bgcolor='#fff'>

<?
$msg="";
/* open DB connection */
$dbfile="calls.db";
$debug=0;
error_reporting (E_ALL);
try {
//$dbh = new PDO("sqlite:$dbfile",'','', array(PDO::ATTR_PERSISTENT => true));
 $dbh = new PDO("sqlite:$dbfile");

  $msg.="<p>DB:Connected</p>"; 
} 
catch (PDOException $e) {
  print "\nDatabase Error!: " . $e->getMessage() . "<br>\n";
  die();
}


// PARSE START/END ////////////////////////////////////////////////////////
?>

<div id='header'>
<table class='tblfrm' cellspacing=0 cellpadding=0 border=0>
<tr>
<td><span class='big'>Call Statistics</span><br>Spiros Ioannou 2009-<br> Version <?=$version?></td>

<td style='padding-right:10px;'> </td>
<td style='border-left:solid 1px black;padding-left:10px;'> </td>
<td>

<?

foreach($_GET as $k => $v) { $$k=$v; }

if (!isset($tstartday)) {
  $tstartday=1;
  $tstartmonth=date('n');
  $tstartyear=date('Y');

  $tendday=date('j');
  $tendmonth=date('n');
  $tendyear=date('Y');
  $type='';
}
$tstartts=mktime(0,0,0,$tstartmonth,$tstartday,$tstartyear); //timestamp
$tendts=mktime(23,59,59,$tendmonth,$tendday,$tendyear); //timestamp

if (!isset($stationfilt)) $stationfilt=array();

//FROM
echo "<form method=get>\n";
echo "Date Start:<select name=tstartday>\n";
for ($i=1; $i<=31; $i++){
  if ($tstartday == $i) $s="selected"; else $s="";
  echo "<option $s value='$i'>$i</option>\n";
}
echo "\n</select>";

echo "</td>\n\n<td> ";

echo "<select name=tstartmonth>\n";
for ($i=1; $i<=12; $i++){
  if ($i == $tstartmonth) $s="selected"; else $s="";
  echo "<option $s value='$i'>$i</option>\n";
}
echo "\n</select>";

echo "</td>\n\n<td> ";
echo "<select name=tstartyear>\n";
for ($i=2009; $i<=2020; $i++){
  if ($i == $tstartyear) $s="selected"; else $s="";
  echo "<option $s value='$i'>$i</option>\n";
}
echo "</select>\n";

echo "</td>\n";


echo "\n<td style='padding-right:10px;'> </td>\n";
echo "<td style='border-left:solid 1px black;padding-left:10px;'> </td>\n";

//TO
echo "\n<td> ";
echo "Date End:<select name=tendday>\n";
for ($i=1; $i<=31; $i++){
  if ($tendday == $i) $s="selected"; else $s="";
  echo "<option $s value='$i'>$i</option>\n";
}
echo "\n</select>";
echo "</td>\n\n<td> ";

echo "<select name=tendmonth>\n";
for ($i=1; $i<=12; $i++){
  if ($i == $tendmonth) $s="selected"; else $s="";
  echo "<option $s value='$i'>$i</option>\n";
}
echo "\n</select>";

echo "</td>\n\n<td> ";
echo "<select name=tendyear>\n";
for ($i=2009; $i<=2020; $i++){
  if ($i == $tendyear) $s="selected"; else $s="";
  echo "<option $s value='$i'>$i</option>\n";
}
echo "\n</select>";
echo "</td>\n";
?>

<td style='padding-right:10px;'> </td>
<td style='border-left:solid 1px black;padding-left:10px;'> </td>
<?
$sth=db_execute($dbh,"SELECT distinct stationno FROM calls WHERE stationno !='' order by stationno asc");
//$qres=$sth->fetchAll(PDO::FETCH_NUM);
$stations="";
while ($r=$sth->fetch(PDO::FETCH_ASSOC)) $stations[]=$r['stationno'];

echo "<td>\n ";
echo "<select multiple size=5 name=stationfilt[]>\n";
if (!isset($stationfilt) || empty($stationfilt[0])) { $s="SELECTED" ; };
echo "<option $s value=''>All</option>\n";
for ($i=0; $i<count($stations); $i++){
  if (in_array($stations[$i] , $stationfilt)) $s="selected"; else $s="";
  echo "<option $s value='{$stations[$i]}'>{$stations[$i]}</option>\n";
}
echo "\n</select>";
echo "</td>\n";


//print_r($stations);
$type_t=array("line","accline","gbar","accbar");
echo "<td>\n ";
echo "<select multiple size=5 name=type>\n";
if (!isset($type) || empty($type)) { $s="SELECTED" ; $type="";};
echo "<option $s value=''>Default</option>\n";
for ($i=0; $i<count($type_t); $i++){
  if ($type_t[$i]==$type) $s="selected"; else $s="";
  echo "<option $s value='{$type_t[$i]}'>{$type_t[$i]}</option>\n";
}
echo "\n</select>";
echo "</td>\n";
?>

<td style='padding-right:10px;'> </td>
<td style='border-left:solid 1px black;padding-left:10px;'> </td>
<td>
Dates affect all results.<br>Station selection affects only graphs.
</td>




  <td style='padding-right:10px;'> </td>
  <td style='border-left:solid 1px black;padding-left:10px;'> </td>
  <td> 
<input type=submit>
</form>
</td>
</tr>
</table>
</div>


<?
// /PARSE START/END ////////////////////////////////////////////////////////

// /OPEN DB ////////////////////////////////////////////////////////

/* define queries */
$query[0]['sql']="SELECT stationno,phone_number from calls limit 20";
$query[0]['colnames']=array("station number","phone number");
$query[0]['description']="show everything";

include("queries.php");
//print_r($query);
$nqueries=count($query);
$msg.="<p>".(1+$nqueries)." queries defined</p>";


  // for all defined queries
for ($qn=0;$qn<$nqueries;$qn++) {
  $bgc='#'.dechex(rand(180,255)).dechex(rand(180,255)).dechex(rand(200,255)); //random bg

  if (isset($query[$qn]['deftype'])) {
    if ($query[$qn]['deftype']=="pie") $isgraph=2;
    else $isgraph=1;
  }
  else 
    $isgraph=0;


  if (isset($query[$qn]['nostation']) && ($query[$qn]['nostation']==1)) 
    $nostation=1;
  else
    $nostation=0;




  if ($isgraph) {
    if ($isgraph==2)
      $cl='graphcontainer2' ; 
    else
      $cl='graphcontainer' ; 
    $bgc="#fff";
    $size="width:".($query[$qn]['w']+0)."px;height:".($query[$qn]['h']+0)."px;";
    $caption="";
  }
  else {
    $cl='tablecontainer';
    $size="";
  }
  echo "\n<div class='$cl' style='background-color:$bgc;$size'>\n";

  //station filter only for graphics
  if ($isgraph && (!$nostation) && isset($stationfilt[0]) && count($stationfilt) && strlen($stationfilt[0])) {
    $stationlist=implode(",",$stationfilt);
    $stationwhere=" AND stationno IN ($stationlist) ";
  }
  else 
    $stationwhere="";

  $sql=$query[$qn]['sql'];
  $sql=str_replace('WHERE ',"WHERE datetime>=$tstartts AND datetime<=$tendts $stationwhere AND ",$sql);
  //echo "$sql<br>";

  if (!$isgraph) {
    echo "\n<table class='tbl1'>\n";
    echo "<caption title=\"$sql\">".($qn+1)."/$nqueries: {$query[$qn]['description']}</caption>\n";
  }




  $sth=db_execute($dbh,$sql);
  $qres=$sth->fetchAll(PDO::FETCH_NUM);
  $nres=count($qres);
  

  if ($isgraph) {
    $yaxis=$query[$qn]['yaxis']; //axis with y values. other axes contain x values (other bars/lines)
    $xaxis=$query[$qn]['xaxis']; 
    $labels=$query[$qn]['labels']; 
    $xtitle=urlencode($query[$qn]['xtitle']); 
    $ytitle=urlencode($query[$qn]['ytitle']); 
    $title=urlencode(($qn+1)."/$nqueries: ".$query[$qn]['description']); 
    $format=$query[$qn]['format']; 

    if (!isset($type)|| $type=='' || $query[$qn]['deftype']=="pie") 
      $graphtype=$query[$qn]['deftype']; 
    else
      $graphtype=$type;

    $w=$query[$qn]['w']; 
    $h=$query[$qn]['h']; 
    //gather all column values in variables data1,data2 ..
    for ($qr=0;$qr<$nres;$qr++) { //rows
      for ($c=0;$c<count($qres[$qr]);$c++) { //columns
	$n="data$c"; //variable name
	if (!isset($$n)) $$n=array();
	array_push($$n,$qres[$qr][$c]);
      }
    }
    //columns, convert data arrays to comma sep GET vars

    if ($graphtype=="pie")
      $imgurl="drawpie.php?w=$w&amp;h=$h&amp;yaxis=$yaxis&xaxis=$xaxis&labels=$labels".
	    "&ytitle=$ytitle&xtitle=$xtitle&title=$title&format=$format&type=$graphtype";
    else
      $imgurl="draw1.php?w=$w&amp;h=$h&amp;yaxis=$yaxis&xaxis=$xaxis&labels=$labels".
	    "&ytitle=$ytitle&xtitle=$xtitle&title=$title&format=$format&type=$graphtype";
    
    if (count($qres)) {
      for ($c=0;$c<count($qres[0]);$c++) { 
	 $n="data$c";
	 $data[$c]=(implode(",",$$n));
	 $imgurl.="&amp;$n=".$data[$c];
	 $$n=array();
      }
      echo "\n<img TITLE=\"$sql\" src='$imgurl'>\n";
    }
    else { //no phone calls
      echo "<b>No phonecall data for this station and date range</b>";
    }
  }//if graph
  else
  //print result rows
    for ($qr=0;$qr<$nres;$qr++) {
      if ($qr==0) { //printout headers
	echo "\n<tr>\n";
	for ($c=0;$c<count($qres[$qr]);$c++) 
	  echo "<th>".$query[$qn]['colnames'][$c]."</th>";
	echo "\n</tr>\n";
      }
      echo "\n<tr>\n";
      //print columns
      for ($c=0;$c<count($qres[$qr]);$c++) {
	if (strstr($query[$qn]['colnames'][$c],"uration")) { // convert to H:M:S
	  $hms=gmdate("H:i:s", $qres[$qr][$c]);
	  echo "<td>$hms</td>";
	}
	else
	  echo "<td>".$qres[$qr][$c]."</td>";
      }//columns
      echo "\n</tr>\n";
    }//rows

  if (!$isgraph) echo "\n</table>\n";
  echo "</div>\n";
}//for query

function db_execute($dbh,$sql)
{
  $sth = $dbh->prepare($sql);
  $error = $dbh->errorInfo();
  if($error[0] && isset($error[2])) {
    echo "\n****<br><b>DB Error: ($sql): ".$error[2]."<br></b>";
    $backtrace = debug_backtrace();
    print_r($backtrace);
    return 0;
  }
  $sth->execute();
  return $sth;
}



echo "\n\n<div class='msg'>$msg</div>\n";

?>

</body>
</html>
