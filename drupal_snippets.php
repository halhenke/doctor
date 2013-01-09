
//==============================================================
/* Using node hook stuff */
/* All this stuff abandoned eventually - felt it was cleaner to use the 
   hook_info()
   hook_form()
   hook_submit() with a save_node call
   to manage all this rather than to invoke the node_hook system.
   However this would provide the ability to automatically update nodes easier i believe
*/
//==============================================================


function admin_note_insert($node){
  db_query("INSERT INTO {admin_note} (nid, vid, user_name) VALUES (%d, %d, %s)", $node->nid, $node->vid, $node->user_name);
}

function admin_note_update($node){
  if (node->revision) {
    admin_note_insert($node);
  }
  else {
    db_query("UPDATE {admin_note} SET user_name= '%s' WHERE vid =  %d", $node->user_name, $node->vid);
  }
}

function admin_note_delete(&$node){
      db_query("DELETE FROM {admin_note} WHERE nid =  %d", $node->nid);
}

function admin_note_load($node){
  return db_fetch_object(db_query("SELECT user_name FROM {admin_note} WHERE vid = %d", $node->vid));
}

function admin_note_view($node, $teaser = FALSE, $page = FALSE){
  /* if (!$teaser) { */
    $node = node_prepare($node, $teaser);
    $node->content['user_name'] = array(
				   '#value' => theme('admin_note_user_name', $node),
				   '#weight' => 2
				   );
  /* } */
  /* if ($teaser) { */
  /*   $node = node_prepare($node, $teaser); */
  /* } */
}

/* function admin_note_theme(){ */
/*   return array( */
/* 	       'admin_note_user' => array( */
/* 					  'arguments' => array('node'),),); */
/* } */

/* function theme_admin_note_user($node) { */
/*   $output = '<b>' . check_markup($node->user_name) . '</b>'; */
/*   return $output; */
/* } */

//==============================================================
/* Attempting to stop node from being shown in the Admin Menu or being loaded/viewed */
//==============================================================

// Aborted attempt to solve via calling a different template file for this one page

function phptemplate_preprocess_page(&vars)
function comment_node_block_preprocess(&vars)
{
  if (current_path() == 'admin/reports/page')
    {
      $template_name = 'hal_report_template';
      $vars['template_files'][] = $template_name;
    }
    /* var_dump($vars); //output: nothings that reference the views! */
    /* if($_GET['q'] == 'my/view/path') */
    /* { */
    /*     drupal_add_js([...]); */
    /*     drupal_add_css([...]); */
    /* } */
}

// This works for someone trying to directly access a url like '/node/15' but
// wont prevent someone from viewing or editing a node from the admin/content menu
function comment_node_block_preprocess(&$vars, $hook) {
  global $user;
  if (($vars['node']->type == 'admin_note') && !in_array('administrator', $user->roles)) {
    drupal_goto(drupal_urlencode('admin/reports/updates'));
  }
}

// ***
// Cannot redirect the user at this stage - headers have been loaded...
// ***


function admin_note_prepare(&$node) {
  drupal_goto(drupal_urlencode('admin/reports/page'));
}

function admin_note_load($nodes) {
  drupal_goto(drupal_urlencode('admin/reports/page'));
}

function admin_note_delete($nodes) {
  drupal_goto(drupal_urlencode('admin/reports/page'));
}

function admin_note_edit($nodes) {
  drupal_goto(drupal_urlencode('admin/reports/page'));
}

function admin_note_view($nodes) {
  drupal_goto(drupal_urlencode('admin/reports/page'));
}

function admin_note_menu() {
  $items['admin/content/node'] =array();
  $items['node/add/admin-note'] =array(); 
  return $items;
}

/* Can remove entries from the admin and content create menus but cant remove stuff 
   from the content section ofthe admin section etc  */
/* I also use this function to put blocks at the top of the content region when this is not otherwise enabled */
function comment_node_block_menu_alter (&$items) {
  $items['admin/reports/updates']['page callback'] = 'reverse_content';        
  /* $items['admin/reports/updates']['file'] => 'comment_node_block.module', */
  /* $items['admin/content/types'] =array(); */
  /* $items['node/add/admin-note'] =array(); */

  $items['node/add/admin-note']['access callback'] = FALSE;

  /* $items['admin/content/node']['access callback'] = FALSE; */
}


  //node_show only seems to get the body of the node and not the title:
  /* $output = node_show($temp, NULL);  //second parameter is the first comment i believe */

//==============================================================
/* STUFF I WROTE */
//==============================================================
/* Helper functions */
//==============================================================

function get_all_nodes_of_type($node_type) {
  $result = db_query("SELECT nid FROM node WHERE type = '%s' ", $node_type);
  $nids = array();
  while ($obj = db_fetch_object ($result)) {
    $nids[] = $obj->nid;
  }
  return $nids;
}

function delete_all_nodes_of_type($node_type) {
  $nodes = get_all_nodes_of_type($node_type);
  foreach ($nodes as $node) {
    node_delete($node);
  }
}

/* This is a bit unnnecessary - use delete_blocks_from_module($module)
  - actually disregard that - i dont know what i was talking about with that one...*/
function delete_blocks_from_module($block_module) {
  $result = db_query("DELETE FROM blocks WHERE module = '%s' ", $block_module);
}
