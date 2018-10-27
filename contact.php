<?php
/******************************************************************
* Copyright © 2011 ICT Innovations Pakistan All Rights Reserved   *
* Developed By: Nasir Iqbal                                       *
*             : Tahir Almas                                       *
* Website : http://www.ictinnovations.com/                        *
* Mail : info@ictinnovations.com                                  *
******************************************************************/

include_once "lib/lib.php";
include_once "soapclient/SforceEnterpriseClient.php";

output_set('request', 'contact');

/**
 * Top menu entries
 *
 * @return
 *   An array contain all top level items / buttons
 */
function contact_menu() {
  $menu = array();
  $menu['add'] = array(
   'title'       => t('Add new'),
   'description' => t('Add new Contact'),
   'href'        => 'contact.php?action=add',
   'class'       => 'action add'
  );
  $menu['import'] = array(
   'title'       => t('Import'),
   'description' => t('Import new Contact'),
   'href'        => 'contact.php?action=import',
   'class'       => 'action addcat'
  );
  $menu['salesforce'] = array(
   'title'       => t('SalesForce Contact'),
   'description' => t('Import Contacts sales force account'),
   'href'        => 'contact.php?action=salesforce',
   'class'       => 'action addcat'
  );
  $menu['export'] = array(
   'title'       => t('Export'),
   'description' => t('Export Current Report in CSV'),
   'href'        => 'contact.php?action=export',
   'class'       => 'action download'
  );

  
  $menu['dnc'] = array(
   'title'       => t('Apply DNC'),
   'description' => t('Apply DNC'),
   'href'        => 'contact.php?action=applydnc',
   'class'       => 'action dnc'
  );
  $menu['refresh'] = array(
   'title'       => t('Refresh'),
   'description' => t('Refresh Contact list'),
   'href'        => 'contact.php?action=list',
   'class'       => 'action reorder'
  );
  $menu['save'] = array(
   'title'       => t('Save'),
   'description' => t('Save Contact'),
   'href'        => "javascript:formSubmit('editForm','submitted',1,1)",
   'class'       => 'action save'
  );
  $menu['reset'] = array(
   'title'       => t('Reset'),
   'description' => t('Reset Contact record'),
   'href'        => "javascript:formReset('editForm');",
   'class'       => 'action undo'
  );
  $menu['cancel'] = array(
   'title'       => t('Cancel'),
   'description' => t('Discard changes and show contact list'),
   'href'        => "contact.php?action=view",
   'class'       => 'action cancel'
  );

  return $menu;
}

/**
 * Control follow of execution
 */
