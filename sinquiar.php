#!/usr/local/bin/php -q
<?php

/*######################################################################
# sinquiar.php
#
# A poor man's file replication service
# Depends on cron, rsync, php4-cli, and a good flock() implementation
# Be aware that flock() does not work on NFS filesystems
#
# 2004 JPV
######################################################################*/

// Configuration values

$lockfile="/tmp/sinquiar.lock"; // lockfile location

$rspath="/usr/local/bin/rsync"; // rsync binary

$rspassfile="/root/rsyncd.secrets/"; // path to rsync password files
                                     // LEAVE trailing slash

$rsdelete="--delete"; // rsync to delete old files
                      // (--delete to activate, leave blank to
                      //    deactivate). USE WITH CAUTION.

$rsexclude="/usr/local/etc/rsync.exclude"; // exclude patterns file

$inipath="/home/www/home/adm.int.datafull.com/"; // also used for repl.php
						 // communication (path
						 // for $statusdir)
						 // LEAVE TRAILING SLASH	


$statusdir="rsyncstatus/"; // used to communicate with repl.php status
			   // utility
	 		   // LEAVE TRAILING SLASH				

$inifile="sinquiar.ini"; // sites init file

$sites=parse_ini_file($inipath.$inifile, TRUE); // get sites settings to an array
// now we have a $sites array, with n elements
// n is the number of sections of our ini file, and the number of sites
// every element of this asociative array is another array, holding the 
// site configuration


// Program

$fp = fopen($lockfile, "w+");

if (flock($fp, LOCK_EX | LOCK_NB)) // acquire an exclusive, non-blocking lock
   { // Everything that goes from here is locked, until lock release

    foreach($sites as $section => $site) 
    	// wohoo, we have a $site array with individual site settings
	// on each pass of the loop (with n passes as n elements of the 
	// $sites array)
        
        {
          // this is all about, we form the system() line over here
          $options = "--exclude-from=".$rsexclude;
          $options.= " ".$rsdelete." --recursive --owner --group --times";
          $options.= " --password-file ".$rspassfile.$site[passfile];
          $options.= " --max-delete=20 --compress";
          $from = "rsync://".$site[username]."@".$site[rsserver].$site[remotefolder];
          $to = $site[localfolder];

          $line=$rspath." ".$options." ".$from." ".$to;

	  touch($inipath.$statusdir.$section."started");	
          system($line);
	  touch($inipath.$statusdir.$section."ended");	          
        }


   flock($fp, LOCK_UN); // release the lock, we are done!
   }
else
   {
    // Error: The lock could not be acquired
    // Nothing goes to stout here, crontab execution needs to be quiet.
    // The non-blocking lock makes the program to exit right away
    // avoiding to pile up rsync executions.
   }

fclose($fp);

?>

