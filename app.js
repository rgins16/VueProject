// sign in component
var signIn = {
  name: 'signIn',
  template: '#sign-in-template',
  // initialize variables
  data() {
    return {
      username: '',
      password: '',
      alerts: [],
      loading: false
    }
  },
  methods: {

    // sign in
    signIn: function(){

      // reset the alerts
      this.alerts = [];

      // check if the username or password are empty
      if(this.username === '') this.alerts.push('please specifiy a username');
      if(this.password === '') this.alerts.push('please specifiy a password');
    
      // if the username or password is empty, then return
      if(this.alerts.length !== 0) return;

      // disable the button and show the spinner
      this.loading = true;

      // this is so we can access variable in the ajax callback
      var self = this;

      // use ajax to call the sign in function
      $.post(localized.ajaxurl + 'signIn', {
        // pass the username, password, and nonce
        username: this.username,
        password: this.password,
        nonce: localized.signInNonce
      }, function(response) {
        // the user was successfully logged in, refresh the page
        if(response.success) window.location.href = localized.siteurl;

        /*
        * I could instead do the following to navigate RESTfully:
        * router.replace('contactsList');
        * but in this case, the toolbar at the top of the page with the log out button does not appear.
        * It only appears when the page is refreshed. This is a wordpress problem. In a traditional web app,
        * I would just add a logout button.
        */

        // otherwise...
        else{
          // hide the spinner and re-enable the button
          self.loading = false;
          // show alerts from the back-end
          self.alerts.push(response.errors[0]);
        }
      }, 'json');
    }
  }
};


// regiter component
var register = {
  name: 'register',
  template: '#register-template',
  // initiaize variables
  data() {
    return {
      username: '',
      email: '',
      firstName: '',
      lastName: '',
      password: '',
      confirmPassword: '',
      alerts: [],
      loading: false,
    }
  },
  methods: {

    // register the user
    registerUser: function(e){

      // prevent the natural action when a form is submitted
      e.preventDefault();

      this.alerts = [];

      // check if any fields are empty or the password don't match
      if(this.username === '') this.alerts.push('please specifiy a username');
      if(this.email === '') this.alerts.push('please specifiy an email');
      if(this.firstName === '') this.alerts.push('please specifiy a first name');
      if(this.lastName === '') this.alerts.push('please specifiy a last name');
      if(this.password === '') this.alerts.push('please specifiy a password');
      if(this.confirmPassword === '') this.alerts.push('please confirm your password');
      if(this.password !== this.confirmPassword) this.alerts.push('passwords do not match');
    
      // if are any errors, then return
      if(this.alerts.length !== 0) return;

      // disable the button and show the spinner
      this.loading = true;

      // this is so we can access variables in the ajax callback
      var self = this;

      // use ajax to call the register function
      $.post(localized.ajaxurl + 'registration', {
        // pass the username, email, first name, last name, password, confirm password, and nonce
        username: this.username,
        email: this.email,
        firstName: this.firstName,
        lastName: this.lastName,
        password: this.password,
        confirmPassword: this.confirmPassword,
        nonce: localized.registerNonce
      }, function(response){
        // if the user successfully registered, refresh the page
        if(response.success) window.location.href = localized.siteurl;

        /*
        * I could instead do the following to navigate RESTfully:
        * router.replace('contactsList');
        * but in this case, the toolbar at the top of the page with the log out button does not appear.
        * It only appears when the page is refreshed. This is a wordpress problem. In a traditional web app,
        * I would just add a logout button.
        */
        
        // otherwise...
        else{
          // hide the spinner and re-enable the button
          self.loading = false;
          // show any errors from the backend
          self.alerts.push(response.errors[0]);
        }
      }, 'json');
    },
  }
};


// contacts list component
var contactsList = {
  name: 'contactsList',
  template: '#contacts-list-template',
  // initialize variables
  data() {
    return {
      contacts: [],
      viewContact: '',
      updateContactVar: '',
      firstName: '',
      lastName: '',
      email: '',
    }
  },
  // when this component has loaded
  mounted () {

    // this is so we can access variables in the ajax callback
    var self = this;

    // get the user's contacts
    // ************* couldn't figure out how to make this a method and call it from the other methods *************
    $.post(localized.ajaxurl + 'getContacts', function(response){
      self.contacts = response.contacts;
    }, 'json');
  },
  methods: {

    // add contact
    addContact: function(){

      // this is so we can access variables in the ajax callbacks
      var self = this;

      // use ajax to add the contact
      $.post(localized.ajaxurl + 'addContact', {
        // pass email, first anem, and last name
        email: this.email,
        firstName: this.firstName,
        lastName: this.lastName,
      }, function(response){

        // get the user's contacts now that a contact has been added
        $.post(localized.ajaxurl + 'getContacts', function(response){

          self.contacts = response.contacts;

          // hide the add contact modal
          $('#addContactModal').modal('hide');
        }, 'json');
      }, 'json');
    },

    // update contact
    updateContact: function(postID){

      // this is so we can access variables in the ajax callback
      var self = this;

      $.post(localized.ajaxurl + 'updateContact', {
        // pass the post id, email, first name, and last name
        postID: postID,
        email: this.updateContactVar.email,
        firstName: this.updateContactVar.firstName,
        lastName: this.updateContactVar.lastName,
      }, function(response){

        // use ajax to get the user's contacts now that a contac has been updated
        $.post(localized.ajaxurl + 'getContacts', function(response){

          self.contacts = response.contacts;

          // hide the update contact modal
          $('#updateContactModal').modal('hide');
        }, 'json');
      }, 'json');
    },

    // delete contact
    deleteContact: function(postID){

      // this is so we can access variable in the ajax callback
      var self = this;

      // confirm that the user wants to delete the contact
      if(confirm("Are you sure you want to delete this contact?")) {

        // use ajax to delete the contact
        $.post(localized.ajaxurl + 'deleteContact', {
          // pass the post id
          postID: postID,
        }, function(response){

          // use ajax to get the user's contacts now that a contact has been deleted
          $.post(localized.ajaxurl + 'getContacts', function(response){
            self.contacts = response.contacts;
          }, 'json');
        }, 'json');
      }
    }
  }
};


// the router that will be used to naviagte RESTfully between templates
var router = new VueRouter({
  // put a hash after the url
  mode: 'hash',
  // the base url is the regular url 
  base: window.location.href,
  // define the routes
  // the paths are associated with a component
  // the paths are used in this file and index.html like this: router.replace('signIn');
  routes: [
    {path: '/signIn', component: signIn},
    {path: '/register', component: register},
    {path: '/contactsList', component: contactsList},
  ]
});


// initialize new VUE instance
new Vue({

  // put this VUE instance on the 'app' element
  el: '#app',

  // initialize the router
  router: router,

  // when this instance has loaded
  mounted () {

    // use ajax to check if a user is already logged in
    $.post(localized.ajaxurl + 'checkLoggedIn', function(response){
      // if so, then tell the router to navifate RESTfully to the contacts list template
      if(response.userLoggedIn) router.replace('contactsList');
      // if not, then tell the component to navigate RESTfully to the sign template
      else router.replace('signIn');
    }, 'json');
  }
});