function contact_navigate() {

  switch (input_get('action')) {

    case 'ajax':
      echo contact_ajax();
      break;

    case 'ajax_crm':
      echo crm_ajax();
      break;


    case 'search':
      $result = contact_search_submit(input_get('searchForm', array()));
      input_set('action', 'list');
      contact_navigate();
      break;

    case 'reset':
      session_set('contact:filter', array());
      input_set('action', 'list');
      contact_navigate();
      break;

    case 'delete':
      can_access('contact_delete', "contact.php");
      $result = contact_delete(input_get('listForm', array()));
      input_set('action', 'redirect'); // redirect to list
      contact_navigate();
      break;
      
    case 'applydnc':
      can_access('contact_delete', "contact.php");
      $result = contact_applydnc(input_get('listForm', array()));
      input_set('action', 'list'); // redirect to list
      contact_navigate();
      break;

    case 'add':
      can_access('contact_add', "contact.php");

      output_set('page_title',  t('Contact :: Add'));
      output_set('page_action', t('Add new Contact'));
      output_set('action',      'update');

      // get form data to keep user input after error on submit
      $form = contact_form(input_get('editForm', array()));

      output_set('body',     formGenerate($form, 'editForm', array('class' => 'formalign'), '    '));
      output_set('top_menu', contact_topMenu(array('save', 'reset', 'cancel')));
      break;

    case 'insert':
      can_access('contact_insert', "contact.php");
      $data    = input_get('editForm', array());
      $result  = contact_validate($data);
      if ($result != false) {
        $result = contact_submit($data);
      }
      input_set('action', ($result) ? 'redirect' : 'add'); // in case of error redirect to add otherwise to list
      contact_navigate();
      break;

    case 'edit':
      can_access('contact_edit', "contact.php");

      output_set('page_title',  t('Contact :: Edit'));
      output_set('page_action', t('Edit Contact'));
      output_set('action',      'update');

      // get form data to keep user input after error on submit, or load required contact
      $form = contact_form(input_get('editForm', contact_load(input_get('contact_id'))));

      output_set('body',     formGenerate($form, 'editForm', array('class' => 'formalign'), '    '));
      output_set('top_menu', contact_topMenu(array('save', 'reset', 'cancel')));
      break;

    case 'update':
      can_access('contact_update', "contact.php");
      $data = input_get('editForm', array());
      $result  = contact_validate($data);
      if ($result != false) {
        $result = contact_submit($data);
      }
      input_set('action', ($result) ? 'redirect' : 'edit'); // in case of error redirect to edit otherwise to list
      contact_navigate();
      break;

    case 'export':
      can_access('contact_list', "contact.php");
      contact_export();      
      input_set('action', 'list');
      contact_navigate();
      break;

    case 'import':
      can_access('contact_add', "contact.php");

      output_set('page_title',  t('Contact :: Import'));
      output_set('page_action', t('Import Contact'));
      output_set('action',      'import_insert');

      // get form data to keep user input after error on submit
      $form = contact_import_form(input_get('editForm', array()));

      output_set('body',     formGenerate($form, 'editForm', array('class' => 'formalign'), '    '));
      output_set('top_menu', contact_topMenu(array('save', 'reset', 'cancel')));
      break;


       case 'salesforce':
      can_access('contact_add', "contact.php");

      output_set('page_title',  t('Contact :: Salesforce'));
      output_set('page_action', t('Salesforce Contact'));
      output_set('action',      'salesforcecontact_insert');

      // get form data to keep user input after error on submit
      $form = salesforce_contact_form(input_get('editForm', array()));

      output_set('body',     formGenerate($form, 'editForm', array('class' => 'formalign'), '    '));
      output_set('top_menu', contact_topMenu(array('save', 'reset', 'cancel')));
      break;

    case 'import_insert':
      can_access('contact_insert', "contact.php");
      $result = contact_import_submit(input_get('editForm', array()));

      input_set('action', ($result) ? 'redirect' : 'import'); // in case of error redirect to import otherwise to list
      contact_navigate();
      break;
      case 'salesforcecontact_insert':

      can_access('contact_insert', "contact.php");
      $data    = input_get('editForm', array());
      $result  = contact_salesforce_validate($data);


      if ($result != false) {
        //echo "adrrl";
        $result = contact_salesforce_submit($data);
        //echo "<pre>";print_r($result);
        //exit;
      }
      //echo "adrrl";
        //$result = contact_salesforce_submit($data);
       // echo "<pre>";print_r($result);
        //exit;
      //echo "ttttt<pre>";print_r($result);exit;
      input_set('action', ($result) ? 'redirect' : 'salesforce'); // in case of error redirect to add otherwise to list
      //contact_salesforce_validate();
       contact_navigate();
      break;

      /*can_access('contact_insert', "contact.php");
      $result = contact_salesforce_submit(input_get('editForm', array()));

      input_set('action', ($result) ? 'redirect' : 'import'); // in case of error redirect to import otherwise to list
      contact_navigate();
      break;*/

    case 'redirect':
      header("Location: " . "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
      exit();
      break;
      
    case 'list':
    default:
      can_access('contact_list', "contact.php");

      output_set('page_title', t('Contact :: List'));
      output_set('page_action', t('Contact List'));

      $listForm = array(
          'contact_group_id' => input_get('contact_group_id', null),
          'usr_id'           => input_get('usr_id', null),
          'name'             => input_get('name', null),
          'phone'            => input_get('phone', null),
          'email'            => input_get('email', null)
      );
      contact_search_submit($listForm);

      output_set('action', 'search');
      $form  = contact_search_form();
      $body  = formGenerate($form, 'searchForm', array('class' => 'listalign'), '    ');

      output_set('action', 'edit');
      if (session_get('contact:filter:group_filter', '') == 'duplicate') {
        $form  = contact_list_duplicate($listForm);
      } else {
        $form  = contact_list($listForm);
      }
      $body   .= formGenerate($form, 'listForm', array('class' => 'formalign'), '    ');

      output_set('body', $body);
      output_set('top_menu', contact_topMenu(array('add', 'refresh', 'import','salesforce','export', 'dnc'))); 
      break;
  }
}

function contact_ajax() {
  $response = array();

  switch (input_get('req_name')) {
    case 'searchForm-usr_id':
      $usr_id = input_get('req_value');
      $query  = 'SELECT contact_group_id, name FROM contact_group';
      if (isset($usr_id) && $usr_id != -1) { 
        $query .= " WHERE created_by=$usr_id";
      }
      $options = array(-1 => t('[All Groups]')) + getSelectData_custom('contact_group', $query);
      $response['contact_group_id'] = array(
          'type'      => 'html', 
          'selection' => '#searchForm-contact_group_id', 
          'html'      => generateSelect($options)
      );
      break;

    case 'salesforce_campaign':
    $salesforce_credential = explode('_',input_get('req_value'));
    $campaings = get_salesforce_campaing($salesforce_credential[0],$salesforce_credential[1],$salesforce_credential[2]);
    $get_campaign_aray = object_to_array1($campaings->records);
    
$i=0;
foreach($get_campaign_aray as $val){
  if($i==0){
$options[] ='<option value="-1">Select Campaign</option>';
  }
  $i++;
  $options[] ='<option value='.$val["Id"].'>'.$val["Name"].'</option>/n';
}
//echo "<pre>";print_r($options );

      $response['salesforce_campaign'] = array(
          'type'      => 'html', 
          'selection' => '#editForm-salesforce_campaign', 
          'html'      => $options
      );



      break;
  }

  echo json_encode($response);
  exit();
}
function get_salesforce_campaing($username,$password,$token)
{
  try {

    $mySforceConnection = new SforceEnterpriseClient();
    $mySforceConnection->createConnection("soapclient/enterprise.wsdl.xml");
    $mySforceConnection->login($username, $password. $token);

    $mySforceConnection->getLocation();
    $mySforceConnection->getSessionId();

    //echo "Logged in with enterprise<br/><br/>\n";
    $query1 = "SELECT Id,Name from  Campaign";
    $response1 = $mySforceConnection->query($query1);
        return  $response1 ;
   // echo "eee<pre>";print_r(( array ) $response1->records);
  }catch (Exception $e) {
      echo "Exception ".$e->faultstring."<br/><br/>\n";
     /* echo "Last Request:<br/><br/>\n";
      echo $mySforceConnection->getLastRequestHeaders();
      echo "<br/><br/>\n";
      echo $mySforceConnection->getLastRequest();
      echo "<br/><br/>\n";
      echo "Last Response:<br/><br/>\n";
      echo $mySforceConnection->getLastResponseHeaders();
      echo "<br/><br/>\n";
      echo $mySforceConnection->getLastResponse();*/
  }

}

function object_to_array1($data)
{
    if (is_array($data) || is_object($data))
    {
        $result = array();
        foreach ($data as $key => $value)
        {
            $result[$key] = object_to_array1($value);
        }
        return $result;
    }
    return $data;
}


function crm_ajax() {
  $response = array();

  switch (input_get('req_name')) {
  

    case 'editForm-username':
    echo  $username = input_get('req_value');
    break;
    case 'editForm-password':
    echo  $password = input_get('req_value');
    break;
    case 'editForm-button':
    echo  $password = input_get('req_value');
    break;
     case 'button3434':
    echo  $password = input_get('req_value');
    break;
    case 'editForm-tokent':
     echo  $token = input_get('req_value');
     $a=array();
     array_push($a,input_get('req_value'));
     print_r($a);
    // echo  $dsd = input_get('req_value');
     //echo  $yty = input_get('req_value');

      /*$query  = 'SELECT contact_group_id, name FROM contact_group';
      if (isset($usr_id) && $usr_id != -1) { 
        $query .= " WHERE created_by=$usr_id";
      }
      $options = array(-1 => t('[All Groups]')) + getSelectData_custom('contact_group', $query);
      $response['contact_group_id'] = array(
          'type'      => 'html', 
          'selection' => '#searchForm-contact_group_id', 
          'html'      => generateSelect($options)
      );*/
      break;
      case 'hide_field':
    echo input_get('req_value');
    break;
  }

  echo json_encode($response);
  exit();
}

/**
 * Loader function for individual contacts.
 *
 * @param $contact_id
 *   An int containing the ID of a contact.
 * @return
 *   A single contact along with assigned groups in array format, 
 */
function contact_load($primaryKey) {
  $query  = "SELECT *
             FROM contact
             WHERE contact_id='%contact_id%' AND %auth_filter%";
  $result = db_query('contact', $query, array('contact_id' => $primaryKey), true)
    or mssgLog(t('Unable to get contact, query failed'), 'error');
  $data   = mysql_fetch_assoc($result);

  // load assigned contact_group
  $query  = "SELECT cg.contact_group_id, cg.name
             FROM contact_group cg
               RIGHT JOIN contact_link cl
                 ON cg.contact_group_id = cl.contact_group_id
             WHERE cl.contact_id = $primaryKey AND cg.%auth_filter%";
  $data['contact_group_id'] = getSelectData_custom('contact_group', $query, true);

  return $data;
}

/**
 * Build the contact editing form.
 *
 * @parms $editForm
 *   data of existing contact or data from last submited failed attemp
 * @return
 *   a error containing form items
 * @see contact_form_submit()
 */
function contact_form($editForm) {
  // It's safe to use on both an empty array, and an incoming array with full or partial data.
  $editForm += array(
    'contact_id'      => '',
    'first_name'      => '',
    'last_name'       => '',
    'phone'           => '',
    'email'           => '',
    'address'         => '',
    'custom1'         => '',
    'custom2'         => '',
    'custom3'         => '',
    'description'     => '',
    'contact_group_id'=> array(''),
  );

  $form['personal'] = array(
    '#type'          => 'fieldset',
    '#title'         => t('Personal Information'),
    '#attributes'    => array('class' => 'inside')
  );
  $form['personal']['contact_id'] = array(
    '#type'          => 'hidden',
    '#default_value' => $editForm['contact_id'],
  );
  $form['personal']['first_name'] = array(
    '#type'          => 'textfield',
    '#title'         => t('First Name'),
    '#default_value' => $editForm['first_name'],
  );
  $form['personal']['last_name'] = array(
    '#type'          => 'textfield',
    '#title'         => t('Last Name'),
    '#default_value' => $editForm['last_name'],
  );
  $form['contact'] = array(
    '#type'          => 'fieldset',
    '#title'         => t('Contact Addresses'),
    '#attributes'    => array('class' => 'inside')
  );
  $form['contact']['phone'] = array(
    '#type'          => 'textfield',
    '#title'         => t('Phone Number'),
    '#required'      => true,
    '#default_value' => $editForm['phone'],
  );
  $form['contact']['email'] = array(
    '#type'          => 'textfield',
    '#title'         => t('E-Mail'),
    '#default_value' => $editForm['email'],
  );
  $form['contact']['address'] = array(
    '#type'          => 'textfield',
    '#title'         => t('Address'),
    '#default_value' => $editForm['address'],
  );
  $form['group'] = array(
    '#type'          => 'fieldset',
    '#title'         => t('Groups'),
    '#attributes'    => array('class' => 'inside')
  );
  // get list of contact_group
  $options = contactGroup_getSelect();
  $form['group']['contact_group_id'] = array(
    '#type'          => 'select',
    '#title'         => t('Related Groups'),
    '#options'       => $options,
    '#default_value' => $editForm['contact_group_id'],
    '#multiple'      => true,
  );
  $form['custom'] = array(
    '#type'          => 'fieldset',
    '#title'         => t('Custom Data'),
    '#attributes'    => array('class' => 'inside')
  );
  $form['custom']['custom1'] = array(
    '#type'          => 'textfield',
    '#title'         => t('Custom Value #1'),
    '#default_value' => $editForm['custom1'],
  );
  $form['custom']['custom2'] = array(
    '#type'          => 'textfield',
    '#title'         => t('Custom Value #2'),
    '#default_value' => $editForm['custom2'],
  );
  $form['custom']['custom3'] = array(
    '#type'          => 'textfield',
    '#title'         => t('Custom Value #3'),
    '#default_value' => $editForm['custom3'],
  );
  $form['remark'] = array(
    '#type'          => 'fieldset',
    '#title'         => t('Comments / Remarks'),
    '#attributes'    => array('class' => 'inside')
  );
  $form['remark']['description'] = array(
    '#type'          => 'textarea',
    '#title'         => t('Description'),
    '#default_value' => $editForm['description'],
  );

  return $form;
}

/**
 * Validate user entered data before submitted to db.
 *
 * @param $form
 *   Array of all form fields as submitted by user
 * @return
 *   boolean value, and also set global error object in case of error.
 */

function contact_validate(&$form) {

  
  if (is_array($form['contact_group_id']) && count($form['contact_group_id']) > 0) {
    // nothing to do
  } else {
    error_set('contact_group_id', t('Please select at least one group'));
  }

  if (!isset($form['phone']) || empty($form['phone'])) {
    error_set('phone', t('Please enter a valid phone number'));
  }

  if ($form['email'] != '' && !validate_email($form['email'])) {
    error_set('email', t('Invalid email! please enter a valid email address'));
  }

  if (count(output_get('errors')) > 0) {
    mssgLog(t('Please fill in the indicated fields'), 'error');
    return false;
  }
  return true;
}

function contact_salesforce_validate(&$form) {
  

  /*if (is_array($form['contact_group_id']) && count($form['contact_group_id']) > 0) {
    // nothing to do
  } else {
    error_set('contact_group_id', t('Please select at least one group'));
  }*/

  if (!isset($form['username']) || empty($form['username'])) {
    error_set('username', t('Please enter a valid username'));
  }

  if ($form['password'] == '') {
    error_set('password', t('please enter password'));
  }
  if ($form['token'] == '') {
    error_set('token', t('please enter security token'));
  }

  if ($form['salesforce_campaign'] == '-1' || $form['salesforce_campaign'] == '') {
    error_set('salesforce_campaign', t('please select salesforce campaign'));
  }
  if ($form['contact_group_id'] == '-1' || $form['contact_group_id'] == '') {
    error_set('contact_group_id', t('please select group '));
  }

  if (count(output_get('errors')) > 0) {
    mssgLog(t('Please fill in the indicated fields'), 'error');
    return false;
  }
  return true;
}

/**
 * Inserts a new contact, or updates an existing one.
 *
 * @param $editForm
 *   A contact to be saved. If $contact['contact_id'] is set, the contact will be updated.
 *   Otherwise, a new contact will be inserted into the database.
 * @return
 *   A boolean indicate if operation successful or failed.
 */
function contact_submit($editForm) {
  if (isset($editForm['contact_id']) && !empty($editForm['contact_id'])) {
    // update existing record
    $result = db_update('contact', $editForm, 'contact_id', true);
    $query  = 'DELETE FROM contact_link WHERE contact_id=%contact_id%'; // TODO: add auth filter
    $result = mysql_query('SET @ib_contact_update=1');
    $result = db_query('contact_link', $query, array('contact_id' => $editForm['contact_id']), true);
    $result = mysql_query('SET @ib_contact_update=NULL');
  } else {
    // add new
    $result = db_update('contact', $editForm, false, true);
    $editForm['contact_id'] = mysql_insert_id();
  }
  // TODO: add auth filter
  foreach ($editForm['contact_group_id'] as $contact_group_id) {
    // create link between recently created content and selected groups
    $query  = "INSERT INTO contact_link (contact_id, contact_group_id) VALUES (%contact_id%, %contact_group_id%)";
    $result = db_query('contact', $query, array('contact_id'       => $editForm['contact_id'],
                                             'contact_group_id' => $contact_group_id
                                            ), true);
  }

  if ($result) {
    mssgLog(t('Contact saved successfully'), 'info');
  } else {
    mssgLog(t('Unable to save contact'), 'error');
  }
  return $result;
}

/**
 * Deletes a contact, given its unique ID.
 *
 * @param $listForm
 *   An array of contact containing the ID of a contact and assigned groups.
 */
function contact_delete($listForm) {
  if (is_array($listForm['frmSelect'])) {
    foreach ($listForm['frmSelect'] as $index => $primaryKey) {
      // first delete all assosiation for that contact in group_link table
      db_delete('contact_link', 'contact_id', $primaryKey, true, 'contact', 'contact_id', $primaryKey);
      db_delete('contact', 'contact_id', $primaryKey, true);
    }
    mssgLog(t('Contact deleted successfully'), 'info');
  } else {
    mssgLog(t('delete not successful'), 'error');
  }
}

function contact_applydnc($listForm) {
  $filter = contact_filter(array('usr_id', 'contact_group_id'));
  $query = "DELETE c.* FROM contact_link cl
            JOIN (contact_dnc d  
            JOIN  contact c 
            ON c.phone = d.phone AND c.created_by = d.created_by) ON c.contact_id=cl.contact_id 
            WHERE $filter";
  $result = db_query('contact_link', $query, array());
  $total = mysql_affected_rows();
  $filter = contact_filter(array('contact_group_id'));
  $query = "DELETE cl.* FROM contact_link cl
            LEFT JOIN contact c 
            ON c.contact_id=cl.contact_id 
            WHERE c.contact_id IS NULL AND ($filter)";
  $result = db_query('contact_link', $query, array());
  if($total > 0) {
    mssgLog(t('@count contacts removed sucessfully', array('@count' => $total)), 'info');  
  }
  else {
    mssgLog(t('No contacts removed'), 'info');  
  }
}

function contact_search_form() {

  $form['search'] = array(
    '#type'          => 'fieldset',
    '#title'         => t('Filter Settings'),
    '#attributes'    => array('class' => 'inside')
  );
  $form['search']['contact_group_id'] = array(
    '#type'          => 'select',
    '#title'         => t('Contact Group'),
    '#options'       => getSelectData('contact_group', 'name', true, 0, true),
    '#default_value' => session_get('contact:filter:contact_group_id')
  );
  $form['search']['name'] = array(
    '#type'          => 'textfield',
    '#title'         => t('Name'),
    '#default_value' => session_get('contact:filter:name')
  );
  $form['search']['phone'] = array(
    '#type'          => 'textfield',
    '#title'         => t('Phone'),
    '#default_value' => session_get('contact:filter:phone')
  );
  /* $form['search']['email'] = array(
    '#type'          => 'textfield',
    '#title'         => t('E-Mail'),
    '#default_value' => session_get('contact:filter:email')
  ); */
  $form['search']['group_filter'] = array(
    '#type'          => 'select',
    '#title'         => t('Filter'),
    '#options'       => array(
      ''          => t('[No Filter]'),
      'error'     => t('[Invalid Contacts]'),
      'duplicate' => t('[Duplicate Contacts]')
    ),
    '#default_value' => session_get('contact:filter:group_filter')
  );
  $form['search']['buttons'] = array(
    '#type'          => 'group',
    '#title'         => '&nbsp;'
  );
  $form['search']['buttons']['filter_btn'] = array(
    '#type'          => 'submit',
    '#action'        => 'search',
    '#title'         => t('Search'),
    '#theme'         => 'empty'
  );
  $form['search']['buttons']['reset_btn'] = array(
    '#type'          => 'submit',
    '#action'        => 'reset',
    '#title'         => t('Reset'),
    '#theme'         => 'empty'
  );

  return $form;
}

function contact_search_submit($searchForm) {
  if (isset($searchForm['contact_group_id']) && !empty($searchForm['contact_group_id'])) {
    session_set('contact:filter:contact_group_id', $searchForm['contact_group_id']);
  }
  if (isset($searchForm['usr_id']) && !empty($searchForm['usr_id'])) {
    session_set('contact:filter:usr_id', $searchForm['usr_id']);
  }
  if (isset($searchForm['name']) && !empty($searchForm['name'])) {
    session_set('contact:filter:name', $searchForm['name']);
  }
  if (isset($searchForm['phone']) && !empty($searchForm['phone'])) {
    session_set('contact:filter:phone', $searchForm['phone']);
  }
  if (isset($searchForm['email']) && !empty($searchForm['email'])) {
    session_set('contact:filter:email', $searchForm['email']);
  }
  if (isset($searchForm['group_filter']) && !empty($searchForm['group_filter'])) {
    session_set('contact:filter:group_filter', $searchForm['group_filter']);
  }
}

function contact_filter($search = array()) {
  $filter = array();

  if (in_array('usr_id', $search)) {
    if (can_access('contact_admin', false)) {
      $usr_id = session_get('contact:filter:usr_id', -1);
    } else {
      // if not admin then force to use his own id
      $usr_id = session_get('user:usr_id');
    }
    if ($usr_id > 0) {
      $filter[] = 'c.created_by='.$usr_id;
    } else {
      $filter[] = 'c.created_by!=-1';
    }
  }

  if (in_array('contact_group_id', $search)) {
    $contact_group_id = session_get('contact:filter:contact_group_id', -1);
    if ($contact_group_id > 0) {
      $filter[] = 'cl.contact_group_id='.$contact_group_id;
    } else {
      $filter[] = 'cl.contact_group_id!=-1';
    }
  }

  if (in_array('name', $search)) {
    $name = session_get('contact:filter:name', '');
    if ($name != '') {
      $filter[] = "CONCAT(c.first_name, ' ', c.last_name) LIKE '%$name%'";
    }
  }

  if (in_array('phone', $search)) {
    $phone = session_get('contact:filter:phone', '');
    if ($phone != '') {
      $filter[] = "c.phone LIKE '%$phone%'";
    }
  }

  if (in_array('email', $search)) {
    $email = session_get('contact:filter:email', '');
    if ($email != '') {
      $filter[] = "c.email LIKE '%$email%'";
    }
  }

  if (in_array('group_filter', $search)) {
    $group_filter = session_get('contact:filter:group_filter', '');
    if ($group_filter == 'error') {
      $filter[] = "c.phone NOT REGEXP '^([0-9]+){1}$'";
    }
  }

  return implode(' AND ', $filter);
}

/**
 * function for contact List form
 *
 * It will show a list of available contacts in form of list
 *
 * @return
 *  An array of form element include list of of contacts in current page
*/
function contact_list($listForm) {

  $headers = array(
    array('data' => t('First Name'), 'field' => 'first_name'),
    array('data' => t('Last Name'),  'field' => 'last_name'),
    array('data' => t('Phone'),      'field' => 'phone'),
    array('data' => t('E-Mail'),     'field' => 'email'),
    array('data' => t('Operations'), 'align' => 'center'),
  );

  $filter = contact_filter(array('usr_id', 'contact_group_id', 'name', 'phone', 'group_filter'));
  $query  = "SELECT DISTINCT c.contact_id, c.first_name, c.last_name, c.phone, c.email, 
                             IF(ISNULL(c.last_updated), c.last_updated, c.date_created) AS date_created 
             FROM contact c LEFT JOIN contact_link cl 
                 ON c.contact_id = cl.contact_id
             WHERE $filter";  
  $listData  = getPagedData('contact', $query, array())
    or mssgLog(t('Unable to get contacts, query failed'), 'error');

  foreach($listData['data'] as $key => $value) {
    $primary_key = $value['contact_id'];
    $listData['data'][$key][] = $value['first_name'];
    $listData['data'][$key][] = $value['last_name'];
    $listData['data'][$key][] = $value['phone'];
    $listData['data'][$key][] = $value['email'];
    $listData['data'][$key][] = contact_list_links($primary_key);
  }

  $form['contact'] = array(
    '#type'          => 'fieldset',
    '#title'         => t('List of Contacts'),
    '#attributes'    => array('class' => 'noBorder')
  );
  $form['contact']['contactTable'] = array(
    '#type'          => 'tablelist',
    '#header'        => $headers,
    '#data'          => $listData,
    '#primary_key'   => 'contact_id'
  );
  if (can_access('contact_delete', false)) {
    $form['contact']['delete_btn'] = array(
      '#type'          => 'submit',
      '#title'         => t('Delete Selected'),
      '#confirm'       => t('Are you sure? you want to delete all selected records'),
      '#theme'         => 'empty',
      '#action'        => 'delete'
    );
  }

  return $form;
}

function contact_list_duplicate($listForm) {

  $headers = array(
    array('data' => t('First Name'), 'field' => 'first_name'),
    array('data' => t('Last Name'),  'field' => 'last_name'),
    array('data' => t('Phone'),      'field' => 'phone'),
    array('data' => t('E-Mail'),     'field' => 'email'),
    array('data' => t('Date'),       'field' => 'date_created', 'align' => 'center'),
  );

  // no filter should be implied expect usr_id while searching duplicate
    $filter = contact_filter(array('usr_id', 'contact_group_id', 'name', 'phone'));
    $query  = "SELECT c.contact_id, c.first_name, c.last_name, c.phone, c.email, 
                      IF(ISNULL(c.last_updated), c.last_updated, c.date_created) AS date_created 
               FROM contact c JOIN 
                 (SELECT c.phone, COUNT(c.contact_id) AS dcount 
                  FROM contact c LEFT JOIN contact_link cl 
                    ON c.contact_id = cl.contact_id
                  WHERE $filter
                  GROUP BY c.phone
                  HAVING dcount > 1
                 ) cinner
               ON c.phone = cinner.phone
               ORDER BY c.phone";
  $listData  = getPagedData('contact', $query, array())
    or mssgLog(t('Unable to get contacts, query failed'), 'error');

  foreach($listData['data'] as $key => $value) {
    $primary_key = $value['contact_id'];
    $listData['data'][$key][] = $value['first_name'];
    $listData['data'][$key][] = $value['last_name'];
    $listData['data'][$key][] = $value['phone'];
    $listData['data'][$key][] = $value['email'];
    $listData['data'][$key][] = date_style('medium', '', $value['date_created']);
  }

  $form['contact'] = array(
    '#type'          => 'fieldset',
    '#title'         => t('List of Contacts'),
    '#attributes'    => array('class' => 'noBorder')
  );
  $form['contact']['contactTable'] = array(
    '#type'          => 'tablelist',
    '#header'        => $headers,
    '#data'          => $listData,
    '#primary_key'   => 'contact_id'
  );
  if (can_access('contact_delete', false)) {
    $form['contact']['delete_btn'] = array(
      '#type'          => 'submit',
      '#title'         => t('Delete Selected'),
      '#confirm'       => t('Are you sure? you want to delete all selected records'),
      '#theme'         => 'empty',
      '#action'        => 'delete'
    );
    /* 
    $form['contact']['buttons'] = array(
      '#type'          => 'group',
      '#title'         => '&nbsp;'
    );
    $form['contact']['buttons']['delete_old'] = array(
      '#type'          => 'submit',
      '#title'         => 'Delete Duplicate (keep latest)',
      '#confirm'       => t('Are you sure? you want to delete all duplicate records'),
      '#theme'         => 'empty',
      '#action'        => 'delete_old'
    );
    $form['contact']['buttons']['delete_new'] = array(
      '#type'          => 'submit',
      '#title'         => 'Delete Duplicate (keep old)',
      '#confirm'       => t('Are you sure? you want to delete all duplicate records'),
      '#theme'         => 'empty',
      '#action'        => 'delete_new'
    ); */
  }

  return $form;
}

/**
 * Build the edit and delete links for a single contact.
 *
 * @see contact_list()
 */
function contact_list_links($primary_key) {
  $title       = t('[Edit]');
  $description = t('Edit Contact');

  $links = "<a title=\"$description\" href=\"contact.php?action=edit&contact_id=$primary_key\">$title<img src=\"images/22/action_edit1.gif\" /></a>";
  return $links;
}

/* following two function are not yet fainalized

function contact_email_validate($email, $contact_group_id) {
  if (email_validate($email)) {
    $contact_group = $contact_group_id ?  'AND contact_link.contact_group_id = %contact_group_id%' : '';
    $query = "SELECT COUNT(*) 
       FROM contact LEFT JION contact_link
        ON contact.contact_id = contact_link.contact_id 
       WHERE contact.email='%email%' $contact_group";
    $result= db_query('contact', $query, array('email' => $email, 'contact_group_id', $contact_group_id));
    if ($count = mysql_num_rows($result))
      return false;
    }
    return true;
  }
  return false;
}

function contact_check_duplicate($field, $value, $contact_group_id, $contact_id = 0) {
  if (variable_get('contact_' . $field . '_duplicate_group', 0) != 1) {
    return true;
  } else {
    $sql = "SELECT COUNT(contact.contact_id) AS total
      FROM contact JOIN contact_link 
        ON contact.contact_id = contact_link.contact_id
      WHERE contact.contact_id != %d
        AND contact_link.contact_group_id = %d
        AND contact.$field = '%s'";
    $result = db_query($sql, $contact_id, $contact_group_id, $value);
    $total  = db_result($result);
    if ($total > 0) {
      return false;
    }
  }
  return true;
}
*/

/**
 * Build the contact form for bulk import.
 *
 * @see contact_import_submit()
 */
function contact_import_form($editForm) {

  // javascript show/hide will allowe user to select between file or existing contact
  $js = "
$('#editForm-source_type').change(function() {
  if ($('#editForm-source_type').val() == 'file') {
    $('#source_file').show()
    $('#example').show()
    $('#source_group').hide()
  } else {
    $('#source_file').hide()
    $('#example').hide()
    $('#source_group').show()
  }
});";
  output_add_body_onload($js, 'inline', 'footer');

  $form['#attributes'] = array('enctype' => 'multipart/form-data');

  $form['selection'] = array(
    '#type'          => 'fieldset',
    '#title'         => t('Import Contacts From'),
    '#attributes'    => array('class' => 'inside')
  );
  $form['selection']['source_type'] = array(
    '#type'          => 'select',
    '#title'         => t('Type of Source'),
    '#options'       => array(
      'file'  => t('Upload contact via file'),
      'group' => t('Copy Contacts from existing Groups'),
    ),
  );
  $form['selection']['source_file'] = array(
    '#type'          => 'file',
    '#title'         => t("Upload Contact's File"),
  );
  $form['selection']['example'] = array(
    '#type'          => 'item',
    '#title'         => t('Example File'),
    '#default_value' => '<a href="contact_sample.csv">contact_sample.csv</a>'
  );
  $options = contactGroup_getSelect();
  $form['selection']['source_group'] = array(
    '#type'          => 'select',
    '#title'         => t('Source Groups'),
    '#options'       => $options,
    '#attributes'    => array('style' => 'display: none;'),
    '#multiple'      => true,
  );
  $form['into'] = array(
    '#type'          => 'fieldset',
    '#title'         => t('Import Contacts Into'),
    '#attributes'    => array('class' => 'inside')
  );
  $form['into']['target_group'] = array(
    '#type'          => 'select',
    '#title'         => t('Target Group'),
    '#options'       => $options,
  );

  return $form;
}

/**
 * Build the contact form for bulk import.
 *
 * @see contact_import_form()
 */
function contact_import_submit($editForm) {

  // Ensure we have the file uploaded
  if ($editForm['source_type'] == 'file') {
    $file = input_get('source_file', null);
    $total_rows = contact_import_file($file, $editForm['target_group']);
  } else {
    $total_rows = contact_import_group($editForm['source_group'], $editForm['target_group']);
  }
  if ($total_rows > 0) {
    mssgLog(t('%count Contacts imported successfully!', array('%count' => $total_rows)), 'info');
    return $total_rows;
  } else {
    mssgLog(t('Unable import, Unknown error!'), 'error');
    return false;
  }
}

/**
 * Import contact from existing groups
 *
 * @see contact_import_submit()
 */
function contact_import_group($src_groups, $trg_group) {
  $selected_groups = implode(',', $src_groups);
  $query           = "INSERT INTO contact_link (contact_id, contact_group_id)
                         SELECT cl.contact_id, $trg_group AS contact_group_id
                           FROM contact_link cl
                         WHERE cl.contact_group_id IN ($selected_groups)";
  $result          = db_query('contact_link', $query);

  return mysql_affected_rows();
}

/**
 * Import contact from uploaded file
 *
 * @see contact_import_submit()
 */
function contact_import_file($src_file, $trg_group) {
    $usr_id     = session_get('user:usr_id');
    $ext        = end(explode('.', $src_file['name']));
    $total_rows = 0;

    // Select whether we are using xls file or CSV
    if (strtolower($ext) == 'xls') {
        // in case of xls file first convert it to csv
        $file_name = tempnam('', 'phone-list-');
        $command = sys_which('xlhtml') . " -csv -xp:0 ".$src_file['tmp_name']." > $file_name";
        exec($command);
    } else {
        $file_name = $src_file['tmp_name'];
    }

    $csv_columns = array(
      'phone'       => '', 
      'first_name'  => 'Mr.',
      'last_name'   => '', 
      'email'       => '', 
      'address'     => '', 
      'custom1'     => '',
      'custom2'     => '', 
      'custom3'     => '', 
      'description' => ''
    );

    exec("dos2unix '$file_name'");

    $handle  = fopen($file_name, "r");
    // get a sample row
    if (($data = fgetcsv($handle, 500, ",")) !== false) {
      // remove extra columns from csv_columns
      array_splice($csv_columns, count($data));
    }
    fclose($handle);

    $sql_columns = implode(', ', array_keys($csv_columns));

    $query = "LOAD DATA LOW_PRIORITY LOCAL INFILE '$file_name'
                IGNORE INTO TABLE contact
                FIELDS
                    TERMINATED BY ','
                    OPTIONALLY ENCLOSED BY '\"'
                LINES
                    TERMINATED BY '\\n'
                ($sql_columns)
                SET last_updated = $trg_group,
                    created_by = $usr_id
            ";
    $result     = mysql_query($query);
    $total_rows = mysql_affected_rows();

    return $total_rows;
}


/**
 * Build the contact form for bulk import.
 *
 * @see contact_salesforce_submit()
 */
function salesforce_contact_form($editForm) {

  // javascript show/hide will allowe user to select between file or existing contact
  $js = "$(document).ready(function() {

    $('#button3434').hide();

   // alert(3232);

    });
