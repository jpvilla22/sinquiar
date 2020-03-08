<META HTTP-EQUIV="Refresh" CONTENT="5; url=http://adm.int.datafull.com/repl.php">

<?php

/*######################################################################
# repl.php
#
# A poor man's file replication service web reporting utility
# Depends on sinquiar.php
#
# 2004 JPV
######################################################################*/

$inipath="/home/adm.int.datafull.com/"; // also used for repl.php
                                                 // communication (path
                                                 // for $statusdir)
                                                 // LEAVE TRAILING SLASH


$statusdir="rsyncstatus/"; // used to communicate with repl.php status
                           // utility
                           // LEAVE TRAILING SLASH

$inifile="sinquiar.ini"; // sites init file

$sites=parse_ini_file($inipath.$inifile, TRUE); // get sites settings to an array
// look on sinquiar.php for more comments

$now=date("d/m/Y  H:i:s.");

echo("<h2>DATAFULL.COM Websites<br>Estado de la replicacion al ".$now."</h2><hr>");

foreach($sites as $section => $site)
// look on sinquiar.php for more comments
{
  $last_started=filemtime($inipath.$statusdir.$section."started");
  $last_ended=filemtime($inipath.$statusdir.$section."ended");

  $date_started=date("d/m/Y  H:i:s.", $last_started);
  $date_ended=date("d/m/Y  H:i:s.", $last_ended);

  if($last_started > $last_ended)
  {
    echo("<h3>".$section."</h3>Status: <b>EN PROCESO</b><br>");
    echo("Ultima replicacion terminada el: ".$date_ended."<br>");
    echo("Replicacion en proceso comenzada el: ".$date_started."<br>");
    $nowtime=time();
    $solong = $nowtime - $last_started;
    $diff = $nowtime - $last_ended;
    echo("Este sitio en particular lleva ".$solong." segundos en proceso de replicacion<br>");
    echo("La copia en produccion lleva alrededor de<b> ".$diff." segundos desactualizada.</b><br>");
    echo("<hr>");
  }
  else
  {
    echo("<h3>".$section."</h3>Status: <b>Esperando turno</b><br>");
    echo("Ultima replicacion comenzada el: ".$date_started."<br>");
    echo("Ultima replicacion terminada el: ".$date_ended."<br>");
    $nowtime=time();
    $lasted = $last_ended - $last_started;
    $diff = $nowtime - $last_ended;
    echo("Este sitio en particular tardo ".$lasted." segundos en replicarse la ultima vez<br>");
    echo("La copia en produccion lleva alrededor de<b> ".$diff." segundos desactualizada.</b><br>");
    echo("<hr>");
  }
	
}

echo("Si no hay ningun sitio marcado <b>EN PROCESO</b>, en un maximo de un minuto ");
echo("el proceso de copia deberia comenzar nuevamente");

?>
