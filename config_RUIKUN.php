<?php

// Database vars
$config_dbHost = "localhost"; 
$config_dbUser = "root";
$config_dbPass = "aval0n";
$config_dbName = "eraeder";
$config_dbPort = "3306";

$config_kseadbs = array(
    "isd"       => "kseahci",
    "ir"        => "kseair",
    "sqlprog"   => "kseadb",
    "javaprog"  => "kseajava",
    "cprog"     => "ksea",
    "tdo"       => "tdo"
);

$global_error_db = "";

//$config_readerURL = "http://columbus.exp.sis.pitt.edu/ebooks/reader.php";
$config_serverRootURL = "http://columbus.exp.sis.pitt.edu/development/readingcircle-project.live";
$config_readerURL = $config_serverRootURL."/reader.php";
$config_corpusURL = $config_serverRootURL."/books";
$config_reportURL = $config_serverRootURL."/reportAction.php";
$config_progressURL = $config_serverRootURL."/getUserProgress.php";
$config_groupMoldelsURL = $config_serverRootURL."/getGroupList.php";
$config_selfMoldelsURL = $config_serverRootURL."/getPastModels.php";
$config_annotatedURL = "http://columbus.exp.sis.pitt.edu/jspeRaeder/annotated2/im/";


$config_page_corr_offset = array(
	"shnm" => -18, 
	"dix" => -27, //"/Human_Computer Inter/PNG", 
	"preece" => -24, //"/Interaction_Design/PNG",
	"lamming" => 0,
	"tdo"=> 0);
	
$config_colors = array("#E5E5E5","#F7FBFF","#DEEBF7","#C6DBEF","#9ECAE1","#6BAED6",
                       "#4292C6","#2171B5","#08519C","#08306B");
?>
