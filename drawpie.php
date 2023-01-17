<?
/* Spiros Ioannou 2009
   sivann at gmail.com
   web based graph generator based on jpgraph
   draws pies
   version 0.1 18/10/2009
*/

/* example:


draw1.php?w=400&h=300&data0=3,5,7,2&data1=7,1255796102,1255896102,1255996102&data2=5,3,3,2&format=num,ymd,num&xaxis=1&xtitle=xtitle&ytitle=ytitle&title=mytitle&labels=711,x,772&type=line

  ?w=400&
  h=300&
  data0=3,5,7,2&
  data1=7,1255796102,1255896102,1255996102&
  data2=5,3,3,2&
  format=num,ymd,num&
  xaxis=1&
  xtitle=xtitle&
  ytitle=ytitle&
  title=mytitle&
  labels=711,x,772&
  type=accbar

type can be: 
pie: grouped bar plot (default)
accbar: accumulated bar plot
line: line plot
accline: accumulated line plot

*/


require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_pie.php');


foreach($_GET as $k => $v) { $$k=$v; }


//xaxis values
$xname="data$xaxis";
$xdata=$$xname;

$dl="data$labels";
$labels_t=$$dl;

$xdata_arr=explode(",",$xdata);
$labels_arr=explode(",",$labels_t);

$colors=array();
for ($i=0;$i<count($xdata_arr);$i++) {
  $rgb=hsv2rgb(mt_rand(1,254),30,250); $color[0]='#'.dechex($rgb["R"]).dechex($rgb["G"]).dechex($rgb["B"]);
  $color='#'.dechex($rgb["R"]).dechex($rgb["G"]).dechex($rgb["B"]);
  array_push($colors,$color);
}

$graph = new PieGraph($w,$h);    
//$graph->SetColor('red');
$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->title->Set("$title");
$graph->SetFrame(true,$color[0]);
$graph->SetFrameBevel(1,true,'#a0a0a0');

$legends = array('June (%d)','June (%d)','June (%d)','June (%d)');

$p1=new PiePlot($xdata_arr);

//$p1->SetSliceColors($colors);
$p1->SetSize(0.2); 
$p1->SetLegends($labels_arr); 
$p1->SetCenter(0.35); //move to the left


$graph->Add($p1);
$graph->Stroke();

exit;

//get data series
for ($i=0;$i<10;$i++) {
  $n="data$i"; //data0 data1 ...
  if (isset($_GET[$n]))
    $$n = explode(",", $_GET[$n]);
  else 
    break;
}
$ndata=($i-1);


//apply required format conversions to data (seconds->date etc)
$format_t=explode(",",$format);

$xformat=$format_t[0];
$yformat=$format_t[1];



//label values
$dl="data$labels";
$labels_t=$$dl;

$x_unique=array_values(array_unique($xdata)); //unique keeps the original keys, array_values renumbers the array
$l_unique=array_values(array_unique($labels_t));

$yname="data$yaxis";
$ydata=$$yname;


//xofl[x][label]=y for all x and labels.  (X of Label)
for ($i=0;$i<count($x_unique);$i++){
  for ($k=0;$k<count($l_unique);$k++){
    $xofl[$x_unique[$i]][$l_unique[$k]]='0'; //prefill with nulls to have same number of values even for missing values
  }
}
ksort($xofl); //re-arrange keys.

//now fill-in the y-values
for ($i=0;$i<count($ydata);$i++){
  $xofl[$xdata[$i]][$labels_t[$i]]=$ydata[$i];
}

$xoflkeys=array_keys($xofl); //uniq x cols
for ($i=0;$i<count($xoflkeys);$i++){
  $xoflkeys_labels=array_keys($xofl[$xoflkeys[$i]]);//uniq labels in this x col
  for ($j=0;$j<count($xoflkeys_labels);$j++){
    $yofl[$xoflkeys_labels[$j]][]=$xofl[$xoflkeys[$i]][$xoflkeys_labels[$j]]; //yofl[label][]=array of values
  }
}



