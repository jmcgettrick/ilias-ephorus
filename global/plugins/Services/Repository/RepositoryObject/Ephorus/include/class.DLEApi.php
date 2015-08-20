<?php
/**
 * class.DLEApi.php - The configurable file of ephorus comms
 *
 * @package    ephorus plagiarism plugin
 * @subpackage ephoruscomms
 * @author     Guido Bonnet
 * @copyright  2012 Guido Bonnet http://ephorus.com
 */

class DLEApi {

	public static function initialize() {

		ini_set("display_errors", 'Off');

		if(!class_exists('ilInitialisation')) {
			chdir(dirname(__FILE__)."/../../../../../../../..");

			include_once "Services/Context/classes/class.ilContext.php";
			ilContext::init(11); // 11 = const unit_test

			require_once "Services/Init/classes/class.ilInitialisation.php";
			ilInitialisation::initILIAS();

			$GLOBALS['WEB_ACCESS_WITHOUT_SESSION'] = true;
		}
	}

	/**
	 * Function to get the Ephorus settings
	 *
	 * @param $setting
	 * @return string
	 */
	public static function getSetting($setting) {
		$settings = new ilSetting("rep_robj_xeph");

		return $settings->get($setting);
	}

	/**
	 * Function to get the DLE's proxy settings
	 *
	 * @param $setting
	 * @return bool|string
	 */
	public static function getProxySetting($setting) {
		$settings = new ilSetting("common");

		switch($setting) {
			case 'host':
				return $settings->get('proxy_host');
				break;
			case 'port':
				return $settings->get('proxy_port');
				break;
			default:
				return false;
		}
	}

	/**
	 * Function to return the documents that have to be send to Ephorus
	 *
	 * @return array
	 */
	public static function getUnsentDocuments() {
		global $ilDB;

		$documents = array();

		$sql = $ilDB->query("SELECT * FROM  rep_robj_xeph_subm ".
			"WHERE guid IS NULL ".
			"AND status = ".$ilDB->quote(0, "integer"));

		while($doc = $ilDB->fetchObject($sql)) {
			array_push($documents, $doc);
		}
		return $documents;
	}

	/**
	 * Function for getting the parameters needed for handing in a document to Ephorus.
	 *
	 * @param object $document - The document there the parameters are needed from
	 * @return array - Hand-in parameters bool - false
	 */
	public static function getHandinParameters($document) {
		$user = ilObjUser::_lookupName($document->user_id);

		return array(
			"code" => self::getSetting('handin_code'),
			"firstName" => $user["firstname"],
			"middleName" => "",
			"lastName" => $user["lastname"],
			"studentEmail" => ilObjUser::_lookupEmail($document->user_id),
			"studentNumber" => $document->user_id,
			"comment" => "",
			"fileName" => $document->filetitle,
			"file" => file_get_contents($document->filename),
			"processType" => $document->processtype
		);
	}

	/**
	 * Function for getting the results for an Report
	 *
	 * @param string $document_guid - The guid from the document where you want results from.
	 * @param bool $as_array -
	 * @return array
	 */
	public static function getResults($document_guid) {
		global $ilDB;

		$sql = "SELECT guid, percentage, type, url, original_guid, student_name, student_number, comparison"
			." FROM rep_robj_xeph_results "
			." WHERE document_guid = ".$ilDB->quote($document_guid, "text")
			." ORDER BY percentage DESC";

		$query = $ilDB->query($sql);

		$records = array();

		while ($result = $ilDB->fetchObject($query)) {
			$records[$result->guid] = $result;
		}

		return $records;
	}

	/**
	 * Function for getting the right (translated) text
	 * @param $identifier_string
	 * @return string
	 */
	public static function getText($identifier_string) {
		global $lng;
		return $lng->txt('rep_robj_xeph_'.$identifier_string);
	}

	/**
	 * Function to get the document information
	 *
	 * @param $document_guid
	 * @return stdClass
	 */
	public static function getDocument($document_guid) {
		global $ilDB;

		$q = "SELECT * FROM  rep_robj_xeph_subm".
			" WHERE guid = ".$ilDB->quote($document_guid, "text")."";
		$set = $ilDB->query($q);
		$document = $ilDB->fetchObject($set);

		return $document;
	}

	/**
	 * Function to get a formatted date
	 *
	 * @param $date
	 * @return string
	 */
	public static function formatDate($date) {
		return ilDatePresentation::formatDate(new ilDateTime($date, IL_CAL_DATETIME));
	}

	/**
	 * Function to get the url of a document
	 *
	 * @param $document_id
	 * @return string
	 */
	public static function getURL($document_id) {
		global $ilCtrl, $ilDB;

		$q = "SELECT filename, filetitle, user_id, ref_id, ass_id FROM rep_robj_xeph_subm s".
			" LEFT JOIN object_reference ref ON ref.obj_id = s.obj_id".
			" WHERE id = ".$ilDB->quote($document_id, "integer")."";
		$set = $ilDB->query($q);
		$document = $ilDB->fetchObject($set);

		$ilCtrl->setParameterByClass('ilObjEphorusGUI', "ref_id", $document->ref_id);
		$ilCtrl->setParameterByClass('ilObjEphorusGUI', "ass_id", $document->ass_id);
		$ilCtrl->setParameterByClass('ilObjEphorusGUI', "filename", $document->filename);
		$ilCtrl->setParameterByClass('ilObjEphorusGUI', "filetitle", $document->filetitle);
		$ilCtrl->setParameterByClass('ilObjEphorusGUI', "part_id", $document->user_id);
		return $ilCtrl->getLinkTargetByClass('ilObjEphorusGUI', "downloadSubmittedFile");
	}

