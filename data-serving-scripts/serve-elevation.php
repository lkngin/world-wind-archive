<?
// This scipt is for serving out your own WW Cached tiles to World Wind, useful for own imagery and for working "off line"
// This script can be named what ever you want, it is controlled via a World Wind XML
//
// thanks to MaurizoZA and Nowak for the script

$X = $_GET['X'];
$Y = $_GET['Y'];
$L = $_GET['L'];
$T = $_GET['T'];

function addzeros($string){
       //echo $string;
       if(strlen($string) >= 4){
               return $string;
       }

       $string = "0" . "$string";

       if(strlen($string) < 4){
               $string = addzeros($string);
       }
       return $string;
}


$ext = ".bil.zip";

// Change the following to the location of your local root cache folder
$url = 'D:/cache/';
$doneurl = $url . $T . "/" . $L . "/" . addzeros($Y) . "/" . addzeros($Y) . "_" . addzeros($X) . $ext;

// Debug tools
//header("Location: $doneurl");
//exit;
// print ($doneurl);
//exit;

     $tileData = file_exists($doneurl) ? file_get_contents($doneurl) : false;
     if ($tileData === false) die();

     @header('Content-type: application/zip');
     print($tileData);

?>