$graph = new Graph($w,$h);    
$graph->SetScale("textlin");
//$graph->SetShadow();
$graph->img->SetMargin(40,30,20,40);
$graph->img->SetAntiAliasing(); //cancels the weight

function format_hm($aLabel) {
    $hms=gmdate("G:i", $aLabel);
    return $hms;
}
function format_hms($aLabel) {
    $hms=gmdate("G:i:s", $aLabel);
    return $hms;
}


//$color[0]='#'.dechex(mt_rand(180,255)).dechex(mt_rand(180,255)).dechex(mt_rand(200,255)); //random 
$rgb=hsv2rgb(mt_rand(1,254),30,250); $color[0]='#'.dechex($rgb["R"]).dechex($rgb["G"]).dechex($rgb["B"]);
$graph->SetMarginColor($color[0]);
$graph->SetFrame(true,$color[0]);
$graph->SetFrameBevel(1,true,'#a0a0a0'); 


$yoflkeys=array_keys($yofl);

for ($i=0;$i<count($yoflkeys);$i++) {
//echo "<pre>";
  srand($i+make_seed());
  $x=implode(",",$yofl[$yoflkeys[$i]]);
//echo "<pre>$i ",$yoflkeys[$i].": ($x)\n";
  $x=explode(",",$x);
  if (strstr($type,"line")){
    //$color[$i]='#'.dechex(mt_rand(10,230)).dechex(mt_rand(10,230)).dechex(mt_rand(10,230)); //random 
    $rgb=hsv2rgb(mt_rand(0,255),230,220); $color[$i]='#'.dechex($rgb["R"]).dechex($rgb["G"]).dechex($rgb["B"]);
    $bplot[$i] = new LinePlot($x);
    $bplot[$i]->SetWeight(2);
    $bplot[$i]->SetColor($color[$i]);
    if ($yformat == "ts") 
      $bplot[$i]->value->SetFormatCallback('format_hm'); 

    //$bplot[$i]->SetColor($color[$i]);
    //$bplot[$i]->SetFillColor($color[$i]."@0.3");
    //$bplot[$i]->mark->SetType(MARK_UTRIANGLE);
    //$bplot[$i]->mark->SetColor('blue');
    //$bplot[$i]->mark->SetFillColor('red');
  }
  else {
    //$color[$i]='#'.dechex(mt_rand(160,230)).dechex(mt_rand(160,230)).dechex(mt_rand(160,240)); //random 
    $rgb=hsv2rgb(mt_rand(1,254),100,220); $color[$i]='#'.dechex($rgb["R"]).dechex($rgb["G"]).dechex($rgb["B"]);
    $bplot[$i]=new BarPlot($x);
    $bplot[$i]->SetFillColor($color[$i]);
    $bplot[$i]->SetFillGradient($color[$i],$color[$i].':0.7',GRAD_HOR);
  }
  $bplot[$i]->value->Show();
  $bplot[$i]->SetLegend($yoflkeys[$i]);
}


// Create the grouped bar plot
if ($type=="accbar")
  $gbplot = new AccBarPlot($bplot);
elseif ($type=="accline") {
  $gbplot = new AccLinePlot($bplot);
}
elseif ($type=="line") {
  $gbplot = $bplot;
}
else
  $gbplot = new GroupBarPlot($bplot);

// ...and add it to the graPH
$graph->Add($gbplot);

$graph->legend->Pos(0.02,0.5,'right','center');
$graph->title->Set("$title");

$graph->yaxis->title->Set("$ytitle");
$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT2,FS_BOLD);

$graph->xaxis->title->Set("$xtitle");
$graph->xaxis->title->SetFont(FF_FONT2,FS_BOLD);
//$graph->xaxis->SetFont(FF_ARIAL,FS_BOLD);


if ($yformat == "ts") 
  $graph->yaxis->SetLabelFormatCallback('format_hms'); 

