<?php

// upon page load, checks if a user is logged in
add_action( 'wp_ajax_checkLoggedIn', 'checkLoggedIn' );
add_action( 'wp_ajax_nopriv_checkLoggedIn', 'checkLoggedIn' );
function checkLoggedIn() {

  $data = array();

  if(is_user_logged_in()) $data['userLoggedIn'] = true;
  else $data['userLoggedIn'] = false;
  
  echo json_encode($data);
  wp_die();
}



// get the user's contacts
add_action( 'wp_ajax_getContacts', 'getContacts' );
function getContacts() {

  $data = array();

  // get current user
  $current_user = wp_get_current_user();

  // search for the user's contacts by the user's ID
  $args = array(
    'post_type' => 'user_contacts',
    'author' => $current_user->ID
  );
  $query = new WP_Query( $args );

  // if the user has contacts
  if( $query->have_posts() ) {
    // loop through them
    while( $query->have_posts() ) {
      // iterate through each contact
      $query->the_post();

      // store each contact locally 
      $contacts[] = array(
       'id' => get_the_ID(),
       'firstName' => get_field('first_name'),
       'lastName' => get_field('last_name'),
       'email' => get_field('email'),
       // gets the date created based on the date the post was created
       'dateCreated' => get_the_date('F j, Y', get_the_ID())
     );
    }

    wp_reset_postdata();
    $data['contacts'] = $contacts;
  }
  else{
    $data['contacts'] = array();
  }
  
  // send the contacts to VUE
  echo json_encode($data);
  wp_die();
}



// save a contact the user has submitted
add_action( 'wp_ajax_addContact', 'addContact' );
function addContact() {

  $data = array();
  $data['success'] = true;

  // sanitize all fields
  $email = sanitize_text_field($_POST['email']);
  $firstName = sanitize_text_field($_POST['firstName']);
  $lastName = sanitize_text_field($_POST['lastName']);

  // insert a space for the new contact in user_contacts
  $my_post = array(
    'post_type' => 'user_contacts',
  );
  $the_post_id = wp_insert_post( $my_post );

  // add meta data to the contact
  add_post_meta( $the_post_id, 'email', $email );
  add_post_meta( $the_post_id, 'first_name', $firstName );
  add_post_meta( $the_post_id, 'last_name', $lastName );

  // send confirmation back to VUE
  echo json_encode($data);
  wp_die();
}



// update a user's contact
add_action( 'wp_ajax_updateContact', 'updateContact' );
function updateContact() {

  $data = array();
  $data['success'] = true;

  // sanitize all fields
  $postID = sanitize_text_field($_POST['postID']);
  $email = sanitize_text_field($_POST['email']);
  $firstName = sanitize_text_field($_POST['firstName']);
  $lastName = sanitize_text_field($_POST['lastName']);

  // update the meta data for each field
  update_post_meta( $postID, 'email', $email );
  update_post_meta( $postID, 'first_name', $firstName );
  update_post_meta( $postID, 'last_name', $lastName );

  // send confirmation back to VUE
  echo json_encode($data);
  wp_die();
}



// delete a contact the user has specified
add_action( 'wp_ajax_deleteContact', 'deleteContact' );
function deleteContact() {
  $data = array();
  $data['success'] = true;

  // sanitize the field
  $postID = sanitize_text_field($_POST['postID']);

  // delete the contact by its ID
  wp_delete_post($postID, true);

  // send confirmation back to VUE
  echo json_encode($data);
  wp_die();
}



