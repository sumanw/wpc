<?php

/**
 * Unit tests for the Calculator class.
 *
 * @package WPC_Plugin_Demo
 */
use \WPC_Plugin_Demo\WPC;

class Test_WPC extends WP_UnitTestCase {
    
    protected static $wpc;
    public static $user_id;
    public static $post;        
    protected static $nonce;        
    protected static $plugin_name;    

    public function setUp() {
        parent::setUp();
        self::$wpc = new WPC();
        self::$plugin_name = 'WPC';
        
    }    
    /*
     * Unit test for registrating meta box for post_type=post in Side Corner
     *
     * @see WPC::wc_add_meta_box();
     */
    public function test_wc_add_meta_box() {
        global $wp_meta_boxes;
        $post_type = 'post';
        self::$wpc->wc_add_meta_box($post_type);
        self::assertArrayHasKey('wcp_meta_box', $wp_meta_boxes['post']['side']['core']);        
    }
    
    /*
     * Unit test for displaying meta box in Side corner
     *
     * @see WPC::wpc_add_meta_box();
     */
    
    public function test_wpc_add_meta_box() {
        self::$user_id = self::factory()->user->create(array(
            'role' => 'author',
        ));
        self::$wpc::$post= self::$post = $this->factory()->post->create_and_get(array('post_title' => 'dummy', 'post_content' => 'dummy dummy',
            'post_type' => 'post', 'post_author' => self::$user_id));         
        $data = self::$wpc->wpc_add_meta_box(self::$post);        
        $this->assertStringStartsWith('No Author added', $data);
    }

/*
*Unit test for saving meta box data
* @see WPC::wc_save();
*/

 public function test_wc_save() {                
        $wpc_contributors=array(self::$user_id);        
        update_post_meta(self::$post->ID, '_wpc_contributors',$wpc_contributors);    
        $userid=get_post_meta(self::$post->ID,'_wpc_contributors',true);                          
        $this->assertEquals(self::$user_id, $userid[0]);
    }    
    /*
   *Unit test for displaying contributors
   * @see WPC::wc_display();
   */
    public function test_wc_display() {  
        global $post;
        $GLOBALS['post']=$post;
        $expected='<p>dummy dummy</p>';
        $contributors="";
        $contributors.='<div class="dcontributors"><span class="contributors-text">Contributors</span>';
        $contributors.='<ul class="contributors" data-bi-name="contributors">';
        $userUrl=get_avatar_url( self::$user_id );
        $args = array(       
          'include'      =>  array(self::$user_id),
          'fields'       => 'all'    
            ); 
        $usersData=get_users( $args,true );         
        $contributors.='<li><img srcset="'.$userUrl.' 2x" data-src="'.$userUrl.'"/></li>';
        $contributors.='</ul></div>';
        $wpc_contributors=self::$user_id;        
        update_post_meta(self::$post->ID, '_wpc_contributors',$wpc_contributors);           
        $filtered_content=self::$wpc->wc_display($expected);       
        $filtered_content.=$contributors;
        self::go_to( get_permalink(self::$wpc::$post->ID) ); 
        $content = apply_filters( 'the_content', $expected,self::$post->ID);    
         $expected.=$contributors;               
       $this->assertEquals( strip_ws( $expected ), strip_ws( $filtered_content ) );       
    } 
}
