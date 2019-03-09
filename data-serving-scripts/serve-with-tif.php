<?
//PHP script for dishing out BILs behind a web server or a geo-trawler.
//It will take a source TIF, use GDAL to cut up the source elevation data into WW tiles
//(and corresponding directory structure), and then GZip the files for delivery.

$szMapCacheDir="G:/CFSImagery/wwcache/DEM";
/* create the main cache directory if necessary */
if (!@is_dir($szMapCacheDir))
    makeDirs($szMapCacheDir);

/* get the various request parameters 
 * also need to make sure inputs are clean, especially those used to
 * build paths and filenames
 */
$X = isset( $_REQUEST['X'] ) ? intval($_REQUEST['X']) : 0;
$Y = isset( $_REQUEST['Y'] ) ? intval($_REQUEST['Y']) : 0;
$L = isset( $_REQUEST['L'] ) ? intval($_REQUEST['L']) : 0;
$T = isset( $_REQUEST['T'] ) ? intval($_REQUEST['T']) : 103;
$szExt = ".7z";

$szLevelcache = $szMapCacheDir."/$L";
$szYcache = sprintf($szLevelcache."/%04d",$Y);
if (!@is_dir($szYcache))
    makeDirs($szYcache);
$szCacheFile = sprintf($szYcache."/%04d_%04d".$szExt,$Y,$X);
$szIntFile = sprintf($szYcache."/%04d_%04d.bil",$Y,$X);;
/* Hit Test the cache tile */
if (!file_exists($szCacheFile) || $bForce){
 $lzts = 1.0;
 //Our Layer Zero Tile Size
 
 $lat1 = ($Y*$lzts*pow(0.5,$L))-90;
 $lon1 = ($X*$lzts*pow(0.5,$L))-180;
 //Lat2 and Lon2 as figured from tile size and level
 $lat2 = $lat1 + $lzts*pow(0.5,$L);
 $lon2 = $lon1 + $lzts*pow(0.5,$L);
 if($T==103){
 if(($lat1>-33)||($lon1<138)||($lat2<-36)||($lon2>140)){
  header("HTTP/1.0 404 Not Found");
  exit();
 }
 else{
  $gdalwarp = "gdalwarp.exe -te $lon1 $lat1 $lon2 $lat2 -ot Int16 -ts 150 150 -of ENVI ".
  "G:/CFSImagery/latlong/dem/hillsdem.tif ".$szIntFile;
  exec($gdalwarp);
  $za7 = "7za.exe a ".$szCacheFile." ".$szIntFile;
  exec($za7);
 }
 }
 if($T==104){
 if(($lat1>28)||($lon1<9)||($lat2<27)||($lon2>11)){
  header("HTTP/1.0 404 Not Found");
  exit();
 }
 else{
  $gdalwarp = "gdalwarp.exe -te $lon1 $lat1 $lon2 $lat2 -ot Int16 -ts 250 250 -of ENVI ".
  "G:/CFSImagery/latlong/dem/LibyaDuneslatlonint ".$szIntFile;
  exec($gdalwarp);
  $za7 = "7za.exe a ".$szCacheFile." ".$szIntFile;
  exec($za7);
 }
 }
}
$h = fopen($szCacheFile, "r");
header("Content-Type: "."application/x-7z-compressed");
header("Content-Length: " . filesize($szCacheFile));
header("Expires: " . date( "D, d M Y H:i:s GMT", time() + 31536000 ));
header("Cache-Control: max-age=31536000, must-revalidate" );
fpassthru($h);
fclose($h);
?>

