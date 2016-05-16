<?php

/*
Plugin Name: User register (Opdracht Acato)
Plugin URI: http://acato.nl
Description: Klein opdracht van Acato. Registratie formulier. Gebruik de shortcode [user-register] om de form genereren op een post.
Author: Jorge Fernandez
Version: 1.0
Author URI: http://acato.nl
*/

class UserRegisterPlugin {
    
    /**
     * The path for all html templates
     * 
     * @var string 
     */
    public $view_path;        
    
    /**
     * This prefix would be assigned to each meta-box-data field
     * 
     * @var string
     */
    public $prefix = 'urp_';
    
    /**
     * Is used to keep the message for the user is email already exists
     * 
     * @var string
     */
    public $message = '';
     
    /**
     * construct
     * 
     */
    public function __construct() {
       
        $this->view_path = dirname(__FILE__) . '/views/'; 
        
        add_action('init', [$this, 'create_post_type_user']);
        add_action('add_meta_boxes', [$this, 'add_user_meta_box']);
        add_action('save_post', [$this, 'save_meta_data']);
        add_action('init', [$this, 'register_shortcode']);
    }    
   
    /**
     * Register post type User
     * 
     * @return void
     */
    public function create_post_type_user() {
        
        $labels = array(
            'name' => 'User',
            'singular_name' => 'Gebruiker',
            'plural_name' => 'Gebruikers'
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'users'),
            'supports' => array('title', 'editor'),
        );

        register_post_type('user', $args);
    }
    
    /**
     * Register meta box 'User properties'
     * 
     * @return void
     */
    public function add_user_meta_box() {
        add_meta_box('user-meta-box', 'User properties', [$this, 'render_meta_box_fields'], 'user', 'normal', 'high');
    }
    
    /**
     * Render the fields for the user meta box
     * 
     * @return void
     */
    public function render_meta_box_fields($post) {   
        wp_nonce_field( 'user_meta_box_nonce', 'meta_box_nonce' );
        $this->render_view('fields-metabox-user', [
            'fields' => $this->get_fields(), 
            'post' => $post,
            'prefix' => $this->prefix
        ]);
    }
    
    /**
     * Save the meta data into the database
     * 
     * @param type $post_id
     */
    public function save_meta_data($post_id) {
                           
        if( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'user_meta_box_nonce' ) ) 
            return;
        
        // Proceed to save data
        $fields = $this->get_fields();
        foreach ($fields as $field) {            
            $meta_key = $this->prefix . $field['name'];                                   
            if (isset($_POST[$meta_key])) {                              
                update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$meta_key]));
            }
        }
    }
    
    /**
     * Create the shortcode 
     * 
     */
    public function register_shortcode() {
        add_shortcode('user-register', [$this, 'render_shortcode']);
    }
    
    /**
     * Render form register in a page
     * 
     */
    public function render_shortcode() {
        global $post;
        $this->save_data_frontend();
        $this->render_view('form-user-register', [
            'prefix' => $this->prefix,
            'fields' => $this->get_fields(),
            'post' => $post,
            'message' => $this->message,
        ]);
    }
    
    /**
     * defien all fields
     * 
     * @return array
     */
    public function get_fields() {
        return [
            [
                'name' => 'email',
                'type' => 'text',
                'label' => 'E-mailadres'
            ],
            [
                'name' => 'phone',
                'type' => 'text',
                'label' => 'Telefoonnummer'
            ],
            [
                'name' => 'color',
                'type' => 'select',
                'label' => 'Favoriete kleur',
                'values' => ['geel' => 'Geel', 'blauw' => 'Blauw', 'rood' => 'Rood']
            ],
            [
                'label' => 'Opgeslagen in pagina',
                'name' => 'saved_in_page',
                'type' =>  'hidden',                
            ]
        ];
    }
    
    /**
     * 
     * 
     * @return mixed
     */
    private function save_data_frontend() {
        if( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'front_user_meta_box_nonce' ) ) 
            return;
        
        // check if user exists
        if (!is_null($this->user_exists($_POST[$this->prefix . 'email']))) {
            $this->message = 'Deze emailadres bestaat al';            
            return;
        }        
        
        // save post
        $post = filter_input_array(INPUT_POST);                       
        $post_id = wp_insert_post([
            'post_title' => $post[$this->prefix . 'name'],
            'post_type' => 'user'
        ]); 
        
        $mail_data = ['Naam' => $post[$this->prefix . 'name']];        
        $meta_fields = $this->get_fields();
        
        // save postmeta 
        foreach ($meta_fields as $field) {
            $meta_key = $this->prefix . $field['name'];
            $mail_data[$field['label']] = $post[$meta_key];
            update_post_meta($post_id, $meta_key, $post[$meta_key]);
        }      
        
        // send mail
        $this->mail_user_data($post[$this->prefix . 'email'], $mail_data);
        $this->mail_user_data(get_bloginfo('admin_email'), $mail_data);
        
        // put success message
        $this->message = 'Bedankt voor je registratie. Er is email ter bevestiging naar je gestuurd.';
    }
    
    /**
     * Send email
     * 
     * @param string $email
     * @param array $data
     */
    private function mail_user_data($email, $data) {
        
        ob_start();
        $this->render_view('email-template', ['data' => $data]);        
        $message = ob_get_contents();
        ob_end_clean();
        
        wp_mail($email, 'Nieuwe registratie', $message, [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=ISO-8859-1',
            'From: Woprdress <no-replye@example.net>'
        ]);
    }
    
    /**
     * 
     * @global wpdb $wpdb
     * @param string $email
     */
    private function user_exists($email) {
        global $wpdb;
        $query = "SELECT p.ID "
            . "FROM {$wpdb->posts} p "
            . "LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id "
            . "WHERE pm.meta_key = '{$this->prefix}email' "
            . "AND pm.meta_value = '{$email}'";
            
        return $wpdb->get_var($query);
    }
    
    
    /**
     * Render an specific HTML view. In this way we keep the html separeted from the logic 
     * 
     * @param string $template
     * @param array $data
     */
    private function render_view($template, $data = []) {        
        if (isset($data) && is_array($data)) {
            extract($data);
        }
        $template_path = $this->view_path . $template . '.php'; 
        if (file_exists($template_path)) {
            include_once $template_path;       
        }
    }                
    
}


new UserRegisterPlugin();