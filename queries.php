<?
/*  define queries here, to be executed and displayed from showstats */

$qno=0;

/////////// GRAPHICAL QUERIES ///////////////
/*
$query[$qno]['sql']="SELECT stationno,strftime('%Y%m%d',datetime,'unixepoch') AS ymd, ".
                    " sum(duration_sec) AS total_dur_sec FROM calls WHERE 1==1 ".
                    " GROUP BY ymd,stationno ";
$query[$qno]['colnames']=array("Station Number","Day","Duration");
$query[$qno]['description']="Total duration of calls per day per station(IN+OUT)";
$query[$qno]['title']=$query[$qno]['description'];
$query[$qno]['labels']=0;
$query[$qno]['xaxis']=1;
$query[$qno]['yaxis']=2;
$query[$qno]['w']=1060;
$query[$qno]['h']=500;
$query[$qno]['deftype']="accbar";
$query[$qno]['format']="num,ymd,num";
$query[$qno]['xtitle']="Day";
$query[$qno]['ytitle']="Duration (sec)";
$qno++;

/* documentation under: Call Detail Recording Central (CDRC) 
outgoing no answer (10 sec ringing):
26.02.10|17:31:24|3|772|00:10|00:00:00|2106973903808||2|||0|1|

outgoing:
26.02.10|17:31:51|3|772|     |00:00:06|6973903808|0,01|2|||0|1|

incoming no answer (4 sec ringing):
26.02.10|17:32:38|3|772|00:04|00:00:00|6973903808||1|||||

incoming (5 sec ringing)
26.02.10|17:34:39|3|772|00:05|00:00:13|6973903808||1|||||
*/

/* predefined informational field sets for IN/OUT calls */
$whin=" (substr(info,-1,1) IN ('1','3','5','7')) ";
$whout=" (substr(info,-1,1) IN ('2','4','6','8','9')) ";

/********************************* QUERY START *******************************************************/

$query[$qno]['sql']=
                    "SELECT 'IN', strftime('%Y%m%d',datetime,'unixepoch')  AS ymd,".
                    "sum(duration_sec) AS total_dur_sec FROM calls WHERE $whin ".
                    "GROUP BY ymd ".
		    " UNION ".
                    "SELECT 'OUT', strftime('%Y%m%d',datetime,'unixepoch')  AS ymd,".
                    "sum(duration_sec) AS total_dur_sec FROM calls WHERE $whout ".
                    "GROUP BY ymd";
$query[$qno]['colnames']=array("Day","Duration");
$query[$qno]['description']="Total duration of calls per day (IN & OUT)";
$query[$qno]['title']=$query[$qno]['description'];
$query[$qno]['labels']=0;
$query[$qno]['xaxis']=1;
$query[$qno]['yaxis']=2;
$query[$qno]['w']=805;
$query[$qno]['h']=435;
$query[$qno]['deftype']="line";
$query[$qno]['format']="ymd,ts"; //X,Y , ymd=20090808,ts=timestamp (sec since 1970), other=no conversion
$query[$qno]['xtitle']="Day";
$query[$qno]['ytitle']="Duration (sec)";
$qno++;


$query[$qno]['sql']=" SELECT   sum(duration_sec) AS total_dur_sec, substr(phone_number,1,2) as numprefix FROM calls WHERE $whout GROUP BY numprefix order by total_dur_sec desc limit 5";
$query[$qno]['colnames']=array("Duration","Number of calls", "Prefix");
$query[$qno]['description']="Top 5 Call prefixes (OUT)";
$query[$qno]['title']=$query[$qno]['description'];
$query[$qno]['labels']=1;
$query[$qno]['xaxis']=0;
$query[$qno]['yaxis']='';
$query[$qno]['ytitle']='';
$query[$qno]['format']='';
$query[$qno]['w']=261;
$query[$qno]['h']=215;
$query[$qno]['deftype']="pie";
$query[$qno]['xtitle']="Number Prefix";
$qno++;



$query[$qno]['sql']=" SELECT   sum(duration_sec) AS total_dur_sec, substr(phone_number,1,4) as numprefix FROM calls WHERE phone_number like '00%' AND $whout GROUP BY numprefix order by total_dur_sec desc limit 8";
$query[$qno]['colnames']=array("Duration","Number of calls", "Prefix");
$query[$qno]['description']="Top 8 Countries (OUT)";
$query[$qno]['title']=$query[$qno]['description'];
$query[$qno]['labels']=1;
$query[$qno]['xaxis']=0;
$query[$qno]['yaxis']='';
$query[$qno]['ytitle']='';
$query[$qno]['format']='';
$query[$qno]['w']=261;
$query[$qno]['h']=215;
$query[$qno]['deftype']="pie";
$query[$qno]['xtitle']="Number Prefix";
$qno++;




$query[$qno]['sql']="SELECT stationno,strftime('%Y-%m-%d',datetime,'unixepoch')  AS ymd, ".
                    " sum(duration_sec) AS total_dur_sec FROM calls WHERE 1==1 ".
                    " GROUP BY ymd,stationno ";