if ($xformat=="ymd") {
  // Adjust the margin to make room for the X-labels
  $graph->SetMargin(75,100,40,100);
  $graph->xaxis->SetLabelAngle(90);
  $graph->xaxis->SetTitleMargin( 50);

  //change (int)ymd to (string)y-m-d
  $i=0;
  foreach ($xoflkeys as $key => &$val) {
    $val=sprintf("%d",$val);
    $v=$val[0].$val[1].$val[2].$val[3]."-".$val[4].$val[5]."-".$val[6].$val[7];
    $xlabels[$i++]=$v;
  }
}
else
  $xlabels=$xoflkeys;

$graph->xaxis->SetTickLabels($xlabels);
  

$graph ->yaxis->SetTitleMargin( 58);

// Display the graph
$graph->Stroke();

function convymd(&$aVal) {
  $aVal=$aVal[0].$aVal[1].$aVal[2].$aVal[3]."-".$aVal[4].$aVal[5]."-".$aVal[6].$aVal[7];
}

function convtimestamp(&$aVal) {
  $aVal = date('Y-m-d G:i',$aVal);
}


function make_seed()
{
  list($usec, $sec) = explode(' ', microtime());
  return (float) $sec + ((float) $usec * 100000);
}

function RGB_TO_HSV ($R, $G, $B)  // RGB Values:Number 0-255
{                                 // HSV Results:Number 0-1
   $HSL = array();

   $var_R = ($R / 255);
   $var_G = ($G / 255);
   $var_B = ($B / 255);

   $var_Min = min($var_R, $var_G, $var_B);
   $var_Max = max($var_R, $var_G, $var_B);
   $del_Max = $var_Max - $var_Min;

   $V = $var_Max;

   if ($del_Max == 0)
   {
      $H = 0;
      $S = 0;
   }
   else
   {
      $S = $del_Max / $var_Max;

      $del_R = ( ( ( $max - $var_R ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
      $del_G = ( ( ( $max - $var_G ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
      $del_B = ( ( ( $max - $var_B ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;

      if      ($var_R == $var_Max) $H = $del_B - $del_G;
      else if ($var_G == $var_Max) $H = ( 1 / 3 ) + $del_R - $del_B;
      else if ($var_B == $var_Max) $H = ( 2 / 3 ) + $del_G - $del_R;

      if (H<0) $H++;
      if (H>1) $H--;
   }

   $HSL['H'] = $H;
   $HSL['S'] = $S;
   $HSL['V'] = $V;

   return $HSL;
}

function hsv2rgb ($H, $S, $V)  // HSV Values:Number 0-1
{                                 // RGB Results:Number 0-255
    $RGB = array();
    $H/=255;
    $S/=255;
    $V/=255;

    if($S == 0)
    {
        $R = $G = $B = $V * 255;
    }
    else
    {
        $var_H = $H * 6;
        $var_i = floor( $var_H );
        $var_1 = $V * ( 1 - $S );
        $var_2 = $V * ( 1 - $S * ( $var_H - $var_i ) );
        $var_3 = $V * ( 1 - $S * (1 - ( $var_H - $var_i ) ) );

        if       ($var_i == 0) { $var_R = $V     ; $var_G = $var_3  ; $var_B = $var_1 ; }
        else if  ($var_i == 1) { $var_R = $var_2 ; $var_G = $V      ; $var_B = $var_1 ; }
        else if  ($var_i == 2) { $var_R = $var_1 ; $var_G = $V      ; $var_B = $var_3 ; }
        else if  ($var_i == 3) { $var_R = $var_1 ; $var_G = $var_2  ; $var_B = $V     ; }
        else if  ($var_i == 4) { $var_R = $var_3 ; $var_G = $var_1  ; $var_B = $V     ; }
        else                   { $var_R = $V     ; $var_G = $var_1  ; $var_B = $var_2 ; }

        $R = $var_R * 255;
        $G = $var_G * 255;
        $B = $var_B * 255;
    }

    $RGB['R'] = $R;
    $RGB['G'] = $G;
    $RGB['B'] = $B;

    return $RGB;
}

?>