$('#editForm-token').change(function() {
      get_value();        
});
$('#editForm-username').change(function() {
      get_value();        
});
$('#editForm-password').change(function() {
      get_value();        
});
function get_value(){

  //alert($('#editForm-token').val());
  // alert($('#editForm-username').val());
  // alert($('#editForm-password').val());

  var token = $('#editForm-token').val();
  var username = $('#editForm-username').val();
  var password = $('#editForm-password').val();

  if(token!='' && username!='' && password!='' ){
    $('#editForm-hide_field').val("."username+'_'+password+'_'+token".") ;
    $('#button3434').show();
  }else{
    $('#button3434').hide();
  }
}";
/**/
  /**/
  output_add_body_onload($js, 'inline', 'footer');

   $editForm += array(
    'contact_id'           => '',
    'hide_field'           => '',
    'username'             => '',
    'password'             => '',
    'token'                => '',
    'salesforce_campaign'  => '',
    'email'                => '',
    'address'              => '',
    'custom1'              => '',
    'custom2'              => '',
    'custom3'              => '',
    'description'          => '',
    'contact_group_id'     => array(''),
    'button'               => '',
  );

  $form['salesforce'] = array(
    '#type'          => 'fieldset',
    '#title'         => t('Salesforce'),
    '#attributes'    => array('class' => 'inside')
  );
  $form['salesforce']['contact_id'] = array(
    '#type'          => 'hidden',
    '#default_value' => $editForm['contact_id'],
  );
  $form['salesforce']['hide_field'] = array(
    '#type'          => 'hidden',
    '#script'        => 'ajax',
    '#default_value' => $editForm['hide_field'],
  );
  $form['salesforce']['username'] = array(
    '#type'          => 'textfield',
    '#title'         => t('User Name'),
   // '#script'        => 'ajax',
    '#default_value' => $editForm['username'],
  );
  $form['salesforce']['password'] = array(
    '#type'          => 'textfield',
    '#title'         => t('Password'),
    //'#script'        => 'ajax',
    '#default_value' => $editForm['password'],
  );
  $form['salesforce']['token'] = array(
    '#type'          => 'textarea',
    '#title'         => t('Security Token'),
    '#script'        => 'ajax',
    '#default_value' => $editForm['token'],
  );
   $form['salesforce']['button'] = array(
    '#type'          => 'button',
    '#title'         => t('Get SalesForce Campaign'),
    '#script'        => "ajax_call('contact.php','salesforce_campaign','#editForm-hide_field')",
    '#attributes'    => array('id' => 'button3434'),
    //'#default_value' => $editForm['button'],
  );
  $form['group'] = array(
    '#type'          => 'fieldset',
    '#title'         => t('Groups'),
    '#attributes'    => array('class' => 'inside')
  );
  // get list of contact_group
 
  $form['group']['salesforce_campaign'] = array(
    '#type'          => 'select',
    '#title'         => t('Salesforece Campaigns'),
    '#options'       => '',
    '#default_value' => $editForm['salesforce_campaign'],
    //'#multiple'      => true,
  );
   $options = contactGroup_getSelect();
  $form['group']['contact_group_id'] = array(
    '#type'          => 'select',
    '#title'         => t('Related Groups'),
    '#options'       => $options,
    '#default_value' => $editForm['contact_group_id'],
    '#multiple'      => true,
  );
  $form['custom'] = array(
    '#type'          => 'fieldset',
    '#title'         => t('Custom Data'),
    '#attributes'    => array('class' => 'inside')
  );
  $form['custom']['custom1'] = array(
    '#type'          => 'textfield',
    '#title'         => t('Custom Value #1'),
    '#default_value' => $editForm['custom1'],
  );
  $form['custom']['custom2'] = array(
    '#type'          => 'textfield',
    '#title'         => t('Custom Value #2'),
    '#default_value' => $editForm['custom2'],
  );
  $form['custom']['custom3'] = array(
    '#type'          => 'textfield',
    '#title'         => t('Custom Value #3'),
    '#default_value' => $editForm['custom3'],
  );
  $form['remark'] = array(
    '#type'          => 'fieldset',
    '#title'         => t('Comments / Remarks'),
    '#attributes'    => array('class' => 'inside')
  );
  $form['remark']['description'] = array(
    '#type'          => 'textarea',
    '#title'         => t('Description'),
    '#default_value' => $editForm['description'],
  );

  return $form;
}
/**
 * Build the contact get from  salesforec.
 *
 * @see salesforce_contact_form()
 */
