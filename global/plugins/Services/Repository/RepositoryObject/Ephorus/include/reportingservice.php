<?php

/**
 * reportingservice.php - Functions for receiving reports from Ephorus
 *
 * @package    Ephoruscomms
 * @subpackage Reporting Service
 * @author     Guido Bonnet
 * @copyright  2012 onwards Ephorus  {@link http://ephorus.com}
 */

error_reporting(-1);
ini_set("error_reporting", E_ALL);
ini_set("display_errors", 1);
include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'class.EphorusApi.php');

if(isset($_GET['ephorus_version'])) {
	echo '--------------- Ephorus Version ---------------'."\n";
	$status = new EphorusStatus();
	$status->versions();
	echo '-----------------------------------------------';
	exit;
}

if(isset($_GET['wsdl'])) {
	header ("Content-Type:text/xml");
	echo file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'ReportingService.wsdl');
	exit;
}

$reporting_service = new EphorusService();

function report($report) {
	global $reporting_service;

	$reporting_service->reportingService($report);
}
$server = $reporting_service->initReportingService();
$server->addFunction('report');
$server->handle();

exit;