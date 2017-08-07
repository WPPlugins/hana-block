<?php
/*
 * Plugin Name: Hana Block 
 * Plugin URI: http://rewindcreation.com/hana-block
 * Description: A simple and powerfull plugin that allows to create content block and display anywhere.
 * Author: RewindCreation
 * Version: 1.0.0
 * License: GNU General Public License v3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: hana-block
 * Domain Path: /languages/
 
 * @package   rewind
 * @author    Stephen Cui
 * @copyright Copyright (c) 2016, Stephen Cui
 */
if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'Hana_Block' ) ) {

class Hana_Block {
    public $allowed_types = array();
    public $block_types = array();
    public $level = 0;
    public $layout = array();

    private function __construct() {
        $this->setup_globals();
        $this->includes();
        $this->actions();
        $this->shortcodes();
    }
    
	private function setup_globals() {
		/** Versions **********************************************************/
		$this->version    = '1.0.0';
		/** Paths *************************************************************/
		$this->file       = __FILE__;
		$this->basename   = plugin_basename( $this->file );
		$this->plugin_dir = plugin_dir_path( $this->file );
		$this->plugin_url = plugin_dir_url ( $this->file );
        
        $this->block_types = array ( ''			=> esc_html__( 'Content', 'hana-block' ),
                                    'row'	    => esc_html__( 'Row', 'hana-block' ),
                                    'rowcol'	=> esc_html__( 'Collapse Row', 'hana-block' ),
                                    'rowexp'	=> esc_html__( 'Expanded Row', 'hana-block' ),
                                    'rowexpcol'	=> esc_html__( 'Expanded Collapse Row', 'hana-block' )
                            );
        $this->allowed_types = array( 'post', 'page', 'hana_block');
    }

    public function includes() {
        
        if ( is_admin() ) {
            require_once( $this->plugin_dir . 'inc/lib-choices.php' );
            require_once( $this->plugin_dir . 'inc/class-meta-box.php' );
        }
        require_once( $this->plugin_dir . 'inc/class-grid.php' );
        require_once( $this->plugin_dir . 'inc/class-block-action.php' );
    }
    
    private function actions() {
        add_action( 'init',  array( $this, 'add_post_type' ) );
        add_action( 'plugins_loaded', array( $this, 'loaded' ) );
        add_action( 'admin_menu',  array( $this, 'metabox' ) );
        
        add_filter( 'manage_edit-hana_block_columns', array( $this, 'add_columns' ) );
        add_action( 'admin_head', array( $this, 'column_width' ) );
        add_action( 'manage_posts_custom_column', array( $this, 'display_columns' ), 10, 2 );
    }

    private function shortcodes() {
        add_shortcode( 'content-block',  array( $this, 'content_block' ) );
    }
    
    public function loaded() { //Actions after plugin loaded
        load_plugin_textdomain( 'hana-block' );

        if ( ! is_admin() ) {
            add_action('wp_enqueue_scripts', array( $this, 'frontend_styles') );
            
            $query = array( 
                'post_type' => 'hana_block',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => '_hana_block_action',
                        'value' => '',
                        'compare' => '!='
                    )
                )
            );
            $blocks = get_posts( $query );
            foreach ( $blocks as $block ) {
                new Hana_Block_Action( $block->ID );
            }
        } else {
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts') );
        }
    }
    
    public function frontend_styles() {
        if ( ! current_theme_supports( 'hana-block' ) ) {
            wp_enqueue_style( 'hana-block-grid', $this->plugin_url . 'css/grid.css', false, '1.0.0'  );            
        }
    }

    public function admin_scripts( $hooks ) {
        global $post_type;

        if ( 'hana_block' == $post_type ) {
            wp_enqueue_script( 'hana-block', $this->plugin_url . 'js/hana-block.js', array( 'jquery') );	
        }
    }
    public function add_post_type() {
        $labels = array(
            'name' => esc_html_x( 'Content Blocks', 'post type general name', 'hana-block' ),
            'singular_name' => esc_html_x( 'Content Block', 'post type singular name', 'hana-block' ),
            'plural_name' => _x( 'Content Blocks', 'post type plural name', 'hana-block' ),
            'add_new' => esc_html_x( 'Add Content Block', 'block', 'hana-block' ),
            'add_new_item' => esc_html__( 'Add New Content Block', 'hana-block' ),
            'edit_item' => esc_html__( 'Edit Content Block', 'hana-block' ),
            'new_item' => esc_html__( 'New Content Block', 'hana-block' ),
            'view_item' => esc_html__( 'View Content Block', 'hana-block' ),
            'search_items' => esc_html__( 'Search Content Blocks', 'hana-block' ),
            'not_found' =>  esc_html__( 'No Content Blocks Found', 'hana-block' ),
            'not_found_in_trash' => esc_html__( 'No Content Blocks found in Trash', 'hana-block' )
        );
        $args = array(
            'labels' => $labels,
            'public' => false,
            'publicly_queryable' => false,
            'exclude_from_search' => true,
            'show_ui' => true,
            'query_var' => true,
            'rewrite' => true,
            'capability_type' => 'post',
            'hierarchical' => false,
            'menu_icon' => 'dashicons-screenoptions',
            'supports' => array( 'title','editor','revisions','thumbnail', 'author' )
        );
        register_post_type( 'hana_block', $args );   
    }

    public function metabox() {
        $meta_boxes = array(
            'hana_block' => array( 
                'id' => 'hana-block-meta',
                'title' => esc_html__('Hana Block Options', 'hana-block'), 
                'type' => 'hana_block',
                'context' => 'side',  //normal, advaned, side  
                'priority' => 'default', //high, core, default, low
                'fields' => array(
                    array(
                        'name' => esc_html__( 'Block Type :', 'hana-block'),
                        'desc' => '',
                        'id' => '_hana_block_type',
                        'type' => 'select',
                        'options' => $this->block_types,
                    ),
                    array(
                        'name' => esc_html__( 'Layout :', 'hana-block'),
                        'desc' => esc_html__( 'Row block only', 'hana-block'),
                        'id' => '_hana_block_layout',
                        'type' => 'select',
                        'options' => hana_column_choices( true ),
                    ),
                    array(
                        'name' => esc_html__( 'Theme Action:' ,'hana'),
                        'desc' => '',
                        'id' => '_hana_block_action',
                        'type' => 'text',
                    ),
                ),
            ) );
        
        foreach ( $meta_boxes as $meta_box )
            $box = new Hana_Meta_Box( $meta_box );        
    }

    // Add content block information column to overview
    public function add_columns( $column ) {
        $column['block_type'] = esc_html__( 'Block Type', 'hana-block' );
        $column['action'] = esc_html__( 'Action', 'hana-block' );
        return $column;
    }

    function column_width() {
        echo '<style type="text/css">';
        echo '.column-block_type { width:150px !important; overflow:hidden }';
        echo '.column-action { width:200px !important; overflow:hidden }';
        echo '</style>';
    }
    
    public function display_columns( $column_name, $post_id ) {
        $custom_fields = get_post_custom( $post_id );
        switch ( $column_name ) {
            case 'block_type' :
                if ( !empty( $custom_fields['_hana_block_type'][0] ) ) {
                    echo $this->block_types[$custom_fields['_hana_block_type'][0]];
                }
            break;
            case 'action' :
                if ( !empty( $custom_fields['_hana_block_action'][0] ) ) {
                    echo esc_html( $custom_fields['_hana_block_action'][0] );
                }
            break;                
        }
    }

    public function content_block( $atts ) {

        extract( shortcode_atts( array(
            'id' => '',
            'slug' => '',
            'class' => '',
        ), $atts ) );
    
        if ( $slug )
            $block = get_page_by_path( $slug, OBJECT, $this->allowed_types );
        else
            $block = get_post( $id );
        

        if( $block ) {
            setup_postdata( $block );

            ob_start();
            $type = get_post_meta( $block->ID, '_hana_block_type', true);
            if ( ! empty( $type ) &&  'row' == substr($type, 0, 3 ) ) { //Row
                if ( 0 == $this->level ) { // Section                     
                    // Background image for section only.
                    $style = '';
                    $img_id = get_post_thumbnail_id( $block );
                    if ( $img_id ) {
                        $size = apply_filters( 'post_thumbnail_size', 'full' );
                        $image = wp_get_attachment_image_src( $img_id, $size);
                        $style = 'style="background-image:url(' . esc_url($image[0]) . ');"';
                    }
                    $data = apply_filters( 'hana_block_section_data', '');
                    echo '<div id="block-' . $block->ID . '" class="block-section" ' . $style . ' ' . $data. '>';
                }
                $this->level = $this->level + 1;
                $this->layout[$this->level] =  get_post_meta( $block->ID, '_hana_block_layout', true);
                
                echo '<div class="' . hana_grid()->row_class( $type, false ) . '">';
            } else { // Content
                if ( false != strpos($class, 'columns') ) {
                    //user defined columns class
                } elseif ( ! empty( $this->layout[$this->level] ) ) {
                    $col = absint( hana_grid()->grid['grid_column'] / absint( $this->layout[$this->level] ) );
                    $class .= hana_grid()->column_class( $col, $col, NULL, false);
                }
            }

            if ( $class )
                echo '<div class="'. esc_attr($class) .'">';
            if (  empty( $type ) ) {
                echo get_the_post_thumbnail( $block->ID );
            }

            the_content();
            if ( $class )
                echo '</div>';
            if ( ! empty( $type ) ) { //Row
                echo '</div>';
                $this->level = $this->level - 1;
                if (0 == $this->level)
                    echo '</div>';
            }
            $content = ob_get_clean();
            wp_reset_postdata();
            return $content;
	   }
    }
    
    public static function instance() {
        static $instance = null;

		if ( null === $instance ) {
			$instance = new Hana_Block;
		}
		return $instance;
	}

    
} // end class
    
function hana_block() {
    return Hana_Block::instance();
}
    
hana_block();

} //end of Class check
