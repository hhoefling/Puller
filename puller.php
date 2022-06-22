<?php


   
   
function logf($s)
{
 error_log("$s\n", 3, "/var/www/html/puller/puller.log");
} 
 
 
function getopenwbconfig($fn)
{
  global $debug;
  
  
// prepare key/value array
    $settingsArray = [];
    $settingsArray['socmodul']='';
    $settingsArray['evsecon']='';
    $settingsArray['ladeleistungmodul']='';
    $settingsArray['wattbezugmodul']='';
    $settingsArray['pvwattmodul']='';
    $settingsArray['pvwattmodul2']='';
    $settingsArray['speichermodul']='';
  
  
    try {
		  if ( !file_exists($fn) ) {
		  	throw new Exception('Konfigurationsdatei nicht gefunden.');
		  }
		 
		// first read config-lines in array
 		$settingsFile = file($fn);

	 	foreach($settingsFile as $line) 
 		{
			// split line at char '='
			$splitLine = explode('=', $line, 2);
			// trim parts
			$splitLine[0] = trim($splitLine[0]);
			$splitLine[1] = trim($splitLine[1]); // do not trim single quotes, we will need them later
			// push key/value pair to new array
			if(   preg_match("/mqtt/" , $splitLine[1]) 
               || $splitLine[0]=='debug'
               || $splitLine[0]=='lastmanagement'
               || $splitLine[0]=='ladeleistungmodul'
               )
				$settingsArray[$splitLine[0]] = $splitLine[1];
		}
        $debug=$settingsArray['debug'];

    } 
    catch ( Exception $e ) 
    {
		$msg = $e->getMessage();
		logf("$msg");
	}	
    if ($debug>2) 
       logf( print_r($settingsArray, true) );
    return $settingsArray;
}



 
 $pullfrom= ( isset($_GET['pullfrom']) ? escapeshellarg($_GET['pullfrom']) : ''); 
 $pull= ( isset($_GET['pull']) ? escapeshellcmd($_GET['pull']) : ''); 
 $myConfigFile = $_SERVER['DOCUMENT_ROOT'].'/openWB/openwb.conf';
 $settingsArray = getopenwbconfig($myConfigFile);
 $debug=$settingsArray['debug'];
 
 if($pullfrom>'')
 {
    header('Content-Type: text/plain');
 
    $pullx = explode(",", $pull);
    $mods=[];
    $mods['lp1']=0;
    $mods['lp2']=0;
    $mods['ll1']=0;
    $mods['ll2']=0;
    $mods['soc1']=0;
    $mods['soc2']=0;
    $mods['evu']=0;
    $mods['wr1']=0;
    $mods['wr2']=0;
    $mods['bat']=0;
    foreach( $pullx as $p) 
        $mods[$p]=1;
        
    if ($debug>2) 
        logf( print_r($mods,true) );

    logf("pullfrom:$pullfrom pull:$pull");
     if( $settingsArray['lastmanagement'] > 0 )
     {
      if( $mods['lp2']  &&   $settingsArray['evsecons1'] != 'mqttevse' ) $mods['lp2']=0;  
      if( $mods['ll2']  &&   $settingsArray['ladeleistungs1modul'] != 'mqttlllp2' ) $mods['ll2']=0;
      if( $mods['soc2'] &&   $settingsArray['socmodul1'] != 'soc_mqtt' ) $mods['soc2']=0;
     } else
     {
      $mods['lp2']=0;  
      $mods['ll2']=0;
      $mods['soc2']=0;
     }
     if( $mods['soc1'] &&   $settingsArray['socmodul'] != 'soc_mqtt' ) $mods['soc1']=0;
     if( $mods['lp1']  &&   $settingsArray['evsecon'] != 'mqttevse' ) $mods['lp1']=0;
     if( $mods['ll1']  &&   $settingsArray['ladeleistungmodul'] != 'mqttll' ) $mods['ll1']=0;
     if( $mods['evu']  &&   $settingsArray['wattbezugmodul'] != 'bezug_mqtt' ) $mods['evu']=0;
     if( $mods['wr1']  &&   $settingsArray['pvwattmodul'] != 'wr_mqtt' ) $mods['wr1']=0;
     if( $mods['wr2']  &&   $settingsArray['pvwattmodul2'] != 'wr_mqtt2' ) $mods['wr2']=0;
     if( $mods['bat']  &&   $settingsArray['speichermodul'] != 'speicher_mqtt' ) $mods['bat']=0;
  
    if ($debug>2) 
       logf( print_r($mods,true) );
    $sum=0;
    foreach($mods as $m)
        if($m==1) 
            $sum++;
    #$sum=0;
    if($sum>0)
    {
        logf(" $sum aktiver module, starte Pull Daemon");
        unset($output);
        exec("ps -aux | grep -v grep | grep [p]uller.py | awk '{print $2}' ", $output, $retval);
        if($debug>2) logf(print_r($output,true));
        if( count($output)==0)
        {
           logf(" no activ puller, start one");
            unset($output);
            exec("sudo -u pi python3 ./puller.py $pullfrom >/var/www/html/openWB/ramdisk/puller.log 2>&1 &", $output, $retval);
            if ($debug>2) 
                logf(print_r($output,true));

            unset($output);
            exec("sudo chmod 0777 /var/www/html/openWB/ramdisk/puller.log", $output, $retval);
            if ($debug>2) 
                logf(print_r($output,true));

        } else
        {
           logf("puller allready running.");
        }
    }
    else
    {
        logf(" keine aktiven module, Stoppe Pulll Daemon");
        unset($output);
        exec("ps -aux | grep -v grep | grep [p]uller.py | awk '{print $2}' ", $output, $retval);
        if($debug>2) logf(print_r($output,true));
        foreach($output as $pid)
        {
          $pid=intval($pid);
          if($pid>0 )
            {
               unset($output);
               exec("sudo kill $pid",$output, $retval);
               logf('kill '.print_r($output, true));
            }
        }
    }

    
    echo "0";   // nix
}


?>
