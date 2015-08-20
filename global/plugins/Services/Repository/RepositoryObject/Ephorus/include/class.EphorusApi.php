<?php
/**
 * class.EphorusApi.php - The file to receive data from Ephorus
 *
 * @package    ephorus plagiarism plugin
 * @subpackage ephoruscomms
 * @author     Guido Bonnet
 * @copyright  2012 Guido Bonnet http://ephorus.com
 */

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'class.DLEApi.php');
/**
 * EphorusService Class
 *
 * The class to get and set data for the different Ephorus services.
 *
 */
class EphorusService {

	const VISIBLE   = 1;
	const INVISIBLE = 2;

	/**
	 * Constructor
	 *
	 * @param bool $logging - overrule function for logging
	 */
	function EphorusService($logging = true) {
		DLEApi::initialize();

		if($logging) {
			$this->initLogging();
		}
	}

	function initSoapClient($address, $service = 'visibility') {
		$proxy_host     = DLEApi::getProxySetting('host');
		$proxy_port     = DLEApi::getProxySetting('port');
		$proxy_username = DLEApi::getProxySetting('username');
		$proxy_password = DLEApi::getProxySetting('password');

		// Create the client instance.
		try {
			$soap_client = @new SoapClient($address, array(
				'proxy_host'     => ($proxy_host)     ? $proxy_host     : null,
				'proxy_port'     => ($proxy_port)     ? $proxy_port     : null,
				'proxy_login'    => ($proxy_username) ? $proxy_username : null,
				'proxy_password' => ($proxy_password) ? $proxy_password : null,
			));
			if($service != 'visibility') {
				$this->trace('... Created soap client');
			}
			return $soap_client;
		} catch (Exception $e) {
			$this->log($e->getMessage());
			if($service != 'visibility') {
				$this->trace($e->getMessage());
			}
			return false;
		}
	}

	/**
	 * Function to send documents to Ephorus
	 */
	function handInService() {
		$handin_code = DLEApi::getSetting('handin_code');
		if($handin_code == '') {
			$this->log('Handin code not found');
			$this->trace('... No hand-in code found. Sending of documents terminated.');
			return;
		}
		$this->trace('... Hand-in code: '.$handin_code);

		$handin_address = DLEApi::getSetting('handin_address');
		if($handin_address == '') {
			$this->log('Handin address not found');
			$this->trace('... No hand-in address found. Sending of documents terminated.');
			return;
		}
		$this->trace('... Hand-in address: '.$handin_address);

		$unsent_documents = DLEApi::getUnsentDocuments();
		if(count($unsent_documents) == 0) {
			$this->trace('... No documents to be send.');
			return;
		}
		$this->trace('... '.count($unsent_documents).' Documents found. Start sending documents');

		$soap_client = $this->initSoapClient($handin_address, 'handin');
		if($soap_client == false) {
			$this->trace('... Connection problem.');
			return;
		}

		foreach($unsent_documents as $document) {
			// Make sure the document has enough time to be sent to Ephorus.
			set_time_limit(600);
			$this->log('Started hand-in document - '.$document->filename);
			if(!$this->isSupportedFiletype($document->filename)) {
				$this->log('Cancelled hand-in document - Wrong filetype.');
				$this->trace('... Error with document: '.$document->filename.' ('.$document->id.')');
				DLEApi::setHandinErrorToDocument($document->id, 'unsupported_file_type');
				continue;
			}

			$handin_parameters = DLEApi::getHandinParameters($document);
			if(!$handin_parameters) {
				$this->log('Cancelled hand-in document - Wrong parameters.');
				continue;
			}

			$this->trace('... POST DOCUMENT ('.$document->id.'): '.$document->filename.
				' with processtype '.$document->processtype);

			try {
				$result = $soap_client->UploadDocument($handin_parameters);
				if($result == false) {
					$this->log('Something went wrong while sending document');
					$this->trace('... Something went wrong while sending');
				} else {
					$guid = $result->UploadDocumentResult;
					$this->log('Document ('.$document->id.') sent successfully - Guid: '.$guid);
					$this->trace('- Succes! GUID: '.$guid);
					// Update the documents table with the GUID.
					DLEApi::setGUIDtoDocument($document->id, $guid);
				}
			} catch (SoapFault $result) {
				$this->log('Document ('.$document->id.') sent. error received - Error: '.$result->getMessage());
				$this->trace('- Error. '.$result->getMessage());
				if (strpos($result->getMessage(), 'Wrong file format.') !== false) {
					DLEApi::setHandinErrorToDocument($document->id, 'unsupported_file_type');
				} else if (strpos($result->getMessage(), 'Hand In code not found') === false) {
					DLEApi::setHandinErrorToDocument($document->id, 'unknown_file_error');
				}
			}
			$this->log('Finished hand-in document ('.$document->id.') - '.$document->filename);
			unset($document);
			unset($result);
		}
		$this->trace('... All documents have been sent.');
	}

