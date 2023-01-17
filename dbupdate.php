#!/usr/local/bin/php -q
<?
if (isset($_SERVER['HTTP_ACCEPT'])) {
  echo "<br><b>This script is meant to be run from the command line!";
  exit;
};
  /* phone call analysis, sivann 2009 
     dbupdate.php: reads text logfile and fills-in database*/
  /* 
     version 0.2 - 08/10/09
   */

  /* 01.10.09|14:35:30|1|833||00:00:35|6932190xxx|0,03|2|||0|1| */
  /*
    FIELDS (check siemens.txt):
    Date (at end of call), 8 characters
    Time (at end of call), 8 characters
    Number of seized trunk, 3 characters
    Internal station number (max. 6 characters)
    Duration of incoming call, 8 characters (ringing duration)
    Duration of call, 8 characters
    External phone number (max. 25 characters, if transmitted)
    Call charge pulse/amount, 11 characters
    Additional information (such as incoming call, outgoing call, transferred call, conference, DISA, call setup charges), 
               2 characters. 
	       1:incoming (voice)
	       2:outgoing (voice)
	       3:incoming (other service)
	       4:outgoing (other service)
	       5:incoming forwarded  (may also have other lines (type:9) if destination is external)
	       6:outgoing forwarded 
	       7:int/ext/ext conference with incoming connection/transit through external transfer
	       8:conference with outgoing connection through external transfer
	       9:outgoing via call forwarding (divert) to an external destination
	       0:call information (caller list)
	       +20: charges (connection charges with no duration)
	       +30: a) call>24h, b)call transfer
	       +40: data record with transit code (?).
	       +50: DISA calls
	       +70: combination of +30,+40
	       35:ext calls A(int) which diverts to B(int). B transfers ext to C. MSN displays B. Call seems to target C.
	       36:A(int) calls external, and transfers call to B(int). MSN field displays A, call seems to originate from B.
	       37:?
	       38:?
	       45:?
	       46:?
    Account code (ACCT), (entered by the user for this call, max. 11 characters)
    MSN used (max. 11 characters for point-to-multipoint connections). (May display forwarder, like saying 'via')
    LCR access code (trunk access code, 6 characters)
    LCR route used (dialing rules table, 2 characters)


    outgoing no answer (10 sec ringing):
    26.02.10|17:31:24|3|772|00:10|00:00:00|2106973903808||2|||0|1|
    18898|1267198284|3|772 |10   |0       |2106973903808||2|||0|1

    outgoing:
    26.02.10|17:31:51|3|772|     |00:00:06|6973903808|0,01|2|||0|1|
    18899|1267198311|3|772 |0    |6       |6973903808|0,01|2|||0|1


    incoming no answer (4 sec ringing):
    26.02.10|17:32:38|3|772|00:04|00:00:00|6973903808||1|||||
    18900|1267198358|3|772 |4    |0       |6973903808||1||||

    incoming (5 sec ringing)
    26.02.10|17:34:39|3|772|00:05|00:00:13|6973903808||1|||||
    18903|1267198479|3|772 |5    |13      |6973903808||1||||
  */

  $mydir=dirname($_SERVER['PHP_SELF']);
  $logfile="/var/log/call.log";
  $dbfile="$mydir/calls.db";



  $debug=0;
  error_reporting (E_ALL);

  try {
    //$dbh = new PDO("sqlite:$dbfile",'','', array(PDO::ATTR_PERSISTENT => true));
    $dbh = new PDO("sqlite:$dbfile");
    echo "DB:Connected to $dbfile\n";
  } catch (PDOException $e) {
    print "\nDatabase Error!: " . $e->getMessage() . "<br>\n";
    die();
  }

  $sth=db_execute($dbh,"SELECT * FROM calls ORDER BY datetime DESC LIMIT 1",1);
  $lastindb=$sth->fetch(PDO::FETCH_ASSOC);



  $fp = fopen($logfile, "r");
  $num_added=0;
  $num_found=0;

  while (!feof($fp)) {
    /* read line */
    $line=trim(fgets($fp, 1024)); 
    $line_arr=explode("|",$line);
    if (count($line_arr)<14) continue;
    $num_found++;

    if (!($num_found%200)) echo ".";

    /* parse line */
    list($d,$m,$y)=sscanf($line_arr[0], "%2d.%2d.%2d");
    $d=sprintf("%02d",$d); $m=sprintf("%02d",$m); $y=sprintf("%02d",$y);
    $datetime="20$y-$m-$d ".$line_arr[1];
    $datetime_ts=strtotime($datetime); //convert to timestamp

    $trunk=$line_arr[2];
    $stationno=$line_arr[3];
    $duration_incoming=$line_arr[4];
    $duration=$line_arr[5];
    $phone_number=$line_arr[6];
    $charge_pulse=$line_arr[7];
    $info=$line_arr[8];
    $account_code=$line_arr[9];
    $msn=$line_arr[10];
    $lcr_access_code=$line_arr[11];
    $lcr_route=$line_arr[12];

    if ($datetime_ts<$lastindb['datetime']) {  // entry older than those in db
      if ($debug) {
        echo "Skipping entry [$line], older than DB\n";
      }
      continue;
    }
    //call entry in same second as the latest in db. Must see if it is already in db.
    elseif ($datetime_ts==$lastindb['datetime']) { 
      $sth=db_execute($dbh,"SELECT * FROM calls where datetime=$datetime_ts and trunk=$trunk",1);
      $samecalls=$sth->fetchAll(PDO::FETCH_ASSOC);
      $isindb=count($samecalls);
      
      if ($isindb>0) {  //same second AND same call, don't re-insert it
	if ($debug) {
	  echo "Skipping entry [$line], already in DB\n";
	}
        continue;
      }
    }

    //convert durations in seconds
    $duration_incoming_sec=time2sec($duration_incoming);
    $duration_sec=time2sec($duration);

    if ($debug) echo "*Inserting new entry [$line], newer than DB\n";


    $sql="INSERT into calls (datetime , trunk,stationno,duration_incoming_sec , ".
         "duration_sec , phone_number, charge_pulse,info,account_code,msn, ".
         "lcr_access_code,lcr_route) VALUES ".
         " ($datetime_ts,$trunk,'$stationno','$duration_incoming_sec','$duration_sec','$phone_number', ".
	 " '$charge_pulse', '$info', '$account_code', '$msn', '$lcr_access_code', '$lcr_route');";

    //update history table
    $rh=$dbh->exec($sql);
    $error = $dbh->errorInfo();
    if($error[0] && isset($error[2])) {
      echo "DB Error: ($sql): ".$error[2]."\n";
      $backtrace = debug_backtrace();
      print_r($backtrace);
    }
    elseif ($debug) {
      echo "inserted $datetime_ts,$stationno into $dbfile\n";
    }
    $num_added++;
    if (!($num_added%20)) echo "+";
  }
  fclose($fp); 

  echo "\nFound $num_found entries in $logfile\n";
  echo "Added $num_added new entries in $dbfile\n";

function db_execute($dbh,$sql)
{
  $sth = $dbh->prepare($sql);
  $error = $dbh->errorInfo();
  if($error[0] && isset($error[2])) {
    echo "<br><b>DB Error: [$sql]: ".$error[2]."<br></b>";
    $backtrace = debug_backtrace();
    print_r($backtrace);
    $error="";
    return 0;
  }
  $sth->execute();
  return $sth;
}

function time2sec($t)
{
  $tx=explode(":",$t);
  $tx=array_reverse($tx);

  for ($sec=0,$i=0;$i<count($tx);$i++){
    $sec+=$tx[$i]*pow(60,$i);
  }
  return $sec;
}

?>

