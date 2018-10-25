<?php
/*
 * WPC class.
 *
 * @package WPC_Plugin_Demo
 */

namespace WPC_Plugin_Demo;

class WPC {
    private $userRole = 'author';
    private $emptyMSG = 'No Author added. Please add more Authors';
    private $Message = 'Author Added';
    public static $post;
    
    public function __construct() {
        global $post;
        add_action('add_meta_boxes', array($this, 'wc_add_meta_box'));
        // Save meta box value
        add_action('save_post', array($this, 'wc_save'));
        // Display contributors
        add_filter('the_content', array($this, 'wc_display'),10,1);
        add_action( 'wp_enqueue_scripts',  array($this, 'add_css') );
        }
    /*
     * Adds meta box to post_type=post
     * @$post_type=post
     */
    public function wc_add_meta_box($post_type) {
        $post_types = array('post');
        if (in_array($post_type, $post_types)) {
            $t = add_meta_box(
                    'wcp_meta_box', 'Contributors', array($this, 'wpc_add_meta_box'), $post_type, 'side', 'core'
            );
        }
    }

    public function add_css(){
        wp_enqueue_style( 'wpc-style',plugins_url('wpc/assets/css/style.css'),false,'1.1','all');
    }

    /* Render Meta Box */

    public function wpc_add_meta_box($post) {
        $outputDisplay = "";
        $nonce=wp_nonce_field('wpc_custom_box', 'wpc_custom_box_nonce');        
        $contributors = get_post_meta($post->ID, '_wpc_contributors', true);        
        $contributors = (!empty($contributors)) ? explode(",", $contributors) : array();
        $Users_args = array(
            'exclude' => array($post->post_author),
            'role' => $this->userRole,
            'orderby' => 'role',
            'order' => 'ASC',
            'fields' => array(
                'ID', 'display_name', 'user_nicename'
            )
        );
        $authors = get_users($Users_args);
        if (count($authors) > 0) {            
            $outputDisplay.= "<ul id='wpc_contributor_list'>";
            foreach ($authors as $author) {

                if (in_array($author->ID, $contributors)) {
                    $outputDisplay.= "<li> <input checked='checked' type='checkbox' name='wpc_contributors[]' value='" . $author->ID . "' /> " . esc_html($author->user_nicename) . "</li>";
                } else {
                    $outputDisplay.= "<li> <input type='checkbox' name='wpc_contributors[]' value='" . $author->ID . "' /> " . esc_html($author->user_nicename) . "</li>";
                }
            }
            $outputDisplay.= "</ul>";
        } else {
            $outputDisplay.= $this->emptyMSG;
        }        
        echo $outputDisplay;
       return $outputDisplay;                          
    }

    public function wc_save($post_id) {        
        if (
                !isset($_POST['wpc_custom_box_nonce']) || !wp_verify_nonce($_POST['wpc_custom_box_nonce'], 'wpc_custom_box')
        ) {
            return $post_id;
        } else {
            $wp_contributor = isset($_POST['wpc_contributors']) ? sanitize_meta('_wpc_contributors', $_POST['wpc_contributors'], 'user') : "";            
            $getContributors = get_post_meta($post_id, '_wpc_contributors', true);
            if (isset($getContributors) && empty($getContributors)) {
                $t = add_post_meta($post_id, '_wpc_contributors', implode(",", $wp_contributor), true);
            } else {
                $t = update_post_meta($post_id, '_wpc_contributors', implode(",", $wp_contributor));
            }
        }
        return $t;
    } 

    public function wc_display($content) {        
        global $post;        
        $id=(!empty($post)&&!is_null($post))?$post->ID:(self::$post->ID);
        $userid=get_post_meta($id,'_wpc_contributors',true);      
        
        $users=(!empty($userid))?explode(',',$userid):array();
        $contributors="";
        if(!empty($users)){
          $args = array(       
          'include'      => $users,
          'fields'       => 'all'    
            ); 
        $usersData=get_users( $args );         
        $contributors.='<div class="dcontributors"><span class="contributors-text">Contributors</span>';
        $contributors.='<ul class="contributors" data-bi-name="contributors">';
                                                                                                                
        foreach ($usersData as $value) {
            $userUrl=get_avatar_url( $value->ID );
            $contributors.='<li><img srcset="'.$userUrl.' 2x" data-src="'.$userUrl.'"/></li>';
        }
         $contributors.='</ul></div>';
        }       
        if ( is_single() ){
        // Add image to the beginning of each page
        $content.=$contributors;
    }
    // Returns the content.    
    return $content;
    }  
}

$contribute = new WPC();