	/**
	 * Function to change the visibility of a document
	 *
	 * @param string $document_guid - the guid of the document to change visibility
	 * @param int    $visibility    - the visibility that the document has to become
	 * @return bool - $the result of the change
	 */
	function visibilityService($document_guid, $visibility) {
		$index_address = DLEApi::getSetting('index_address');
		if($index_address == '') {
			$this->log('Index address not found');
			return false;
		}

		$soap_client = $this->initSoapClient($index_address);
		if($soap_client == false) {
			$this->log('... Connection problem.');
			return false;
		}

		// Call the SOAP method...
		try {
			$soap_client->IndexDocument(array('documentGuid' => $document_guid, 'indexType' => $visibility));
			DLEApi::changeVisibility($document_guid, $visibility);
			return true;
		} catch (SoapFault $e) {
			$this->log('Document ('.$document_guid.') index change. error received - Error: '.$e->getMessage());
			return false;
		}
	}

	/**
	 * Function for initiating the soap server
	 *
	 * @return void
	 */
	function initReportingService() {
		/* Instantiate soapserver */
		try {
			$server = @new SoapServer(dirname(__FILE__).DIRECTORY_SEPARATOR.'ReportingService.wsdl', array('encoding' => 'UTF-8', 'features' => SOAP_SINGLE_ELEMENT_ARRAYS));
			return $server;
		} catch (SoapFault $e) {
			$this->log('Error creating reporting soap server. Error: '.$e->getMessage());
			header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
			exit;
		}
	}

	/**
	 * Function for receiving Ephorus reports
	 *
	 * @param object $report - the object containing the report
	 * @return void
	 */
	function reportingService($report) {
		$this->log('Received report for document ('.$report->document_guid.')');

		if(DLEApi::checkDocumentExists($report->document_guid) == false) {
			$this->log('Report has unknown guid ('.$report->document_guid.')');
			$this->log('Report finished processing');
			return new SoapFault('SOAP-ENV:Client', 'Unknown guid received');
		}

		// Create Eph_document representation.
		$document = new stdClass();
		$document->guid           = $report->document_guid;
		$document->status         = $report->status;
		$document->student_name   = $report->student_name;
		$document->student_number = substr($report->student_number, 0, 25);
		$document->date_created   = $report->document_date;

		$document->percentage = 0;
		$document->summary    = NULL;

		$document->duplicate_guid           = NULL;
		$document->duplicate_student_name   = NULL;
		$document->duplicate_student_number = NULL;

		switch ($report->status) {
			case 1:
				$document->percentage = $report->document_percentage;
				// Get summary...
				$document->summary    = $report->summary->any;

				// When only one result, $report->results->result is not an array (as expected) but an object
				$result_list = (is_array($report->results->result)) ? $report->results->result : $report->results;

				$results = array();
				foreach($result_list as $result_item) {
					$result = new stdClass();
					$result->guid               = $result_item->result_guid;
					$result->document_guid      = $report->document_guid;
					$result->percentage         = $result_item->percent;
					$result->type               = $result_item->type;
					$result->url                = $result_item->url;
					$result->original_guid      = ($result_item->type == 'local') ? $result_item->original_guid : NULL;
					$result->student_name       = ($result_item->type == 'local') ? $result_item->student_name : NULL;
					$result->student_number     = ($result_item->type == 'local') ? substr($result_item->student_number, 0, 25) : NULL;
					$result->comparison         = $result_item->diff->any;
					$results[] = $result;
				}
				unset($result);
				break;
			case 2: // Duplicate document
				$this->log('Report returned a duplicate');
				$document->duplicate_guid          = $report->duplicate_original_guid;
				$document->duplicate_student_name   = $report->duplicate_student_name;
				$document->duplicate_student_number = substr($report->duplicate_student_number, 0, 25);
				break;
			default: // Nothing extra needs to happen if the status is 3 - 6.
				break;
		}
		DLEApi::saveReport($document, isset($results) ? $results : array());
		$this->log('Report finished processing');
	}