$query[$qno]['colnames']=array("Station Number","Day","Duration");
$query[$qno]['description']="Total duration of calls per day per station(IN+OUT)";
$query[$qno]['title']=$query[$qno]['description'];
$qno++;



$query[$qno]['sql']="SELECT sum(duration_sec) AS total_dur_sec, count(id) AS totcalls  FROM calls WHERE ".
                    " $whin ORDER BY total_dur_sec desc";
$query[$qno]['colnames']=array("Duration","Number of calls");
$query[$qno]['description']="Total successful calls (IN)";
$qno++;




$query[$qno]['sql']="SELECT sum(duration_sec) AS total_dur_sec, count(id) AS totcalls FROM calls WHERE ".
                    " $whout ORDER BY total_dur_sec desc";
$query[$qno]['colnames']=array("Duration","Number of calls");
$query[$qno]['description']="Total successful calls (OUT)";
$qno++;




$query[$qno]['sql']="SELECT  sum(duration_sec) AS total_dur_sec, count(id) AS totcalls FROM calls ".
                    "WHERE phone_number like '21%' AND $whout ";
$query[$qno]['colnames']=array("Duration","Number of calls");
$query[$qno]['description']="Total local calls (OUT)";
$qno++;



$query[$qno]['sql']=
                    "SELECT 'IN/OUT', strftime('%Y%m%d',datetime,'unixepoch')  AS ymd,".
                    "max(trunk) AS maxtrunk FROM calls WHERE $whin  ".
                    "GROUP BY ymd ";
$query[$qno]['colnames']=array("Day","Max. Trunks Used");
$query[$qno]['description']="Maximum simultaneous calls per day";
$query[$qno]['title']=$query[$qno]['description'];
$query[$qno]['labels']=0;
$query[$qno]['xaxis']=1;
$query[$qno]['nostation']=1;
$query[$qno]['yaxis']=2;
$query[$qno]['w']=535;
$query[$qno]['h']=217;
$query[$qno]['deftype']="accline";
$query[$qno]['format']="ymd,other"; //X,Y , ymd=20090808,ts=timestamp (sec since 1970), other=no conversion
$query[$qno]['xtitle']="Day";
$query[$qno]['ytitle']="Max. Trunk #";
$qno++;





$query[$qno]['sql']="SELECT sum(duration_sec) AS total_dur_sec, count(id) as totcalls FROM calls WHERE ".
                    "phone_number like '00%' AND $whout ORDER BY total_dur_sec desc";
$query[$qno]['colnames']=array("Duration","Number of calls");
$query[$qno]['description']="Total international calls (OUT)";
$qno++;

$query[$qno]['sql']="SELECT sum(duration_sec) AS total_dur_sec,count(id) as totcalls FROM calls WHERE ".
                    " phone_number like '69%' AND $whout ORDER BY total_dur_sec desc";
$query[$qno]['colnames']=array("Duration","Number of calls");
$query[$qno]['description']="Total mobile calls (OUT)";
$qno++;

$query[$qno]['sql']="SELECT strftime('%H',datetime,'unixepoch')  AS H,sum(duration_sec) AS total_dur_sec ,".
                    " count(id) as totcalls FROM calls WHERE $whout GROUP BY H";
$query[$qno]['colnames']=array("Hour","Duration","Number of Calls");
$query[$qno]['description']="Total calls per hour (OUT)";
$qno++;

$query[$qno]['sql']="SELECT strftime('%H',datetime,'unixepoch')  AS H,sum(duration_sec) AS total_dur_sec,count(id) FROM calls ".
                    " WHERE $whin GROUP BY H";
$query[$qno]['colnames']=array("Hour","Duration","Number of Calls");
$query[$qno]['description']="Total calls per hour (IN)";
$qno++;

$query[$qno]['sql']="SELECT strftime('%Y-%m-%d',datetime,'unixepoch')  AS ymd,".
                    "sum(duration_sec) AS total_dur_sec ,count(id) as totcalls FROM calls WHERE 1==1 GROUP BY ymd";
$query[$qno]['colnames']=array("Day","Duration","Number of Calls");
$query[$qno]['description']="Total calls per day (IN+OUT)";
$qno++;

$query[$qno]['sql']="SELECT strftime('%Y-%m',datetime,'unixepoch') AS ym, ".
                    "sum(duration_sec) AS total_dur_sec,count(id) as totcalls ".
                    " FROM calls WHERE $whout GROUP BY ym";
$query[$qno]['colnames']=array("Month","Duration","Number of calls");
$query[$qno]['description']="Total calls per month (OUT)";
$qno++;

$query[$qno]['sql']="SELECT strftime('%Y-%m',datetime,'unixepoch')  AS ym,sum(duration_sec) AS total_dur_sec, count(id) as totcalls  FROM calls WHERE duration_incoming_sec>0 GROUP BY ym";
$query[$qno]['colnames']=array("Month","Duration","Number of calls");
$query[$qno]['description']="Total calls per month (IN)";
$qno++;

$query[$qno]['sql']="SELECT stationno,sum(duration_sec) AS total_dur_sec,count(id) AS totcalls FROM calls WHERE phone_number like '69%' AND $whin GROUP BY stationno ORDER BY total_dur_sec desc";
$query[$qno]['colnames']=array("Station Number","Duration","Number of calls");
$query[$qno]['description']="Mobile calls per station (IN)";
$qno++;

