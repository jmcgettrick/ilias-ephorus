<?php

/**
 * handinservice.php - File for sending documents to Ephorus
 *
 * @package    Ephoruscomms
 * @subpackage Handin Service
 * @author     Guido Bonnet
 * @copyright  2012 onwards Ephorus  {@link http://ephorus.com}
 */

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'class.EphorusApi.php');

$hand_in_service = new EphorusService();
$hand_in_service->handInService();