	/**
	 * Initializes logging
	 * Checks if the setting is set. If so, enable all logging
	 *
	 * @return void
	 */
	function initLogging() {
		if(DLEApi::getSetting('ephorus_logging') == 1) {
			error_reporting(-1);
			ini_set("error_reporting", E_ALL);
			ini_set("log_errors", 1);
		}
	}

	/**
	 * Creates a line of logging
	 * Checks if the setting is set. If so, write the rule
	 *
	 * @param string $message - the message to be written
	 * @return void
	 */
	function log($message) {
		if(DLEApi::getSetting('ephorus_logging') == 1) {
			error_log('['.date('r').'] Ephorus logging: '.$message, 0);
		}
	}

	/**
	 * Creates a line of logging
	 * Checks if the setting is set. If so, write the rule
	 *
	 * @param string $message - the message to be written
	 * @param string $eol     - the character at the end of the line
	 */
	static function trace($message, $eol="\n") {
		if (defined('STDOUT')) {
			fwrite(STDOUT, $message.$eol);
		} else {
			echo $message.$eol;
		}
		flush();
	}

	/**
	 * Method to check if Ephorus supports a file type
	 *
	 * @param string $filename - the name of the file that has to be checked
	 * @return bool
	 */
	function isSupportedFiletype($filename) {
		$supported_types = array('doc', 'docx', 'wpd', 'pdf', 'txt', 'rtf', 'odt', 'sxw', 'html', 'htm');
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		return in_array(strtolower($ext), $supported_types);
	}
}

/**
 * EphorusReport Class
 *
 * The class to get and set data for the Ephorus plagiarism report.
 *
 */
class EphorusReport {

	var $document_guid;
	var $mode;

	/**
	 * Constructor
	 */
	public function EphorusReport($document_guid, $mode = 'summary') {
		$this->document_guid = $document_guid;
		$this->mode = $mode;
	}

	/**
	 * Gets the header of the report
	 *
	 * @param array|string $selected_sources
	 * @return array
	 */
	public function getHeader($selected_sources) {
		$result_list = array();
		$results = DLEApi::getResults($this->document_guid);

		foreach($results as $result) {
			$result_list[$result->guid] = array(
				'percentage' => $result->percentage,
			);
			if($this->mode == 'detailed') {
				$result_list[$result->guid]['input'] = array(
					'type'    => 'radio',
					'checked' => ($result->guid == $selected_sources),
					'value'   => $result->guid,
					'name'    => 'diff'
				);
			} else {
				$result_list[$result->guid]['input'] = array(
					'type'    => 'checkbox',
					'checked' => in_array($result->guid, $selected_sources),
					'value'   => $result->guid,
					'name'    => 'guids_use[]'
				);
			}
			$result_list[$result->guid]['source'] = array(
				'title'   => ($result->type == 'local') ?
					sprintf(DLEApi::getText('document_written_by'), $result->student_name, $result->student_number) :
					$result->url,
				'link'    => ($result->type == 'local') ?
					($document_id = DLEApi::checkDocumentExists($result->original_guid)) ?
						DLEApi::getURL($document_id) :
						false :
					$result->url,
			);
		}
		return $result_list;
	}