function contact_salesforce_submit($editForm)
{
    //echo "<pre>";print_r($editForm);
    // echo $editForm['username']. $editForm['password']. $editForm['token'];
    $campaign_members = get_salesforce_campaign_members($editForm['username'],$editForm['password'],$editForm['token'],$editForm['salesforce_campaign']);
    $campaign_records = $campaign_members->records;
    $sales_contanct = array();
    $editForm2 = array();

  foreach($campaign_records as $campaign_contact){
   $sales_contanct[] =  $campaign_contact->Contact;
  }

  //echo "<pre>";print_r($sales_contanct);

  foreach($sales_contanct as $addcontact){
    if($addcontact->Phone!=''){
      $name =array('first_name'=>$addcontact->FirstName,'last_name'=>$addcontact->LastName,'phone'=>$addcontact->Phone,'address'=>$addcontact->OtherCity.' '.$addcontact->OtherCountry,'email'=>$addcontact->Email);
      $editForm1 = $editForm + $name;
      $editForm2[] = $editForm +$name;
      $result = db_update('contact', $editForm1, false, true);
      $editForm['contact_id'] = mysql_insert_id();
      $error[] = mysql_error();
      // TODO: add auth filter
      foreach ($editForm['contact_group_id'] as $contact_group_id) {
        // create link between recently created content and selected groups
        $query  = "INSERT INTO contact_link (contact_id, contact_group_id) VALUES (%contact_id%, %contact_group_id%)";
        $result = db_query('contact', $query, array('contact_id'       => $editForm['contact_id'],
                                                 'contact_group_id' => $contact_group_id
                                                ), true);
        $editForm['contact_id'] = '';
      }
      $editForm['contact_id']='';
    }
  }

   if ($result) {
    mssgLog(t('Contact saved successfully'), 'info');
  } else {
    mssgLog(t('Unable to save contact'), 'error');
  }
  return $result;
}
function get_salesforce_campaign_members($username,$password,$token,$campaign_id) 
{
  try {

      $mySforceConnection = new SforceEnterpriseClient();
      $mySforceConnection->createConnection("soapclient/enterprise.wsdl.xml");
      $mySforceConnection->login($username, $password. $token);
  
      $mySforceConnection->getLocation();
      $mySforceConnection->getSessionId();
      //echo "Logged in with enterprise<br/><br/>\n";
      //$query1 = "SELECT Id, FirstName, LastName, Phone from Contact";
      $query1 = "SELECT Campaign.Name,ContactId, Contact.FirstName, Contact.LastName, Contact.Phone,Contact.Email,Contact.OtherCity,Contact.OtherCountry, LeadId, Lead.FirstName, Lead.LastName, Lead.Phone FROM CampaignMember WHERE CampaignId='$campaign_id'";

      $response1 = $mySforceConnection->query($query1);
     // echo "eee<pre>";print_r($response1->records);
      return $response1;
     // return $response1->records->contact;
  }catch (Exception $e) {
    echo "Exception ".$e->faultstring."<br/><br/>\n";
   /* echo "Last Request:<br/><br/>\n";
    echo $mySforceConnection->getLastRequestHeaders();
    echo "<br/><br/>\n";
    echo $mySforceConnection->getLastRequest();
    echo "<br/><br/>\n";
    echo "Last Response:<br/><br/>\n";
    echo $mySforceConnection->getLastResponseHeaders();
    echo "<br/><br/>\n";
    echo $mySforceConnection->getLastResponse();*/
  }
}

