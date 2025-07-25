<?php

require_once 'vieweventparticipants.civix.php';
use CRM_Vieweventparticipants_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function vieweventparticipants_civicrm_config(&$config) {
  _vieweventparticipants_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function vieweventparticipants_civicrm_install() {
  _vieweventparticipants_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function vieweventparticipants_civicrm_enable() {
  CRM_Core_Session::setStatus(
    ts(
      "To use the extension: in your CMS's permissions management screen, grant the 'view my event participants' or 'edit my event participants' permissions to the users/roles who need to be able to view/edit their event participants.
      See the <a href=\"%1\">documentation</a> for details.",
      array(
        'domain' => 'org.civicrm.vieweventparticipants',
        1 => 'https://github.com/circleinteractive/org.civicrm.vieweventparticipants/blob/master/README.md',
      )
    ),
    ts(
      '"View My Event Participants" extension enabled',
      array('domain' => 'org.civicrm.vieweventparticipants')
    ),
    'success'
  );
  _vieweventparticipants_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_permission().
 *
 * @link https://docs.civicrm.org/dev/en/master/hooks/hook_civicrm_permission/
 */
function vieweventparticipants_civicrm_permission(&$permissions) {
  $permissions['view my event participants'] = [
    'label' => E::ts('CiviEvent: view my event participants'),
    'description' => E::ts('Grants event creators permission to view their event\'s participants'),
  ];

  $permissions['edit my event participants'] = [
    'label' => E::ts('CiviEvent: edit my event participants'),
    'description' => E::ts('Grants event creators permission to edit their event\'s participants'),
  ];
}

/**
 * Implements hook_civicrm_aclWhereClause().
 *
 * @link https://docs.civicrm.org/dev/en/master/hooks/hook_civicrm_aclWhereClause/
 */
function vieweventparticipants_civicrm_aclWhereClause($type, &$tables, &$whereTables, &$contactID, &$where) {
  /*
   * Grant access for event creators to view or edit their events' participants.
   */
  if (!$contactID) {
    return;
  }

  if ($type != CRM_Core_Permission::VIEW && $type != CRM_Core_Permission::EDIT) {
    return;
  }

  /*
   * Only allow access if user also has our permission.
   */
  if ($type == CRM_Core_Permission::VIEW && !CRM_Core_Permission::check('view my event participants')) {
    return;
  }

  if ($type == CRM_Core_Permission::EDIT && !CRM_Core_Permission::check('edit my event participants')) {
    return;
  }

  if (!in_array('civicrm_participant', $whereTables)) {
    $tables['civicrm_participant'] = $whereTables['civicrm_participant']
      = "LEFT JOIN civicrm_participant ON contact_a.id = civicrm_participant.contact_id";
  }

  if (!in_array('civicrm_event', $whereTables)) {
    $tables['civicrm_event'] = $whereTables['civicrm_event']
      = "LEFT JOIN civicrm_event ON civicrm_participant.event_id = civicrm_event.id";
  }

  /*
   * If other ACLs are in place, e.g. through ACL UI, then we allow access to
   * the user's events' participants in addition to the contacts permitted by
   * these other ACLs. Hence OR.
   */
  if (!empty($where)) {
    $where = "($where) OR ";
  }

  $where .= sprintf("(civicrm_event.created_id = %d)", $contactID);
}
