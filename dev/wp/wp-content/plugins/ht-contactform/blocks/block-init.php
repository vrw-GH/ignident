<?php 

namespace HtContactForm\Block;


/**
 * analytical data store
*/
class Contactform_Block 
{
	/**
     * [$_instance]
     * @var null
     */
    private static $_instance = null;

    /**
     * [instance] Initializes a singleton instance
     * @return [Actions]
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
            self::$_instance->define_constants();
        }
        return self::$_instance;
    }

	/**
	 * The Constructor.
	*/
	public function __construct() {
		add_action( 'enqueue_block_assets', [ $this, 'block_assets' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'block_editor_assets' ] );
		add_action( 'init', [ $this, 'init' ] );
		add_action( 'rest_api_init', [ $this, 'register_api' ] );
	}

	public function init(){

		// Return early if this function does not exist.
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}
		$this->register_block();

	}

	/**
	 * Block assets.
	 */
	public function block_assets() {
		wp_enqueue_style(
			'ht-contactform-block-style',
			HTCONTACTFORM_BLOCK_URL . '/src/assets/css/style-index.css',
			array(),
			HTCONTACTFORM_VERSION
		);
	}

	/**
	 * Block editor assets.
	 */
	public function block_editor_assets() {
		$dependencies = require_once( HTCONTACTFORM_BLOCK_PATH . '/build/htcontactform-block.asset.php' );
		wp_enqueue_script(
			'ht-contactform-blocks',
			HTCONTACTFORM_BLOCK_URL . '/build/htcontactform-block.js',
			$dependencies['dependencies'],
			$dependencies['version'],
			true
		);
		wp_localize_script(
			'ht-contactform-blocks',
			'htcontactdata',
			[
				'pluginDirPath'   	=> plugin_dir_path( __DIR__ ),
				'pluginDirUrl'    	=> plugin_dir_url( __DIR__ ),
				'security' 			=> wp_create_nonce('htcontactform-nonce'),
			]
		);
		wp_enqueue_style( 
			'ht-contactform-block-editor-style', 
			HTCONTACTFORM_BLOCK_URL . '/src/assets/css/editor-style.css', 
			[], 
			HTCONTACTFORM_VERSION, 
			'all' 
		);
		wp_enqueue_style( 'ht-form' );
		wp_enqueue_style('ht-select');
		wp_enqueue_script('ht-select');
		wp_enqueue_script('ht-form');
	}

	private function register_block(){

		ob_start();
		include HTCONTACTFORM_BLOCK_PATH . '/src/ht-contactform-block/block.json';
		$attributes = json_decode( ob_get_clean(), true );

		register_block_type(
			'block/ht-contactform', array(
				'render_callback' => [ $this, 'render_content' ],
				'attributes'  	  => $attributes,
			)
		);

		ob_start();
		include HTCONTACTFORM_BLOCK_PATH . '/src/ht-form-block/block.json';
		$attributes = json_decode( ob_get_clean(), true );

		register_block_type(
			'block/ht-form', array(
				'render_callback' => [ $this, 'render_ht_form' ],
				'attributes'  	  => $attributes,
			)
		);
	}

	/**
	 * Define the required plugin constants
	 *
	 * @return void
	 */
	public function define_constants() {
		$this->define( 'HTCONTACTFORM_BLOCK_FILE', __FILE__ );
		$this->define( 'HTCONTACTFORM_BLOCK_PATH', __DIR__ );
		$this->define( 'HTCONTACTFORM_BLOCK_URL', plugins_url( '', HTCONTACTFORM_BLOCK_FILE ) );
	}


	/**
     * Define constant if not already set
     *
     * @param  string $name
     * @param  string|bool $value
     * @return type
     */
    private function define( $name, $value ) {
        if ( ! defined( $name ) ) {
            define( $name, $value );
        }
    }

	public function render_content($attr){

		if(isset($attr['formId']) && !empty($attr['formId'])){
			$block_uniqueid = '#ht-block-'.esc_attr($attr['blockUniqId']);
			ob_start();
				echo '<div id="ht-block-'.esc_attr($attr['blockUniqId']).'">';
					echo do_shortcode( '[contact-form-7 id="'.esc_attr($attr['formId']).'"]' );
				echo "</div>";
				?> 
					<style type="text/css">
						<?php echo esc_attr($block_uniqueid); ?>{
							<?php echo $this->dimentation($attr,'areaMargin','','margin'); ?>
						}
						<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 input:not([type="checkbox"]):not([type="submit"]),<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 textarea{
							<?php echo $this->generate_css($attr,'inputBackground','','background'); ?>
							<?php echo $this->generate_css($attr,'inputTextColor','','color'); ?>
							<?php echo $this->dimentation($attr,'inputPadding','','padding'); ?>
							<?php echo $this->dimentation($attr,'inputMargin','','margin'); ?>
							<?php
								$border = $attr['borderWidth'] ? $attr['borderWidth'].'px'.' '.$attr['borderType'].' '.$attr['borderColor'] : '';
								echo $this->generate_css($attr, $border, '', 'border'); 
							?>
							<?php echo $this->dimentation($attr,'inputBorderRadius','','border-radius'); ?>
						}
						<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 label{
							<?php echo $this->generate_css($attr,'labelColor','','color'); ?>
						}
						<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 input[type=submit]{
							<?php echo $this->generate_css($attr,'btnBackgroundColor','','background'); ?>
							<?php echo $this->dimentation($attr,'btnTextColor','','color'); ?>
							<?php
								$btn_border = $attr['btnBorderWidth'] ? $attr['btnBorderWidth'].'px'.' '.$attr['btnBorderType'].' '.$attr['btnBorderColor'] : '';
								echo $this->generate_css($attr, $btn_border, '', 'border'); 
							?>
							<?php echo $this->dimentation($attr,'buttonBorderRadius','','border-radius'); ?>
						}

						
						/* Normal desktop*/
						@media (min-width: 1300px){
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 input[type=text],
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 input[type=email],
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 input[type=password],
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 input[type=search],
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 input[type=tel],
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 input[type=url],
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 select{
								height: <?php echo $attr['inputHight']['desktop'] ?>px;
							}
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 textarea{
								height: <?php echo $attr['textAreaHight']['desktop'];?>px;
							}
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 input:not([type="checkbox"]):not([type="submit"]),<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 textarea{
								<?php echo $this->generate_css($attr,'inputTextSize','desktop','font-size'); ?>
							}
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 label{
								<?php echo $this->generate_css($attr,'labelFontSize','desktop','font-size'); ?>
							}
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 input[type=submit]{
								<?php echo $this->generate_css($attr,'btnFontSize','desktop','font-size'); ?>
								<?php echo $this->dimentation($attr,'buttonPadding','desktop','padding'); ?>
								<?php echo $this->dimentation($attr,'buttonMargin','desktop','margin'); ?>
							}
						}

						/* Normal laptop*/
						@media (min-width: 992px) and (max-width: 1299px){
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 input[type=text],
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 input[type=email],
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 input[type=password],
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 input[type=search],
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 input[type=tel],
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 input[type=url],
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 select{
								height: <?php echo $attr['inputHight']['laptop'] ?>px;
							}
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 textarea{
								height: <?php echo $attr['textAreaHight']['laptop'];?>px;
							}
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 input:not([type="checkbox"]):not([type="submit"]),<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 textarea{
								<?php echo $this->generate_css($attr,'inputTextSize','laptop','font-size'); ?>
							}
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 label{
								<?php echo $this->generate_css($attr,'labelFontSize','laptop','font-size'); ?>
							}
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 input[type=submit]{
								<?php echo $this->generate_css($attr,'btnFontSize','laptop','font-size'); ?>
								<?php echo $this->dimentation($attr,'buttonPadding','laptop','padding'); ?>
								<?php echo $this->dimentation($attr,'buttonMargin','laptop','margin'); ?>
							}
						}

						/* Normal tablate*/
						@media (min-width: 768px) and (max-width: 991px) {
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 input[type=text],
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 input[type=email],
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 input[type=password],
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 input[type=search],
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 input[type=tel],
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 input[type=url],
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 select{
								height: <?php echo $attr['inputHight']['tablet'] ?>px;
							}
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 textarea{
								height: <?php echo $attr['textAreaHight']['tablet'];?>px;
							}
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 input:not([type="checkbox"]):not([type="submit"]),<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 textarea{
								<?php echo $this->generate_css($attr,'inputTextSize','tablet','font-size'); ?>
							}
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 label{
								<?php echo $this->generate_css($attr,'labelFontSize','tablet','font-size'); ?>
							}
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 input[type=submit]{
								<?php echo $this->generate_css($attr,'btnFontSize','tablet','font-size'); ?>
								<?php echo $this->dimentation($attr,'buttonPadding','tablet','padding'); ?>
								<?php echo $this->dimentation($attr,'buttonMargin','tablet','margin'); ?>
							}
						}

						/* Normal mobile*/
						@media (max-width: 767px) {
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 input[type=text],
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 input[type=email],
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 input[type=password],
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 input[type=search],
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 input[type=tel],
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 input[type=url],
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 select{
								height: <?php echo $attr['inputHight']['mobile'] ?>px;
							}
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 textarea{
								height: <?php echo $attr['textAreaHight']['mobile'];?>px;
							}
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 input:not([type="checkbox"]):not([type="submit"]),<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 textarea{
								<?php echo $this->generate_css($attr,'inputTextSize','mobile','font-size'); ?>
							}
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 label{
								<?php echo $this->generate_css($attr,'labelFontSize','mobile','font-size'); ?>
							}
							<?php echo esc_attr( $block_uniqueid ); ?> .wpcf7 input[type=submit]{
								<?php echo $this->generate_css($attr,'btnFontSize','mobile','font-size'); ?>
								<?php echo $this->dimentation($attr,'buttonPadding','mobile','padding'); ?>
								<?php echo $this->dimentation($attr,'buttonMargin','mobile','margin'); ?>
							}
						}
					</style>
				<?php
			return ob_get_clean();
		}else{
			return '<p class="ht-contactform-initial">'.esc_html__( "Please Select a contact form.", "ht-contactform" ).'</p>';
		}
	}



	public function render_ht_form($attr){

		if(isset($attr['formId']) && !empty($attr['formId'])){
			ob_start();
				echo '<div id="ht-block-'.esc_attr($attr['blockUniqId']).'">';
					echo do_shortcode( '[ht_form id="'.esc_attr($attr['formId']).'"]' );
				echo "</div>";
			return ob_get_clean();
		}else{
			return '<p class="ht-contactform-initial">'.esc_html__( "Please Select a contact form.", "ht-contactform" ).'</p>';
		}
	}

	private function generate_css($settings, $attribute, $device, $css_attr, $important = ''){

		if('' == $device && 'border' != $css_attr ){
			$value = !empty( $settings[$attribute] ) ? $settings[$attribute] : '';
		}else{
			$value = !empty( $settings[$attribute][$device] ) ? $settings[$attribute][$device] : '';
		}

		if('border' == $css_attr){
			$value = $attribute;
		}

		if( !empty( $value ) && 'NaN' !== $value ){
			$css_attr .= ":{$value}";
			return $css_attr."{$important};";
		}else{
			return "";
		}
	}

	private function dimentation($settings, $attribute, $device, $css_attr, $important = ''){
		$dimensions = !empty( $settings[$attribute] ) ? $settings[$attribute] : array();
		if('' == $device){
			if( isset( $dimensions['top'] ) || isset( $dimensions['right'] ) || isset( $dimensions['bottom'] ) || isset( $dimensions['left'] ) ){
				$unit = empty( $dimensions['unit'] ) ? 'px' : $dimensions['unit'];
				$top = ( $dimensions['top'] !== '' ) ? $dimensions['top'].$unit : null;
				$right = ( $dimensions['right'] !== '' ) ? $dimensions['right'].$unit : null;
				$bottom = ( $dimensions['bottom'] !== '' ) ? $dimensions['bottom'].$unit : null;
				$left = ( $dimensions['left'] !== '' ) ? $dimensions['left'].$unit : null;
				$css_dimension = ( ($top != null) || ($right !=null) || ($bottom != null) || ($left != '') ) ? ( $css_attr.":{$top} {$right} {$bottom} {$left}" ) : '';

				return $css_dimension."{$important};";
		
			}else{
				return "";
			}
		}else{
			if( isset( $dimensions[$device]['top'] ) || isset( $dimensions[$device]['right'] ) || isset( $dimensions[$device]['bottom'] ) || isset( $dimensions[$device]['left'] ) ){
				$unit = empty( $dimensions['unit'] ) ? 'px' : $dimensions['unit'];
				$top = ( $dimensions[$device]['top'] !== '' ) ? $dimensions[$device]['top'].$unit : null;
				$right = ( $dimensions[$device]['right'] !== '' ) ? $dimensions[$device]['right'].$unit : null;
				$bottom = ( $dimensions[$device]['bottom'] !== '' ) ? $dimensions[$device]['bottom'].$unit : null;
				$left = ( $dimensions[$device]['left'] !== '' ) ? $dimensions[$device]['left'].$unit : null;
				$css_dimension = ( ($top != null) || ($right !=null) || ($bottom != null) || ($left != '') ) ? ( $css_attr.":{$top} {$right} {$bottom} {$left}" ) : '';

				return $css_dimension."{$important};";
			}else{
				return "";
			}
		}
	}

	public function register_api(){
		$api = new Api\Api();
        $api->register_routes();
	}

}

Contactform_Block::instance();