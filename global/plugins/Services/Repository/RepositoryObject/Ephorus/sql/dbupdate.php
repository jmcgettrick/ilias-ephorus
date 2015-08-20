<#1>
<?php // Data
$fields = array(
	'obj_id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'show_submissions' => array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => true,
		'default' => 0
	),
	'pass_mode' => array(
		'type' => 'text',
		'length' => 8,
		'fixed' => false,
		'notnull' => true,
		'default' => 'all'
	),
	'min_number' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => false
	),
	'notification' => array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => true,
		'default' => 0
	),
	'copletion_by_submission' => array(
		'type'		=> 'integer',
		'length'	=> 1,
		'notnull'	=> true,
		'default'	=> 0
	),
	'processtype' => array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => true
	)
);
$ilDB->createTable("rep_robj_xeph_data", $fields);
$ilDB->addPrimaryKey("rep_robj_xeph_data", array("obj_id"));
?>
<#2>
<?php // Assignment
$fields = array(
	'id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'eph_id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'deadline' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => false
	),
	'instruction' => array(
		'type' => 'clob',
		'notnull' => false
	),
	'title' => array(
		'type' => 'text',
		'length' => 200,
		'notnull' => false
	),
	'start_time' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => false
	),
	'mandatory' => array(
		'type' => 'integer',
		'length' => 4,
		'default' => 1,
		'notnull' => true
	),
	'order_nr' => array(
		'type' => 'integer',
		'length' => 4,
		'default' => 0,
		'notnull' => true
	)
);
$ilDB->createTable("rep_robj_xeph_assign", $fields);
$ilDB->addPrimaryKey("rep_robj_xeph_assign", array("id"));
$ilDB->createSequence('rep_robj_xeph_assign', 1);
?>
<#3>
<?php // Submissions / Documents
$fields = array(
	'id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'obj_id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'ass_id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => false
	),
	'user_id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'filename' => array(
		'type' => 'text',
		'length' => 1000,
		'notnull' => false
	),
	'filetitle' => array(
		'type' => 'text',
		'length' => 1000,
		'notnull' => false
	),
	'mimetype' => array(
		'type' => 'text',
		'length' => 40,
		'notnull' => false
	),
	'date_created' => array(
		'type' => 'timestamp',
		'notnull' => false
	),
	'guid' => array(
		'type' => 'text',
		'length' => 36,
		'notnull' => false
	),
	'processtype' => array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => true
	),
	'visibility_index' => array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => true
	),
	'percentage' => array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => true
	),
	'status' => array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => true
	),
	'error' => array(
		'type' => 'text',
		'length' => 30,
		'notnull' => true
	),
	'duplicate_guid' => array(
		'type' => 'text',
		'length' => 36,
		'notnull' => false
	),
	'duplicate_student_name' => array(
		'type' => 'text',
		'length' => 40,
		'notnull' => false
	),
	'duplicate_student_number' => array(
		'type' => 'text',
		'length' => 25,
		'notnull' => false
	),
	'summary' => array(
		'type' => 'clob',
		'notnull' => false
	),
);
$ilDB->createTable("rep_robj_xeph_subm", $fields);
$ilDB->addPrimaryKey("rep_robj_xeph_subm", array("id"));
$ilDB->createSequence('rep_robj_xeph_subm', 1);
?>
<#4>
<?php // Members
$fields = array(
	'obj_id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'user_id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'returned' => array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false
	),
	'solved' => array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false
	),
	'notice' => array(
		'type' => 'text',
		'length' => 4000,
		'notnull' => false,
		'fixed' => false
	),
	'status' => array(
		'type' => 'text',
		'length' => 9,
		'notnull' => false,
		'default' => 'notgraded'
	),
	'status_time' => array(
		'type' => 'timestamp',
		'notnull' => false
	),
	'sent' => array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => true
	),
	'sent_time' => array(
		'type' => 'timestamp',
		'notnull' => false
	),
	'feedback' => array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => true
	),
	'feedback_time' => array(
		'type' => 'timestamp',
		'notnull' => false
	)
);
$ilDB->createTable('rep_robj_xeph_members', $fields);
$ilDB->addPrimaryKey('rep_robj_xeph_members', array('obj_id','user_id'));
$ilDB->addIndex('rep_robj_xeph_members', array('obj_id'), 'ob', false);
?>
<#5>
<?php // Members Assignments Status
$fields = array(
	'ass_id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'user_id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'returned' => array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false
	),
	'solved' => array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false
	),
	'notice' => array(
		'type' => 'text',
		'length' => 4000,
		'notnull' => false,
		'fixed' => false
	),
	'status' => array(
		'type' => 'text',
		'length' => 9,
		'notnull' => false,
		'default' => 'notgraded'
	),
	'status_time' => array(
		'type' => 'timestamp',
		'notnull' => false
	),
	'sent' => array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false
	),
	'sent_time' => array(
		'type' => 'timestamp',
		'notnull' => false
	),
	'feedback' => array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => true,
		'default' => 0
	),
	'feedback_time' => array(
		'type' => 'timestamp',
		'notnull' => false
	),
	'mark' => array (
		'type' => 'text',
		'length' => 32,
		'notnull' => false,
		'fixed' => false,
	),
	'user_comment' => array (
		'type' => 'text',
		'length' => 4000,
		'notnull' => false,
		'fixed' => false
	)
);
$ilDB->createTable('rep_robj_xeph_ass_stat', $fields);
$ilDB->addPrimaryKey('rep_robj_xeph_ass_stat', array('ass_id','user_id'));
?>
<#6>
<?php // Tutors
$fields = array(
	'obj_id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'ass_id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'user_id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'tutor_id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'download_time' => array(
		'type' => 'timestamp',
		'notnull' => false
	)
);
$ilDB->createTable('rep_robj_xeph_tutor', $fields);
$ilDB->addPrimaryKey('rep_robj_xeph_tutor', array('ass_id','user_id','tutor_id'));
$ilDB->addIndex('rep_robj_xeph_tutor', array('obj_id'), 'ob', false);
?>
<#7>
<?php // Results
$fields = array(
	'id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'guid' => array(
		'type' => 'text',
		'length' => 36,
		'notnull' => true,
		'fixed' => true
	),
	'document_guid' => array(
		'type' => 'text',
		'length' => 36,
		'notnull' => false,
		'fixed' => true
	),
	'url' => array(
		'type' => 'text',
		'length' => 255,
		'notnull' => true,
		'fixed' => false
	),
	'type' => array(
		'type' => 'text',
		'length' => 10,
		'notnull' => false,
		'fixed' => false
	),
	'percentage' => array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false
	),
	'comparison' => array(
		'type' => 'clob',
		'notnull' => false
	),
	'original_guid' => array(
		'type' => 'text',
		'length' => 36,
		'notnull' => false,
		'fixed' => true
	),
	'student_name' => array(
		'type' => 'text',
		'length' => 40,
		'notnull' => false
	),
	'student_number' => array(
		'type' => 'text',
		'length' => 25,
		'notnull' => false
	)
);
$ilDB->createTable('rep_robj_xeph_results', $fields);
$ilDB->addPrimaryKey('rep_robj_xeph_results', array('id'));
$ilDB->addIndex('rep_robj_xeph_results', array('document_guid'), 'dgd', false);
$ilDB->createSequence('rep_robj_xeph_results', 1);
?>
<#8>
<?php // Settings
$ilDB->manipulate("INSERT IGNORE INTO settings ".
		"(module, keyword, value) VALUES".
	"(".
		$ilDB->quote("rep_robj_xeph", "text").",".
		$ilDB->quote("ephorus_logging", "text").",".
		$ilDB->quote(0, "integer").
	"), (".
		$ilDB->quote("rep_robj_xeph", "text").",".
		$ilDB->quote("handin_code", "text").",".
		$ilDB->quote("", "text").
	"), (".
		$ilDB->quote("rep_robj_xeph", "text").",".
		$ilDB->quote("handin_address", "text").",".
		$ilDB->quote("http://services.ephorus.com/handinservice/handinservice.asmx?wsdl", "text").
	"), (".
		$ilDB->quote("rep_robj_xeph", "text").",".
		$ilDB->quote("index_address", "text").",".
		$ilDB->quote("http://services.ephorus.com/indexdocumentservice/indexdocumentservice.asmx?wsdl", "text").
	"), (".
		$ilDB->quote("rep_robj_xeph", "text").",".
		$ilDB->quote("processtype", "text").",".
		$ilDB->quote(3, "integer").
	"), (".
		$ilDB->quote("rep_robj_xeph", "text").",".
		$ilDB->quote("disclosure", "text").",".
		$ilDB->quote("", "text").
	")"
);
?>