$query[$qno]['sql']="SELECT stationno,sum(duration_sec) AS total_dur_sec,count(id) AS totcalls FROM calls WHERE phone_number like '69%' AND $whout GROUP BY stationno ORDER BY total_dur_sec desc";
$query[$qno]['colnames']=array("Station Number","Duration","Number of calls");
$query[$qno]['description']="Mobile calls per station (OUT)";
$qno++;

$query[$qno]['sql']="SELECT stationno,sum(duration_sec) AS total_dur_sec, count(id) AS totcalls FROM  calls WHERE phone_number like '00%' AND $whin GROUP BY stationno ORDER BY total_dur_sec desc";
$query[$qno]['colnames']=array("Station Number","Duration","Number of calls");
$query[$qno]['description']="International calls per station (IN)";
$qno++;

$query[$qno]['sql']="SELECT stationno,sum(duration_sec) AS total_dur_sec,count(id) AS totcalls FROM calls WHERE phone_number like '00%' AND $whout GROUP BY stationno ORDER BY total_dur_sec desc";
$query[$qno]['colnames']=array("Station Number","Duration","Number of calls");
$query[$qno]['description']="International calls per station (OUT)";
$qno++;

$query[$qno]['sql']="SELECT stationno,sum(duration_sec) AS total_dur_sec,count(id) AS totcalls FROM calls WHERE phone_number like '21%' AND $whout GROUP BY stationno ORDER BY total_dur_sec desc";
$query[$qno]['colnames']=array("Station Number","Duration","Number of calls");
$query[$qno]['description']="Local calls per station (OUT)";
$qno++;

$query[$qno]['sql']="SELECT stationno,sum(duration_sec) AS total_dur_sec,count(id) as totcalls FROM calls WHERE 1==1 GROUP BY stationno ORDER BY total_dur_sec desc";
$query[$qno]['colnames']=array("Station Number","Duration","Number of Calls");
$query[$qno]['description']="Total calls per station (IN+OUT)";
$qno++;

$query[$qno]['sql']="SELECT stationno,sum(duration_sec) AS total_dur_sec,count(id) AS totcalls FROM calls WHERE $whin GROUP BY stationno ORDER BY totcalls DESC ";
$query[$qno]['colnames']=array("Station Number","Duration","Number of calls");
$query[$qno]['description']="Total Calls per station (IN)";
$qno++;

$query[$qno]['sql']="SELECT stationno,sum(duration_sec) AS total_dur_sec, count(id) AS totcalls,  round(sum(replace(charge_pulse,',','.')),2) as chargepulses FROM calls WHERE $whout GROUP BY stationno ORDER BY totcalls DESC ";
$query[$qno]['colnames']=array("Station Number","Duration","Number of calls","Charge pulses");
$query[$qno]['description']="Total Calls per station (OUT)";
$qno++;


$query[$qno]['sql']="SELECT phone_number,sum(duration_sec)AS total_dur_sec, stationno FROM calls WHERE $whout GROUP BY phone_number ORDER BY total_dur_sec DESC LIMIT 20;";
$query[$qno]['colnames']=array("Phone Number","Duration","An SRC Station");
$query[$qno]['description']="20 Most active destination phone numbers";
$qno++;

$query[$qno]['sql']="SELECT phone_number,sum(duration_sec) AS total_dur_sec,stationno FROM calls WHERE $whin GROUP BY phone_number ORDER BY total_dur_sec DESC LIMIT 20;";
$query[$qno]['colnames']=array("Phone Number","Duration","A DST Station");
$query[$qno]['description']="The 20 most active phone numbers calling us";
$qno++;

$query[$qno]['sql']="SELECT stationno,count(id) AS totcalls FROM calls WHERE $whin AND duration_sec==0  GROUP BY stationno ORDER BY totcalls DESC";
$query[$qno]['colnames']=array("Station Number","Calls #");
$query[$qno]['description']="Missed calls per station";
$qno++;

$query[$qno]['sql']="SELECT stationno,sum(duration_incoming_sec) AS total_dur_sec FROM calls WHERE $whin GROUP BY stationno ORDER BY total_dur_sec DESC ";
$query[$qno]['colnames']=array("Station Number","Ring Duration");
$query[$qno]['description']="Most ringing internal stations";
$qno++;

 $query[$qno]['sql']="SELECT stationno, count(id) AS totcalls,sum(duration_incoming_sec) AS totsecs,  round(sum(duration_incoming_sec+0.0)/count(id),1) as norm_durin_sec FROM calls WHERE  (substr(info,-1,1) IN ('1','3','5','7')) AND duration_incoming_sec>=0  AND duration_sec>0 GROUP BY stationno ORDER BY norm_durin_sec  DESC";
$query[$qno]['colnames']=array("Station Number","Number of Calls", "Total Ringing Duration", "Seconds to pickup");
$query[$qno]['description']="Average waiting time to pick-up (on successful connections)";
$qno++;


?>
