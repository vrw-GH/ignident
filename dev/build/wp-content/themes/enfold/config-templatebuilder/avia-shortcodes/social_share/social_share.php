<?php
/**
 * Social Share Buttons
 *
 * Shortcode creates one or more social share buttons
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'avia_sc_social_share', false ) )
{
	class avia_sc_social_share extends aviaShortcodeTemplate
	{
		/**
		 * Create the config array for the shortcode button
		 */
		protected function shortcode_insert_button()
		{
			$this->config['version']		= '1.0';
			$this->config['self_closing']	= 'yes';
			$this->config['base_element']	= 'yes';

			$this->config['name']			= __( 'Social Buttons', 'avia_framework' );
			$this->config['tab']			= __( 'Content Elements', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-social.png';
			$this->config['order']			= 7;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode'] 		= 'av_social_share';
			$this->config['tooltip'] 	    = __( 'Create one or more social buttons to share a post or to link to your social profile', 'avia_framework' );
			$this->config['preview'] 		= true;
//			$this->config['disabling_allowed'] 	= true;		//	also needed in single pages
			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'yes';
			$this->config['alb_desc_id']	= 'alb_description';
		}


		protected function extra_assets()
		{
			$ver = Avia_Builder()->get_theme_version();
			$min_css = avia_minify_extension( 'css' );

			//load css
			wp_enqueue_style( 'avia-module-social', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/social_share/social_share{$min_css}.css", array( 'avia-layout' ), $ver );
		}


		/**
		 * Popup Elements
		 *
		 * If this function is defined in a child class the element automatically gets an edit button, that, when pressed
		 * opens a modal window that allows to edit the element properties
		 *
		 * @return void
		 */
		protected function popup_elements()
		{
			$this->elements = array(

				array(
						'type' 	=> 'tab_container',
						'nodescription' => true
					),

				array(
						'type' 	=> 'tab',
						'name'  => __( 'Content', 'avia_framework' ),
						'nodescription' => true
					),

					array(
							'type'			=> 'template',
							'template_id'	=> $this->popup_key( 'content_icons' )
						),

				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),

				array(
						'type' 	=> 'tab',
						'name'  => __( 'Profiles', 'avia_framework' ),
						'nodescription' => true
					),

					array(
							'type'			=> 'template',
							'template_id'	=> $this->popup_key( 'content_profiles' )
						),

				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),

				array(
						'type' 	=> 'tab',
						'name'  => __( 'Styling', 'avia_framework' ),
						'nodescription' => true
					),

					array(
							'type'			=> 'template',
							'template_id'	=> $this->popup_key( 'styling_general' )
						),

				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),

				array(
						'type' 	=> 'tab',
						'name'  => __( 'Advanced', 'avia_framework' ),
						'nodescription' => true
					),

					array(
							'type' 	=> 'toggle_container',
							'nodescription' => true
						),

						array(
								'type'			=> 'template',
								'template_id'	=> 'screen_options_toggle',
								'lockable'		=> true
							),

						array(
								'type'			=> 'template',
								'template_id'	=> 'developer_options_toggle',
								'args'			=> array( 'sc' => $this )
							),

					array(
							'type' 	=> 'toggle_container_close',
							'nodescription' => true
						),

				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),

				array(
						'type'			=> 'template',
						'template_id'	=> 'element_template_selection_tab',
						'args'			=> array( 'sc' => $this )
					),

				array(
						'type' 	=> 'tab_container_close',
						'nodescription' => true
					)

				);

		}

		/**
		 * Create and register templates for easier maintainance
		 *
		 * @since 4.6.4
		 */
		protected function register_dynamic_templates()
		{

			/**
			 * Content Tab
			 * ===========
			 */

			$desc  = __( 'Which Social Buttons do you want to display? Defaults are set in ', 'avia_framework' );
			$desc .= '<a target="_blank" href="' . admin_url( 'admin.php?page=avia#goto_blog' ) . '">' . __( 'Blog Layout', 'avia_framework' ) . '</a>';

			$check = __( 'Check to display', 'avia_framework' );
			$check_profile = __( 'Check to display', 'avia_framework' );

			$profile_desc  = ' ' . __( 'Make sure to add the profile link here in &quot;Profile Tab&quot; or in ', 'avia_framework' );
			$profile_desc .= '<a target="_blank" href="' . admin_url( 'admin.php?page=avia#goto_social' ) . '">' . __( 'Social Profiles', 'avia_framework' ) . '</a>';

			$c = array(
						array(
							'name'		=> __( 'Small title', 'avia_framework' ),
							'desc'		=> __( 'A small title above the buttons.', 'avia_framework' ),
							'id'		=> 'title',
							'type'		=> 'input',
							'std'		=> __( 'Share this entry', 'avia_framework' ),
							'lockable'	=> true
						),

						array(
							'name'		=> __( 'Social Buttons', 'avia_framework' ),
							'desc'		=> $desc,
							'id'		=> 'buttons',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Use defaults that are also used for your blog (share entry)', 'avia_framework' )	=> '',
												__( 'Use a custom set', 'avia_framework' )		=> 'custom'
											),
						),

						array(
							'name'		=> __( 'Custom Button Behaviour', 'avia_framework' ),
							'desc'		=> __( 'Select what you want to do. Social media platform is opened in a new window. Make sure to set the links to your social profiles. Share links are created by theme.', 'avia_framework' ),
							'id'		=> 'btn_action',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'buttons', 'equals', 'custom' ),
							'subtype'	=> array(
												__( 'Share Entry', 'avia_framework' )			=> '',
												__( 'Link to your profile', 'avia_framework' )	=> 'profile'
											),

						),

						array(
							'name'		=> __( 'Social Profile And Share Buttons', 'avia_framework' ),
							'desc'		=> __( 'The following buttons support share links (generated by theme) and links to a profile.', 'avia_framework' ) . $profile_desc,
							'type'		=> 'heading',
							'required'	=> array( 'buttons', 'equals', 'custom' ),
							'description_class' => 'av-builder-note av-neutral'
						),

						array(
							'name'		=> __( 'Facebook link', 'avia_framework' ),
							'desc'		=> $check,
							'id'		=> 'share_facebook',
							'type'		=> 'checkbox',
							'std'		=> '',
							'container_class' => 'av_third av_third_first',
							'lockable'	=> true,
							'required'	=> array( 'buttons', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'X link', 'avia_framework' ),
							'desc'		=> $check,
							'id'		=> 'share_twitter',
							'type'		=> 'checkbox',
							'std'		=> '',
							'container_class' => 'av_third ',
							'lockable'	=> true,
							'required'	=> array( 'buttons', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'Square-X-Twitter link', 'avia_framework' ),
							'desc'		=> $check,
							'id'		=> 'share_square-x-twitter',
							'type'		=> 'checkbox',
							'std'		=> '',
							'container_class' => 'av_third ',
							'lockable'	=> true,
							'required'	=> array( 'buttons', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'WhatsApp link', 'avia_framework' ),
							'desc'		=> $check,
							'id'		=> 'share_whatsapp',
							'type'		=> 'checkbox',
							'std'		=> '',
							'container_class' => 'av_third ',
							'lockable'	=> true,
							'required'	=> array( 'buttons', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'Pinterest link', 'avia_framework' ),
							'desc'		=> $check,
							'id'		=> 'share_pinterest',
							'type'		=> 'checkbox',
							'std'		=> '',
							'container_class' => 'av_third ',
							'lockable'	=> true,
							'required'	=> array( 'buttons', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'Reddit link', 'avia_framework' ),
							'desc'		=> $check,
							'id'		=> 'share_reddit',
							'type'		=> 'checkbox',
							'std'		=> '',
							'container_class' => 'av_third ',
							'lockable'	=> true,
							'required'	=> array( 'buttons', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'Telegram link', 'avia_framework' ),
							'desc'		=> $check,
							'id'		=> 'share_telegram',
							'type'		=> 'checkbox',
							'std'		=> '',
							'container_class' => 'av_third ',
							'lockable'	=> true,
							'required'	=> array( 'buttons', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'LinkedIn link', 'avia_framework' ),
							'desc'		=> $check,
							'id'		=> 'share_linkedin',
							'type'		=> 'checkbox',
							'std'		=> '',
							'container_class' => 'av_third ',
							'lockable'	=> true,
							'required'	=> array( 'buttons', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'Tumblr link', 'avia_framework' ),
							'desc'		=> $check,
							'id'		=> 'share_tumblr',
							'type'		=> 'checkbox',
							'std'		=> '',
							'container_class' => 'av_third ',
							'lockable'	=> true,
							'required'	=> array( 'buttons', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'VK link', 'avia_framework' ),
							'desc'		=> $check,
							'id'		=> 'share_vk',
							'type'		=> 'checkbox',
							'std'		=> '',
							'container_class' => 'av_third ',
							'lockable'	=> true,
							'required'	=> array( 'buttons', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'Email link', 'avia_framework' ),
							'desc'		=> $check,
							'id'		=> 'share_mail',
							'type'		=> 'checkbox',
							'std'		=> '',
							'container_class' => 'av_third ',
							'lockable'	=> true,
							'required'	=> array( 'buttons', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'Yelp', 'avia_framework' ),
							'desc'		=> $check,
							'id'		=> 'share_yelp',
							'type'		=> 'checkbox',
							'std'		=> '',
							'container_class' => 'av_third ',
							'lockable'	=> true,
							'required'	=> array( 'buttons', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'Yelp Share Link', 'avia_framework' ),
							'desc'		=> __( 'Enter the share link to Yelp for this button.', 'avia_framework' ),
							'id'		=> 'yelp_link',
							'type'		=> 'input',
							'std'		=> 'https://www.yelp.com',
							'lockable'	=> true,
							'required'	=> array( 'share_yelp', 'not', '' )
						),

						array(
							'name'		=> __( 'Social Profile Buttons', 'avia_framework' ),
							'desc'		=> __( 'The following buttons currently only support links to a profile.', 'avia_framework' ) . $profile_desc,
							'type'		=> 'heading',
							'required'	=> array( 'buttons', 'equals', 'custom' ),
							'description_class' => 'av-builder-note av-neutral'
						),

						array(
							'name'		=> __( '500px Link', 'avia_framework' ),
							'desc'		=> $check_profile,
							'id'		=> 'share_five_100_px',
							'type'		=> 'checkbox',
							'std'		=> '',
							'container_class' => 'av_third ',
							'lockable'	=> true,
							'required'	=> array( 'buttons', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'Behance Link', 'avia_framework' ),
							'desc'		=> $check_profile,
							'id'		=> 'share_behance',
							'type'		=> 'checkbox',
							'std'		=> '',
							'container_class' => 'av_third ',
							'lockable'	=> true,
							'required'	=> array( 'buttons', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'Dribbble Link', 'avia_framework' ),
							'desc'		=> $check_profile,
							'id'		=> 'share_dribbble',
							'type'		=> 'checkbox',
							'std'		=> '',
							'container_class' => 'av_third ',
							'lockable'	=> true,
							'required'	=> array( 'buttons', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'Flickr Link', 'avia_framework' ),
							'desc'		=> $check_profile,
							'id'		=> 'share_flickr',
							'type'		=> 'checkbox',
							'std'		=> '',
							'container_class' => 'av_third ',
							'lockable'	=> true,
							'required'	=> array( 'buttons', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'Instagram Link', 'avia_framework' ),
							'desc'		=> $check_profile,
							'id'		=> 'share_instagram',
							'type'		=> 'checkbox',
							'std'		=> '',
							'container_class' => 'av_third ',
							'lockable'	=> true,
							'required'	=> array( 'buttons', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'Skype Link', 'avia_framework' ),
							'desc'		=> $check_profile,
							'id'		=> 'share_skype',
							'type'		=> 'checkbox',
							'std'		=> '',
							'container_class' => 'av_third ',
							'lockable'	=> true,
							'required'	=> array( 'buttons', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'Soundcloud Link', 'avia_framework' ),
							'desc'		=> $check_profile,
							'id'		=> 'share_soundcloud',
							'type'		=> 'checkbox',
							'std'		=> '',
							'container_class' => 'av_third ',
							'lockable'	=> true,
							'required'	=> array( 'buttons', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'Vimeo Link', 'avia_framework' ),
							'desc'		=> $check_profile,
							'id'		=> 'share_vimeo',
							'type'		=> 'checkbox',
							'std'		=> '',
							'container_class' => 'av_third ',
							'lockable'	=> true,
							'required'	=> array( 'buttons', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'Xing Link', 'avia_framework' ),
							'desc'		=> $check_profile,
							'id'		=> 'share_xing',
							'type'		=> 'checkbox',
							'std'		=> '',
							'container_class' => 'av_third ',
							'lockable'	=> true,
							'required'	=> array( 'buttons', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'YouTube Link', 'avia_framework' ),
							'desc'		=> $check_profile,
							'id'		=> 'share_youtube',
							'type'		=> 'checkbox',
							'std'		=> '',
							'container_class' => 'av_third ',
							'lockable'	=> true,
							'required'	=> array( 'buttons', 'equals', 'custom' )
						),

				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_icons' ), $c );


			/**
			 * Profiles Tab
			 * ============
			 */

			$desc_link = __( 'Enter the link to your profile for this button. Leave empty to use link defined in ', 'avia_framework' );
			$desc_link .= '<a target="_blank" href="' . admin_url( 'admin.php?page=avia#goto_social' ) . '">' . __( 'Social Profiles', 'avia_framework' ) . '</a>';


			$c = array(

						array(
							'name'		=> __( 'Social Profile Links', 'avia_framework' ),
							'desc'		=> __( 'You selected to use the social share buttons used for your blog selected on theme options page . No options available here at the moment.', 'avia_framework' ),
							'type'		=> 'heading',
							'required'	=> array( 'buttons', 'equals', '' ),
							'description_class' => 'av-builder-note av-neutral'
						),

						array(
							'name'		=> __( 'Social Profile Links', 'avia_framework' ),
							'desc'		=> __( 'Enter links to your social profiles which will be used in this element only.', 'avia_framework' ),
							'type'		=> 'heading',
							'required'	=> array( 'buttons', 'equals', 'custom' ),
							'description_class' => 'av-builder-note av-neutral'
						),

						array(
							'name'		=> __( 'Facebook Profile Link', 'avia_framework' ),
							'desc'		=> $desc_link,
							'id'		=> 'facebook_profile',
							'type'		=> 'input',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'btn_action', 'not', '' )
						),

						array(
							'name'		=> __( 'X Profile Link', 'avia_framework' ),
							'desc'		=> $desc_link,
							'id'		=> 'twitter_profile',
							'type'		=> 'input',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'btn_action', 'not', '' )
						),

						array(
							'name'		=> __( 'WhatsApp Profile Link', 'avia_framework' ),
							'desc'		=> $desc_link,
							'id'		=> 'whatsapp_profile',
							'type'		=> 'input',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'btn_action', 'not', '' )
						),

						array(
							'name'		=> __( 'Pinterest Profile Link', 'avia_framework' ),
							'desc'		=> $desc_link,
							'id'		=> 'pinterest_profile',
							'type'		=> 'input',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'btn_action', 'not', '' )
						),

						array(
							'name'		=> __( 'Reddit Profile Link', 'avia_framework' ),
							'desc'		=> $desc_link,
							'id'		=> 'reddit_profile',
							'type'		=> 'input',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'btn_action', 'not', '' )
						),

						array(
							'name'		=> __( 'LinkedIn Profile Link', 'avia_framework' ),
							'desc'		=> $desc_link,
							'id'		=> 'linkedin_profile',
							'type'		=> 'input',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'btn_action', 'not', '' )
						),

						array(
							'name'		=> __( 'Tumblr Profile Link', 'avia_framework' ),
							'desc'		=> $desc_link,
							'id'		=> 'tumblr_profile',
							'type'		=> 'input',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'btn_action', 'not', '' )
						),

						array(
							'name'		=> __( 'VK Profile Link', 'avia_framework' ),
							'desc'		=> $desc_link,
							'id'		=> 'vk_profile',
							'type'		=> 'input',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'btn_action', 'not', '' )
						),

						array(
							'name'		=> __( 'Email Profile Link', 'avia_framework' ),
							'desc'		=> $desc_link,
							'id'		=> 'mail_profile',
							'type'		=> 'input',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'btn_action', 'not', '' )
						),

						array(
							'name'		=> __( 'Yelp Profile Link', 'avia_framework' ),
							'desc'		=> $desc_link,
							'id'		=> 'yelp_profile',
							'type'		=> 'input',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'btn_action', 'not', '' )
						),

						array(
							'name'		=> __( '500px Profile Link', 'avia_framework' ),
							'desc'		=> $desc_link,
							'id'		=> 'five_100_px_profile',
							'type'		=> 'input',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'buttons', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'Behance Profile Link', 'avia_framework' ),
							'desc'		=> $desc_link,
							'id'		=> 'behance_profile',
							'type'		=> 'input',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'buttons', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'Dribbble Profile Link', 'avia_framework' ),
							'desc'		=> $desc_link,
							'id'		=> 'dribbble_profile',
							'type'		=> 'input',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'buttons', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'Flickr Profile Link', 'avia_framework' ),
							'desc'		=> $desc_link,
							'id'		=> 'flickr_profile',
							'type'		=> 'input',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'buttons', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'Instagram Profile Link', 'avia_framework' ),
							'desc'		=> $desc_link,
							'id'		=> 'instagram_profile',
							'type'		=> 'input',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'buttons', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'Skype Profile Link', 'avia_framework' ),
							'desc'		=> $desc_link,
							'id'		=> 'skype_profile',
							'type'		=> 'input',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'buttons', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'Soundcloud Profile Link', 'avia_framework' ),
							'desc'		=> $desc_link,
							'id'		=> 'soundcloud_profile',
							'type'		=> 'input',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'buttons', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'Vimeo Profile Link', 'avia_framework' ),
							'desc'		=> $desc_link,
							'id'		=> 'vimeo_profile',
							'type'		=> 'input',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'buttons', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'Xing Profile Link', 'avia_framework' ),
							'desc'		=> $desc_link,
							'id'		=> 'xing_profile',
							'type'		=> 'input',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'buttons', 'equals', 'custom' )
						),

						array(
							'name'		=> __( 'YouTube Profile Link', 'avia_framework' ),
							'desc'		=> $desc_link,
							'id'		=> 'youtube_profile',
							'type'		=> 'input',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'buttons', 'equals', 'custom' )
						),

				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_profiles' ), $c );


			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(
						array(
							'name'		=> __( 'Button Bar Style', 'avia_framework' ),
							'desc'		=> __( 'Select how to display the social buttons bar', 'avia_framework' ),
							'id'		=> 'style',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Rectangular', 'avia_framework' )			=> '',
												__( 'Rectangular minimal', 'avia_framework' )	=> 'minimal',
												__( 'Block square', 'avia_framework' )			=> 'av-social-sharing-box-square',
												__( 'Rounded rectangular', 'avia_framework' )	=> 'av-social-sharing-box-rounded',
												__( 'Buttons', 'avia_framework' )				=> 'av-social-sharing-box-buttons',
												__( 'Circle', 'avia_framework' )				=> 'av-social-sharing-box-circle',
												__( 'Icon', 'avia_framework' )					=> 'av-social-sharing-box-icon',
												__( 'Icon simple', 'avia_framework' )			=> 'av-social-sharing-box-icon-simple',
											),

						),

						array(
							'name'		=> __( 'Button Bar Alignment', 'avia_framework' ),
							'desc'		=> __( 'Select alignment of the social buttons bar', 'avia_framework' ),
							'id'		=> 'alignment',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> true,
							'required'	=> array( 'style', 'parent_in_array', 'av-social-sharing-box-square,av-social-sharing-box-circle,av-social-sharing-box-icon,av-social-sharing-box-icon-simple' ),
							'subtype'	=> array(
												__( 'Left', 'avia_framework' )		=> '',
												__( 'Centered', 'avia_framework' )	=> 'av-social-sharing-center',
												__( 'Right', 'avia_framework' )		=> 'av-social-sharing-right',
											)

						),

				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_general' ), $c );

		}

		/**
		 * Create custom stylings
		 *
		 * @since 4.8.7
		 * @param array $args
		 * @return array
		 */
		protected function get_element_styles( array $args )
		{
			$result = parent::get_element_styles( $args );

			extract( $result );

			$default = array(
						'title'				=> '',
						'btn_action'		=> '',
						'buttons'			=> '',
						'share_facebook'	=> '',
						'share_twitter'		=> '',
						'share_whatsapp'	=> '',
						'share_vk'			=> '',
						'share_tumblr'		=> '',
						'share_linkedin'	=> '',
						'share_pinterest'	=> '',
						'share_mail'		=> '',
						'share_reddit'		=> '',
						'share_telegram'  => '',
						'share_yelp'		=> '',
						'share_five_100_px'	=> '',
						'share_behance'		=> '',
						'share_dribbble'	=> '',
						'share_flickr'		=> '',
						'share_instagram'	=> '',
						'share_skype'		=> '',
						'share_soundcloud'	=> '',
						'share_vimeo'		=> '',
						'share_xing'		=> '',
						'share_youtube'		=> '',
						'facebook_profile'	=> '',
						'twitter_profile'	=> '',
						'whatsapp_profile'	=> '',
						'vk_profile'		=> '',
						'tumblr_profile'	=> '',
						'linkedin_profile'	=> '',
						'pinterest_profile'	=> '',
						'mail_profile'		=> '',
						'reddit_profile'	=> '',
						'yelp_link'			=> '',
						'telegram_link'			=> '',
						'yelp_profile'		=> '',
						'five_100_px_profile' => '',
						'behance_profile'	=> '',
						'dribbble_profile'	=> '',
						'flickr_profile'	=> '',
						'instagram_profile'	=> '',
						'skype_profile'		=> '',
						'soundcloud_profile' => '',
						'vimeo_profile'		=> '',
						'xing_profile'		=> '',
						'youtube_profile'	=> '',
						'style'				=> '',
						'alignment'			=> ''
					);

			$default = $this->sync_sc_defaults_array( $default, 'no_modal_item', 'no_content' );

			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );

			/**
			 * Set style classes - were extended to support multiple stylings for buttons
			 *
			 * @since 4.8.3
			 */
			if( '' == $atts['style'] )
			{
				$atts['style'] = 'av-social-sharing-box-default';
			}
			else if( 'minimal' == $atts['style'] )
			{
				$atts['style'] = 'av-social-sharing-box-minimal';
			}



			$classes = array(
						'av-social-sharing-box',
						$element_id,
						$atts['style']
					);

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes_from_array( 'container', $meta, 'el_class' );
			$element_styling->add_responsive_classes( 'container', 'hide_element', $atts );

			if( ! in_array( $atts['style'], array( 'av-social-sharing-box-default', 'av-social-sharing-box-minimal', 'av-social-sharing-box-icon', 'av-social-sharing-box-icon-simple' ) ) )
			{
				$element_styling->add_classes( 'container', 'av-social-sharing-box-color-bg' );
			}

			if( in_array( $atts['style'], array( 'av-social-sharing-box-square', 'av-social-sharing-box-circle', 'av-social-sharing-box-icon', 'av-social-sharing-box-icon-simple' ) ) )
			{
				$element_styling->add_classes( 'container', array( 'av-social-sharing-box-same-width', $atts['alignment'] ) );
			}
			else
			{
				$element_styling->add_classes( 'container', 'av-social-sharing-box-fullwidth' );
			}



			$selectors = array(
							'container'	=> ".av-social-sharing-box.{$element_id}"
				);

			$element_styling->add_selectors( $selectors );

			$result['default'] = $default;
			$result['atts'] = $atts;
			$result['content'] = $content;
			$result['element_styling'] = $element_styling;

			return $result;
		}

		/**
		 * Frontend Shortcode Handler
		 *
		 * @param array $atts array of attributes
		 * @param string $content text within enclosing form of shortcode element
		 * @param string $shortcodename the shortcode found, when == callback name
		 * @param array $meta
		 * @return string $output returns the modified html string
		 */
		public function shortcode_handler( $atts, $content = '', $shortcodename = '', $meta = '' )
		{
			$result = $this->get_element_styles( compact( array( 'atts', 'content', 'shortcodename', 'meta' ) ) );

			extract( $result );
			extract( $atts );

			$args = array();
			$options = false;
			$echo = false;

			if( $buttons == 'custom' )
			{
				foreach( $atts as $key => &$att )
				{
					if( ! empty( $att ) )
					{
						continue;
					}

					if( 0 === strpos( $key, 'share_' ) )
					{
						$att = 'disabled';
					}
				}
				unset( $att );
				$options = $atts;
			}

			$style_tag = $element_styling->get_style_tag( $element_id );
			$container_class = $element_styling->get_class_string( 'container' );

			$output  = '';
			$output .= $style_tag;
			$output .= "<div {$meta['custom_el_id']} class='{$container_class}'>";
			$output .=		avia_social_share_links( $args, $options, $title, $echo );
			$output .= '</div>';

			return $output;
		}

	}
}
