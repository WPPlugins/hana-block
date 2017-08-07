<?php
/**
 * Foundaiton Grid Class
 * 
 * @package	  hanacore
 * @since     1.0
 * @author	  Stephen Cui
 * @copyright Copyright 2016, Stephen Cui
 * @license   GPL v3 or later
 * @link      http://rewindcreation.com/
 */
if ( ! class_exists( 'HANA_Grid' ) ) {
	
	class HANA_Grid {
		public $grid = array(
			'grid_width' => 1200,
			'grid_column' => 12, //Only 12-Column Grid is supported as of now
			'fluid_grid' => 0,
			'fluid_header' => 0,
			'sidebar_pos' => 'right',
			'content_column' => 8,
			'sidebar1_column' => 2,
			'sidebar2_column' => 2,		
			'sidebar_bbp' => 3,
		);

		private function __construct() {
			// Core uses the own defaults if theme do not add them in customizer
			$this->grid['grid_width'] = absint(get_theme_mod( 'grid_width', $this->grid['grid_width'] ));
			$this->grid['fluid_grid'] = absint(get_theme_mod( 'fluid_grid', $this->grid['fluid_grid'] ));
			$this->grid['fluid_header'] = absint(get_theme_mod( 'fluid_header', $this->grid['fluid_header'] ));
			$this->grid['sidebar_pos'] = get_theme_mod( 'sidebar_pos', $this->grid['sidebar_pos'] );
			$this->grid['content_column'] = absint(get_theme_mod( 'content_column', $this->grid['content_column'] ));
			$this->grid['sidebar1_column'] = absint(get_theme_mod( 'sidebar1_column', $this->grid['sidebar1_column'] ));
			$this->grid['sidebar2_column'] = absint(get_theme_mod( 'sidebar2_column', $this->grid['sidebar2_column'] ));
			$this->grid['sidebar_bbp'] = absint(get_theme_mod( 'sidebar_bbp', $this->grid['sidebar_bbp'] ));
		}

		public function main_class( $echo = true ) {
			$class = array();
            $class[] = 'site-main';
			if ( is_page_template( apply_filters('hanagrid_fluidwidth_templates', array('pages/fluidwidth.php','pages/homepage.php') ) ) ) {
				$class[] = 'clearfix';                
            } else {
				$class[] = 'row';
				if ($this->grid['fluid_grid'] )
					$class[] = 'expanded';
			}
			$class = apply_filters( 'hanagrid_main_class', $class );
			$imp_class = implode( ' ', $class );
			if ( $echo )
				echo esc_attr( $imp_class );
			else
				return $imp_class;
		}

        public function row_class( $type = 'row', $echo = true  ) {
            $class = array();
            $class[] = 'row';
            if ( 'rowcol' == $type || 'rowexpcol' == $type)
                $class[] = 'collapse';
            if ( 'rowexp' == $type || 'rowexpcol' == $type)
                $class[] = 'expanded';
			$class = apply_filters( 'hanagrid_row_class', $class );
			$imp_class = implode( ' ', $class );
			if ( $echo )
				echo esc_attr( $imp_class );
			else
				return $imp_class;
        }
        
		public function header_row_class( $echo = true ) {
			$class = array();
			$class[] = 'row';
			if ( $this->grid['fluid_grid'] || $this->grid['fluid_header']  )
				$class[] = 'expanded';
			$class = apply_filters( 'hanagrid_header_row_class', $class );
			$imp_class = implode( ' ', $class );
			if ( $echo )
				echo esc_attr( $imp_class );
			else
				return $imp_class;
		}

		public function content_class( $echo = true ) {
			$class = array();
			$class[] = 	'large-' . $this->grid['content_column'];
			$class[] = 	'medium-' . $this->grid['content_column'];

			if ( 'left' == $this->grid['sidebar_pos'] && ( $this->grid['sidebar1_column'] > 0 || $this->grid['sidebar2_column'] > 0 ) ) {
				if ( ( $this->grid['content_column'] + $this->grid['sidebar1_column'] + $this->grid['sidebar2_column'] ) > $this->grid['grid_column']  ) {
					if ( $this->grid['sidebar1_column'] > $this->grid['sidebar2_column'] )
						$push_col = $this->grid['sidebar1_column']; 
					else
						$push_col = $this->grid['sidebar2_column'];
				}
				else {
					$push_col = $this->grid['sidebar1_column'] + $this->grid['sidebar2_column']; 			
				}
				$class[] = 'large-push-' . $push_col;
				$class[] = 'medium-push-' . $push_col;
			}
			elseif ( 'both' == $this->grid['sidebar_pos'] && $this->grid['sidebar1_column'] > 0 ) {
				$class[] = 'large-push-' . $this->grid['sidebar1_column'];		
				$class[] = 'medium-push-' . $this->grid['sidebar1_column'];		
			}
			elseif ( 'none' == $this->grid['sidebar_pos']  ) {
				$class[] = 'large-centered';		
			}
			$class[] = 'columns';
			$class = apply_filters( 'hanagrid_content_class', $class );
			$imp_class = implode( ' ', $class );
			if ( $echo )
				echo esc_attr( $imp_class );
			else
				return $imp_class;
		}
		
		public function sidebar_class( $location = 'full', $echo = true ) {
			$class = array();
			
			$width =  $this->grid['sidebar1_column'] +  $this->grid['sidebar2_column'];		
			if ( ( $width + $this->grid['content_column'] ) > $this->grid['grid_column'] ) 
				$width = $this->grid['grid_column'] - $this->grid['content_column'];
				
			if ( 'full' == $location ) {
				$class[] = 'large-' . $width;
				$class[] = 'medium-' . $width;			

				if ( 'left' == $this->grid['sidebar_pos'] ) {
					$class[] = 'large-pull-' . $this->grid['content_column'];
					$class[] = 'medium-pull-' . $this->grid['content_column'];
				}
				$class[] = 'columns';
				$class = apply_filters( 'hanagrid_sidebarfull_class', $class );
			}
			elseif ( 'one' == $location ) {
				if ( 'both' == $this->grid['sidebar_pos'] )
					$width = $this->grid['sidebar1_column'];
				$class[] = 'large-' . $this->grid['sidebar1_column'];
				$class[] = 'medium-' . $width;
				
				if ( 'right' != $this->grid['sidebar_pos'] ) {
					$class[] = 'large-pull-' . $this->grid['content_column'];
					$class[] = 'medium-pull-' . $this->grid['content_column'];				
				}
				if ( 'right' == $this->grid['sidebar_pos'] ) {
                    $class[] = 'float-right';
                }
				$class[] = 'columns';
				$class = apply_filters( 'hanagrid_sidebarone_class', $class );	
			}
			elseif ( 'two' == $location ) {
				if ( 'both' == $this->grid['sidebar_pos'] )
					$width = $this->grid['sidebar2_column'];
				$class[] = 'large-' . $this->grid['sidebar2_column'];
				$class[] = 'medium-' . $width;
				if ( 'left' == $this->grid['sidebar_pos'] ) {
					$class[] = 'large-pull-' . $this->grid['content_column'];
					$class[] = 'medium-pull-' . $this->grid['content_column'];						
				}
				$class[] = 'columns';
				$class = apply_filters( 'hanagrid_sidebartwo_class', $class );	
			}		
			$imp_class = implode( ' ', $class );
			if ( $echo )
				echo esc_attr( $imp_class );
			else
				return $imp_class;
		}
		
		public function fullgrid_class( $echo = true ) {
			$class = array();
			$class[] = 'large-' . $this->grid['grid_column'];
			$class[] = 'columns';
			$imp_class = implode( ' ', $class );
			if ( $echo )
				echo esc_attr( $imp_class );
			else
				return $imp_class;
		}

		public function column_class( $large_col, $medium_col = NULL, $small_col = NULL, $echo = true ) {
			$class = array();
			$class[] = 'large-' . $large_col;
			if ( isset( $medium_col ) )
                $class[] = 'medium-' . $medium_col;        
			if ( isset( $small_col ) )
                $class[] = 'small-' . $small_col;
			$class[] = 'columns';
			$imp_class = implode( ' ', $class );
			if ( $echo )
				echo esc_attr( $imp_class );
			else
				return $imp_class;
		}
		
		public function bbp_content_class ( $echo = true ) {
			$class = array();

			$width = $this->grid['grid_column'] - $this->grid['sidebar_bbp'];
			$class[] = 'large-' . $width;
			$class[] = 'medium-' . $width;
			$class[] = 'columns';
			$class = apply_filters( 'hanagrid_bbp_content_class', $class );
			$imp_class = implode( ' ', $class );
			if ( $echo )
				echo esc_attr( $imp_class );
			else
				return $imp_class;
		}
		
		public function bbp_sidebar_class ( $echo = true ) {
			$class = array();

			$class[] = 'large-' . $this->grid['sidebar_bbp'];
			$class[] = 'medium-' . $this->grid['sidebar_bbp'];
			$class[] = 'columns';
			$class = apply_filters( 'hanagrid_bbp_sidebar_class', $class );
			$imp_class = implode( ' ', $class );
			if ( $echo )
				echo esc_attr( $imp_class );
			else
				return $imp_class;
		}
		/**
		 * Create the object when called for the 1st time
		 */
		public static function get_instance() {
			static $instance = null;

			if ( is_null( $instance ) )
				$instance = new HANA_Grid;

			return $instance;
		}

	}

	function hana_grid() {
		return HANA_Grid::get_instance();
	}

}