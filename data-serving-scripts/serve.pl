#!/usr/bin/perl
use strict;
use CGI;
use Apache::Connection ();

my $r = shift;
my $c = $r->connection;
my $q = new CGI;

# Initialize
my $inDataset = "";
my $inLevel = "";
my $inX = "";
my $inY = "";
my $outInvalid = 0;
my $outDataType = 0;
my $outPathname = "";
my $outMimeType = "";
my $outExtension = "";
my $buff = "";
my $bytesRead = 0;
my $haveFile = 0;

# Get posted variables
if (length($q->param('T'))) {     # dataset
  $inDataset = $q->param('T');
  $inDataset = int($inDataset);
} else {
  $outInvalid = 1;
}

if (length($q->param('L'))) {    # level
  $inLevel = $q->param('L');
  $inLevel = int($inLevel);
} else {
  $outInvalid = 1;
}

if (length($q->param('X'))) {    # x
  $inX = $q->param('X');
  $inX = int($inX);
} else {
  $outInvalid = 1;
}

if (length($q->param('Y'))) {    # y
  $inY = $q->param('Y');
  $inY = int($inY);
} else {
  $outInvalid = 1;
}

# Verify input, create pathname
$outPathname = "/wwdata/";
$outMimeType = "";

if ($inDataset == 100) {
    $outPathname .= "100/";
    $outMimeType = "application/x-7z-compressed";
    $outExtension = ".7z";
    $outDataType = 1;
} elsif ($inDataset == 106) {
    $outPathname .= "106/";
    $outMimeType = "application/x-7z-compressed";
    $outExtension = ".7z";
    $outDataType = 1;
} elsif ($inDataset == 105) {
    $outPathname .= "105/";
    $outMimeType = "image/jpeg";
    $outExtension = ".jpg";
    $outDataType = 2;
} else {
    $outInvalid = 1;
}

if (($inLevel >= 0) && ($inLevel <= 9)) {
  $outPathname .= "${inLevel}/";
} else {
  $outInvalid = 1;
}

#0005_0006.7z
#
#pad with 3 zeroes, if less than 1000
if ($inY < 1000) {
    $inY = sprintf("%04u", $inY);
}
$outPathname .= "${inY}/${inY}_";

#pad with 3 zeroes, if less than 1000
if ($inX < 1000) {
    $inX = sprintf("%04u", $inX);
}
$outPathname .= "${inX}";
$outPathname .= "${outExtension}";


# Display (for debugging purposes)
#
#if (!$outInvalid) {
#  $r->content_type('text/plain');
#
#  print "$inDataset $inLevel $inX $inY\n";
#  print "$outPathname\n\n";
#}

if ($outInvalid) {
  $r->status(400);     # BAD_REQUEST
  exit 0;
}


# Do we have the file?
if (-e $outPathname && -r $outPathname) {
  $haveFile = 1;
} else {
  # handle error


#  if ($outDataType == 2) {
#     # Can't find the Landsat7 tile, push /wwdata/105/blank.jpg
#     $outPathname = "/wwdata/105/blank.jpg";
#     if (!(-e $outPathname) || !(-r $outPathname)) {
#       print "Can't find blank Landsat7 tile";
#       exit 0;
#     }
#  } else {
#     $r->content_type('text/plain');
#
#     print "Can't find tile\n";
#     print "$inDataset $inLevel $inX $inY $outMimeType $outExtension";


     $r->status(404);      # NOT_FOUND
     exit 0;
#  }
}
 
# Send file

# Set up headers
$r->content_type("$outMimeType");
$r->set_content_length((-s($outPathname)));

# Open and display file
open my $fh, "$outPathname";
binmode $fh;
binmode STDOUT;
  
$bytesRead = read ($fh, $buff, 8192);
while ($bytesRead) {
  print $buff;
  $r->rflush;
  last if $c->aborted;
  $bytesRead = read ($fh, $buff, 8192);
}
close $fh;
exit 0;