function contact_export() {
  $filter = contact_filter(array('usr_id', 'contact_group_id', 'name', 'phone', 'group_filter'));
  $query  = "SELECT DISTINCT c.* FROM contact c
                 LEFT JOIN contact_link cl 
                   ON c.contact_id = cl.contact_id
               WHERE $filter";
  $result    = db_query('contact', $query, array(), true);  
  
  //Begin writing headers
  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Cache-Control: public");
  header("Content-Description: CSV Download");

  //Use the switch-generated Content-Type
  header("Content-Type: text/csv");

  //Force the download
  $header="Content-Disposition: attachment; filename=contact.csv;";
  header($header);
  header("Content-Transfer-Encoding: binary");

  // headers
  //echo "Prefix,contact_dnc Name\n";

  // data
  while ($aValue = mysql_fetch_assoc($result)) {
    echo $aValue['phone'].','.$aValue['first_name'].','.$aValue['last_name'].','.$aValue['email'].','.$aValue['address'].','.$aValue['custom1'].','.$aValue['custom2'].','.$aValue['custom3'].','.$aValue['description']."\n";
  }
  exit();
} 

/**
 * menu function
 */
function contact_topMenu($aSelected) {
  static $aMenu;

  if (!isset($aMenu)) {
    $aMenu = contact_menu();
  }

  $out_menu = '';
  foreach ($aSelected as $selected) {
    $cur_menu  = $aMenu[$selected];
    $out_menu .= "    <a class=\"$cur_menu[class]\" title=\"$cur_menu[description]\" href=\"$cur_menu[href]\">$cur_menu[title]</a>\n";
  }
  return $out_menu;
}

output_set('menu', contact_menu());
output_set('url', 'contact.php');
contact_navigate();

include "header.php";
include "footer.php";
?>