// user registration
add_action( 'wp_ajax_registration', 'registration' );
add_action( 'wp_ajax_nopriv_registration', 'registration' );
function registration() {
  
  // array to hold validation errors
  $errors = array();
  // array to pass back data
  $data = array();
  // array to insert into the database
  $userData = array();
  
  // sanitize all fields
  $username = sanitize_text_field($_POST['username']);
  $email = sanitize_text_field($_POST['email']);
  $firstName = sanitize_text_field($_POST['firstName']);
  $lastName = sanitize_text_field($_POST['lastName']);
  $password = sanitize_text_field($_POST['password']);
  $confirmPassword = sanitize_text_field($_POST['confirmPassword']);

  // put info about eh new user into an array which will be inserted in the DB
  $userData['user_login'] = $username;
  $userData['user_pass'] = $password;
  $userData['user_email'] = $email;
  $userData['first_name'] = $firstName;
  $userData['last_name'] = $lastName;
  
  // back-end validation, even though there is front-end validation
  if(empty($username)) $errors['emptyUsername'] = "please enter a username";
  if(empty($email)) $errors['emptyEmail'] = "please enter an email";
  if(empty($firstName)) $errors['emptyFirstName'] = "please enter a first name";
  if(empty($lastName)) $errors['emptyLastName'] = "please enter a last name";
  if($password !== $confirmPassword) $errors['mismatchedPasswords'] = "passwords do not match";

  // verify that the nonce is correct
  if(!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'registration'))
    $errors[] = "nonce field could not be verified";

  // check to see if the username is already in use
  if(username_exists($username))
    $errors[] = "that username is already in use";

  // check to see if the email is already in use
  if(email_exists($email))
    $errors[] = "that email is already in use";
  
  // if there were no errors...
  if(empty($errors)){

    // insert the user into the DB
    $user_id = wp_insert_user($userData);
    
    // checks to see if there was an error inserting the user into the DB
    if(is_wp_error($user_id))
      $errors['err'] = str_replace('ERROR: ', '', strip_tags($user_id->get_error_message()));
  }   
  
  // if there were errors...
  if (!empty($errors)) {
    // return those errors
    $data['success'] = false;
    $data['errors']  = $errors;
  }
  // else if there are no errors...
  else {
    
    $data['output'] = "User Registration Successful";
    $data['success'] = true;
  
    // prepare to perform a sign on for the new user based on their username and password
    $credentials = array();
    $credentials['user_login'] = $username;
    $credentials['user_password'] = $password;

    // sign out any user that may already be signed in
    wp_logout();

    //perform a sign in for the new user
    wp_signon($credentials); //log the user in
  }
  
  // return confirmation/failure and any errors back to VUE
  echo json_encode($data);
  wp_die();
}



// sign in 
add_action( 'wp_ajax_signIn', 'signIn' );
add_action( 'wp_ajax_nopriv_signIn', 'signIn' );
function signIn() {
  
  // array to hold validation errors  
  $errors = array();
  // array to pass back data
  $data = array();
  
  // prepare to sign in the user with their username and password
  $credentials['user_login'] = $_POST['username'];
  $credentials['user_password'] = $_POST['password'];
  
  // check to see if the username or password are empty
  if(empty($_POST['username']))
    $errors[] = "Please enter a valid username or email";
  if (empty($_POST['password']))
    $errors[] = "Please enter a valid password";
  
  // verify that the nonce is correct
  if(!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'signIn'))
    $errors[] = "nonce field could not be verified";

  $username = $_POST['username'];
  $password = $_POST['password'];
  
  // if there weren't any errors
  if(empty($errors)) {
    
    // authenticate the user
    $user = wp_authenticate($username, $password);

    // checks to see if there was an error authenticating the user
    if(is_wp_error($user))
      $errors[] = str_replace('ERROR: ', '', strip_tags($user->get_error_message()));
  }
  
  // if there are still no errors
  if(empty($errors)) {
    // perform a sign in to the user
    $user = wp_signon($credentials);

    // checks to see if there was an error performing the sign in
    if(is_wp_error($user))
      $errors[] = 'error while logging in';
  }
  
  // if there are errors...
  if(!empty($errors)){
    $data['success'] = false;
    $data['errors']  = $errors;
  }
  // else if there are no errors
  else{
    $data['output'] = 'login successful';
    $data['success'] = true;
  }

  // return confirmation/failure and any errors back to VUE
  echo json_encode($data);
  wp_die();
}



?>