	/**
	 * Gets the report content
	 *
	 * @param array $selected_sources
	 * @param string $comparison
	 * @return string
	 */
	public function getReport($selected_sources = array(), $comparison = '') {
		$document = DLEApi::getDocument($this->document_guid);
		switch($document->status) {
			case 0:
				$report = DLEApi::getText('wait_for_sending_msg');
				break;
			case 1:
				if($document->summary == NULL) {
					$report = DLEApi::getText('no_results_found');
				} else if($this->mode == 'detailed') {
					$xml = new DOMDocument();
					$xml->loadXML($comparison);
					$xml->saveXML();

					$xsl = new DOMDocument();
					$xsl->load(dirname(__FILE__).DIRECTORY_SEPARATOR.'detailed.xslt');

					$xslt = new XSLTProcessor();
					$xslt->setParameter('', 'original', DLEApi::getText('original_text'));
					$xslt->setParameter('', 'found', DLEApi::getText('found_by_ephorus'));

					$xslt->importStyleSheet($xsl);

					$report = $xslt->transformToXML($xml);
				} else {
					$xml = new DOMDocument();
					$xml->loadXML($document->summary);
					$xml->saveXML();

					$xsl = new DOMDocument();
					$xsl->load(dirname(__FILE__).DIRECTORY_SEPARATOR.'summary.xslt');

					$xslt = new XSLTProcessor();
					$xslt->setParameter('', 'guids', implode(', ', $selected_sources));
					$xslt->importStyleSheet($xsl);

					$report = $xslt->transformToXML($xml);
				}
				break;
			case 2:
				$report = DLEApi::getText('duplicate_document_msg')
					.'<br />';

				$original_document = DLEApi::getDocument($document->duplicate_guid);
				if($original_document) {
					$report .= sprintf(DLEApi::getText('original_document_by'),
						$document->duplicate_student_name,
						$document->duplicate_student_number,
						DLEApi::formatDate($original_document->date_created))."<br />";

					$report .= DLEApi::getText('duplicate_document_download').': '
						.DLEApi::getLink($original_document->id)
						.'<br />';

					$report .= DLEApi::getText('original_report').': '
						.DLEApi::getReportLink($original_document->guid, DLEApi::getText('link_original_report'));
				}
				else
				{
					$report .= sprintf(DLEApi::getText('original_document_by_no_date'),
						$document->duplicate_student_name, $document->duplicate_student_number)
						.'<br />';
				}
				break;
			case 3:
				$report = DLEApi::getText('document_protected_msg');
				break;
			case 4:
				$report = DLEApi::getText('not_enough_text_msg');
				break;
			case 5:
				$report = DLEApi::getText('no_text_msg');
				break;
			case 6:
				$report = DLEApi::getText('unknown_error_msg');
				break;
			case 99:
				$error = !empty($document->error) ? $document->error : 'unknown_file_error';
				$report = DLEApi::getText($error.'_msg');
				break;
			default:
				$report = DLEApi::getText('unknown_error_msg');
				break;
		}
		return $report;
	}

	/**
	 * Function to export the report as pdf
	 *
	 * @param array|string$selected_sources
	 */
	public function exportReport($selected_sources) {

	}
}

/**
 * EphorusStatus Class
 *
 * The class to get and set data the current status of the Ephorus module and ephorus comms
 *
 */
class EphorusStatus {

	/**
	 * Constructor
	 */
	public function EphorusStatus() {
		DLEApi::initialize();
	}

