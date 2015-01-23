#!/usr/local/bin/php -q
<?
$filename="R65.raw";

$fp=fopen($filename,"r");

while ( $r=fgetcsv($fp,256) ) {
	if ( '#' == substr($r[0],0,1) )
		continue;

//	print_r($r);
//	return;

/* date, pulseTime, pulseMinTime, pulseCount, batteryPercentCharged, wind direction sector */

	if ( $r[1]>0 && $r[1]<65535 ) 
		$ws=7650.0/$r[1] + 0.35;
	else
		$ws=0.0;

	if ( $r[2]>0 && $r[2]<65535 ) 
		$wg=7650.0/$r[2] + 0.35;
	else
		$wg=0.0;

/* 
+----------------------+------------+------+-----+---------------------+-------+
| Field                | Type       | Null | Key | Default             | Extra |
+----------------------+------------+------+-----+---------------------+-------+
| packet_date          | datetime   |      | PRI | 0000-00-00 00:00:00 |       |
| windSpeed            | float      |      |     | 0                   |       |
| windGust             | float      |      |     | 0                   |       |
| windCount            | int(11)    |      |     | 0                   |       |
| windDirectionSector  | tinyint(4) |      |     | 0                   |       |
| batteryStateOfCharge | tinyint(4) |      |     | 0                   |       |
| pulseTime            | int(11)    |      |     | 0                   |       |
| pulseMinTime         | int(11)    |      |     | 0                   |       |
+----------------------+------------+------+-----+---------------------+-------+
8 rows in set (0.00 sec)

data from .RAW file 
[0] => 2009-07-17 13:47
[1] => 1529
[2] => 1260
[3] => 382
[4] => 100
[5] => 3

*/



//	printf("%s,%0.1f,%0.1f,0\n",$r[0],$ws,$wg);
	printf("INSERT INTO rdLoggerCell_R65 (packet_date,windSpeed,windGust,windCount,windDirectionSector,batteryStateOfCharge,pulseTime,pulseMinTime) VALUES('%s',%0.1f,%0.1f,%d,%d,%d,%d,%d);\n",$r[0],$ws,$wg,$r[3],$r[5],$r[4],$r[1],$r[2]);

/*
if ( current.pulse_period[0]>0 && current.pulse_period[0]<65535 ) {
ws = 7650.0 / current.pulse_period[0] + 0.35;

*/

}

fclose($fp);

?>
