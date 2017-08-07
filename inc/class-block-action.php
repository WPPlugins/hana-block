<?php
/**
 * Class Meta Box
 * @package	  hanacore
 * @since     1.0
 * @author	  Stephen Cui
 * @copyright Copyright 2016, Stephen Cui
 * @license   GPL v3 or later
 * @link      http://rewindcreation.com/
 */
if ( ! defined('ABSPATH') ) exit;

if ( ! class_exists( 'Hana_Block_Action' ) ) {
    
    class Hana_Block_Action {
        protected $_post_id;

        function __construct( $post_id ) {

            $this->_post_id = $post_id;
            $action =  get_post_meta( $post_id, '_hana_block_action', true);

            if ( $action )
                add_action( $action, array( $this, 'action') );
        }
        
        public function action() {
            if ( $this->_post_id ) {
                echo hana_block()->content_block( array('id' => $this->_post_id ) );
            }
        }
    }
}