	public function versions()
	{
		$data = DLEApi::getDLEData();

		EphorusService::trace('Version control version:              0.3');
		EphorusService::trace('Digital Learning Environment:         '.$data['dle']);
		EphorusService::trace('Digital Learning Environment version: '.$data['dle_version']);
		EphorusService::trace('Digital Learning Environment release: '.$data['dle_release']);
		EphorusService::trace('Module version:                       '.$data['module_version']);
		EphorusService::trace('Ephorus Comms version:                1.5 (2013-08-05)');
		EphorusService::trace('PHP version:                          '.PHP_VERSION);
		EphorusService::trace('PHP memory limit:                     '.ini_get('memory_limit'));
		EphorusService::trace('XSL:                                  '.((extension_loaded('xsl') == true)?'enabled':'disabled'));
		EphorusService::trace('SOAP:                                 '.((extension_loaded('soap') == true)?'enabled':'disabled'));
		EphorusService::trace('Soap Client:                          '.((class_exists("SOAPClient") == true)?'enabled':'disabled'));
		EphorusService::trace('Soap Server:                          '.((class_exists("SOAPServer") == true)?'enabled':'disabled'));
		EphorusService::trace('Hand-in address:                      '.$data['handin_address']);
		EphorusService::trace('Index document address:               '.$data['index_address']);
	}

	/**
	 * Function to test if all connections are open
	 *
	 * @return array
	 */
	public function connectivityTest()
	{
		return array(
			'handin' => $this->testHandIn(),
			'index'  => $this->testIndex()
		);
	}

	public function testHandIn()
	{
		/* Initiate the service class */
		$service = new EphorusService(false);

		/* Get the hand-in code */
		$handin_code = DLEApi::getSetting('handin_code');
		if($handin_code == '') {
			return false;
		}

		/* Get the hand-in address */
		$handin_address = DLEApi::getSetting('handin_address');
		if($handin_address == '') {
			return false;
		}

		$curl = curl_init($handin_address);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_FAILONERROR, true);

		if(DLEApi::getProxySetting('host')) {
			curl_setopt($curl, CURLOPT_PROXY, DLEApi::getProxySetting('host').':'.DLEApi::getProxySetting('port'));
		}

		if(!curl_exec($curl) || !strpos($handin_address, 'wsdl')) {
			return false;
		}

		/* Initiate soap */
		$soap_client = @$service->initSoapClient($handin_address, 'visibility');
		set_time_limit(610);

		/* Open the test document */
		$test_document = dirname(__FILE__).DIRECTORY_SEPARATOR.'connection_test.txt';
		$file = fopen($test_document, 'r') or die('Cannot open file:  '.$test_document);
		$file = fread($file, filesize($test_document));

		$handin_parameters = array(
			'code'          => $handin_code,
			'firstName'     => 'firstname',
			'middleName'    => 'middlename',
			'lastName'      => 'lastname',
			'studentEmail'  => 'student_email@school.com',
			'studentNumber' => '1',
			'comment'       => 'Document for testing the connectivity',
			'fileName'      => 'connection_test.txt',
			'file'          => base64_encode($file),
			'processType'   => 2,
		);

		/* Try to send file */
		try {
			@$soap_client->UploadDocument($handin_parameters);
			return true;
		} catch(SoapFault $e) {
			return true;
		}
	}

	public function testIndex()
	{
		/* Initiate the service class */
		$service = new EphorusService(false);

		/* Get the index address */
		$index_address = DLEApi::getSetting('index_address');
		if($index_address == '') {
			return false;
		}

		$curl = curl_init($index_address);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);
		curl_setopt($curl, CURLOPT_FAILONERROR, true);
		if(DLEApi::getProxySetting('host')) {
			curl_setopt($curl, CURLOPT_PROXY, DLEApi::getProxySetting('host').':'.DLEApi::getProxySetting('port'));
		}

		if(!curl_exec($curl) || !strpos($index_address, 'wsdl')) {
			return false;
		}

		/* Initiate soap */
		$soap_client = $service->initSoapClient($index_address, 'visibility');

		/* Try to change file's visibility index */
		try {
			@$soap_client->IndexDocument(array('documentGuid' => '123456-123456-123456-123456', 'indexType' => 2));
			return true;
		} catch(SoapFault $e) {
			return true;
		}
	}
}