	/**
	 * function to return a link with the document and a value for the anchor
	 *
	 * @param $document_id
	 * @return string
	 */
	public static function getLink($document_id) {
		global $ilDB;

		$q = "SELECT filetitle FROM rep_robj_xeph_subm".
			" WHERE id = ".$ilDB->quote($document_id, "integer")."";
		$set = $ilDB->query($q);
		$document = $ilDB->fetchObject($set);

		return "<a href=\"".self::getURL($document_id)."\">".$document->filetitle."</a>";
	}

	/**
	 * Function to return a link to the report of the selected document
	 *
	 * @param $document_guid
	 * @param $string
	 * @return string
	 */
	public static function getReportLink($document_guid, $string) {
		global $ilDB, $ilCtrl;

		$q = "SELECT ref_id FROM rep_robj_xeph_subm s".
			" LEFT JOIN object_reference ref ON ref.obj_id = s.obj_id".
			" WHERE s.guid = ".$ilDB->quote($document_guid, "text")."";
		$set = $ilDB->query($q);
		$document = $ilDB->fetchObject($set);

		$ilCtrl->setParameterByClass('ilObjEphorusGUI', "ref_id", $document->ref_id);
		$ilCtrl->setParameterByClass('ilObjEphorusGUI', "doc_id", $document_guid);
		return '<a href="'.$ilCtrl->getLinkTargetByClass('ilObjEphorusGUI', "viewReport").'">'.$string.'</a>';
	}

	/**
	 * Function to receive data from the DLE and environment
	 *
	 * @return array
	 */
	public static function getDLEData() {
		global $ilDB;

		$settings = new ilSetting();
		$eph_settings = new ilSetting("rep_robj_xeph");

		$sql = $ilDB->query("SELECT last_update_version FROM il_plugin ".
				" WHERE plugin_id = ".$ilDB->quote('xeph', "text")
		);
		$version = $ilDB->fetchAssoc($sql);

		return array(
			'dle'            => "Ilias",
			'dle_version'    => $settings->get("ilias_version"),
			'dle_release'    => "-",
			'module_versiom' => $version["last_update_version"],
			'handin_address' => $eph_settings->get("handin_address"),
			'index_address'  => $eph_settings->get("index_address"),
		);
	}

	/**
	 * Function to check if a document exists in the database
	 *
	 * @param $document_guid
	 * @return bool|int - Returns false if not, document_id if does
	 */
	public static function checkDocumentExists($document_guid) {
		global $ilDB;
		// Check if the document exists and return document id if it does
		// If not, return false
		$q = "SELECT id FROM  rep_robj_xeph_subm".
			" WHERE guid = ".$ilDB->quote($document_guid, "text")."";
		$set = $ilDB->query($q);
		$document = $ilDB->fetchAssoc($set);

		if($document) {
			return $document['id'];
		}
		return false;
	}

	/**
	 * Function to set an error to a document e.g. wrong filetype
	 *
	 * @param int $document_id
	 * @param string $error
	 */
	public static function setHandinErrorToDocument($document_id, $error = '') {
		global $ilDB;

		$ilDB->manipulateF("UPDATE  rep_robj_xeph_subm ".
				"SET status = 99,  error = %s".
				" WHERE id = %s",
			array("text", "integer"),
			array($error, $document_id));
	}

	/**
	 * Function to set a guid to a document
	 *
	 * @param $document_id
	 * @param $guid
	 */
	public static function setGUIDtoDocument($document_id, $guid) {
		global $ilDB;

		$ilDB->manipulateF("UPDATE  rep_robj_xeph_subm ".
				"SET guid = %s ".
				" WHERE id = %s",
			array("text", "integer"),
			array($guid, $document_id));
	}

	/**
	 * Function to change the visibility index of a document
	 *
	 * @param $document_guid
	 * @param $visibility_index
	 */
	public static function changeVisibility($document_guid, $visibility_index) {
		global $ilDB;

		$ilDB->manipulateF("UPDATE  rep_robj_xeph_subm ".
				"SET visibility_index = %s ".
				" WHERE guid = %s",
			array("integer", "text"),
			array($visibility_index, $document_guid));
	}

	/**
	 * Function to update the document and save the report
	 *
	 * @param $document
	 * @param $results
	 */
	public static function saveReport($document, $results) {
		global $ilDB;

		$ilDB->manipulate("DELETE FROM  rep_robj_xeph_results WHERE ".
				" document_guid = ".$ilDB->quote($document->guid, "integer")
		);

		$ilDB->manipulateF("UPDATE  rep_robj_xeph_subm ".
				"SET status = %s, percentage = %s, summary = %s, ".
				"duplicate_guid = %s, duplicate_student_name = %s, duplicate_student_number = %s ".
				"WHERE guid = %s",
			array("integer", "integer", "text", "text", "text", "text", "text"),
			array(
				$document->status, $document->percentage, $document->summary,
				$document->duplicate_guid, $document->duplicate_student_name, $document->duplicate_student_number,
				$document->guid
			)
		);

		foreach($results as $result)
		{
			$next_id = $ilDB->nextId("rep_robj_xeph_results");

			$ilDB->manipulateF("INSERT INTO rep_robj_xeph_results (id, guid, document_guid, url, type, ".
					"percentage, comparison, original_guid, student_name, student_number".
					") VALUES ".
					"(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
				array("integer", "text", "text", "text", "text", "integer", "text", "text", "text", "text"),
				array($next_id,
					$result->guid, $result->document_guid, $result->url, $result->type,
					$result->percentage, $result->comparison, $result->original_guid, $result->student_name,
					$result->student_number
				)
			);
		}
	}
}