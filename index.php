<!DOCTYPE html>
<html>
<head>
  <title>Vue Project</title>
  
  <?php

    function wpdocs_theme_name_scripts() {
      // include all css
      wp_enqueue_style( 'bootstrap_css', get_template_directory_uri() . '/node_modules/bootstrap/dist/css/bootstrap.min.css' );
      wp_enqueue_style( 'style_css', get_template_directory_uri() . '/style.css' );

      // include all scripts
      wp_enqueue_script( 'vue_js', get_template_directory_uri() . '/node_modules/vue/dist/vue.js', array(), null, true );
      wp_enqueue_script( 'vue-resource_js', get_template_directory_uri() . '/node_modules/vue-resource/dist/vue-resource.js', array(), null, true );
      wp_enqueue_script( 'vue-router_js', get_template_directory_uri() . '/node_modules/vue-router/dist/vue-router.js', array(), null, true );
      wp_enqueue_script( 'vue-simple-spinner_js', get_template_directory_uri() . '/node_modules/vue-simple-spinner/dist/vue-simple-spinner.js', array(), null, true );
      wp_enqueue_script( 'jquery_js', get_template_directory_uri() . '/node_modules/jquery/dist/jquery.min.js', array(), null, false );
      wp_enqueue_script( 'bootstrap_js', get_template_directory_uri() . '/node_modules/bootstrap/dist/js/bootstrap.min.js' );
      wp_enqueue_script( 'app_js', get_template_directory_uri() . '/app.js', array(), null, true );
      
      wp_localize_script('jquery_js', 'localized', array(
        // set the ajax url
        'ajaxurl' => admin_url( 'admin-ajax.php' ) . '?action=',
        // set the siteurl
        'siteurl' => get_site_url(),
        // create a sign in nonce
        'signInNonce' => wp_create_nonce( 'signIn'),
        // create a registration nonce
        'registerNonce' => wp_create_nonce( 'registration')
      ));
    }
    add_action( 'wp_enqueue_scripts', 'wpdocs_theme_name_scripts' );

    include 'signIn.html';
    include 'register.html';
    include 'contactsList.html';

    wp_head();
  ?>
</head>

<body>

  <div id="app">

    <!-- the router which will navigate RESTfully between all templates -->
    <router-view class="view"></router-view>
  </div>
<?php wp_footer() ?>
</body>
</html>