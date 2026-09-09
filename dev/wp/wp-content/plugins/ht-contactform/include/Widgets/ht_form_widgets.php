<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HTForm_Elementor_Widget extends Widget_Base {

    public function get_name() {
        return 'htform-addons';
    }
    
    public function get_title() {
        return __( 'HT Form', 'ht-contactform' );
    }

    public function get_icon() {
        return 'eicon-mail';
    }

    public function get_categories() {
        return [ 'general' ];
    }

    public function get_style_depends(){
        return [
            'ht-form',
            'ht-select'
        ];
    }
    public function get_script_depends() {
        return [
            'ht-select',
            'ht-imask',
            'ht-recaptcha-v2',
        ];
    }

    public function ht_forms(){
        $formlist = array();
        $forms_args = array( 'posts_per_page' => -1, 'post_type'=> 'ht_form' );
        $forms = get_posts( $forms_args );
        if( $forms ){
            foreach ( $forms as $form ){
                $formlist[$form->ID] = $form->post_title;
            }
        }else{
            $formlist['0'] = __('Form not found','ht-contactform');
        }
        return $formlist;
    }

    protected function register_controls() {

        $this->start_controls_section(
            'ht_form_content',
            [
                'label' => __( 'Contact Form', 'ht-contactform' ),
            ]
        );
            $this->add_control(
                'contact_form_id',
                [
                    'label'             => __( 'Select Form', 'ht-contactform' ),
                    'type'              => Controls_Manager::SELECT,
                    'label_block'       => true,
                    'options'           => $this->ht_forms(),
                    'default'           => '0',
                ]
            );

        $this->end_controls_section();

    }

    protected function render( $instance = [] ) {
        $settings   = $this->get_settings_for_display();

        $this->add_render_attribute( 'shortcode', 'id', $settings['contact_form_id'] );
        $shortcode = sprintf( '[ht_form %s]', $this->get_render_attribute_string( 'shortcode' ) );

        if( !empty( $settings['contact_form_id'] ) ){
            echo do_shortcode( $shortcode ); 
            ?>
                <script>
                    // Handle Range Slider Value Update
                    if(document.querySelectorAll('.ht-form-elem-range')) {
                        document.querySelectorAll('.ht-form-elem-range').forEach((range) => {
                            range.addEventListener('input', () => {
                                range.nextElementSibling.querySelector('.ht-form-elem-range-amount').textContent = range.value;
                            });
                        });
                    }

                    // Input Mask
                    if(document.querySelectorAll('[data-mask]')) {
                        document.querySelectorAll('[data-mask]').forEach((input) => {
                            const maskFormat = input.getAttribute('data-mask');
                            
                            if (maskFormat) {
                                let maskOptions = {};
                                
                                // Configure specific formats
                                if (maskFormat === 'MM/DD/YYYY') {
                                    // Date mask with M/D/Y format
                                    maskOptions = {
                                        alias: 'datetime',
                                        inputFormat: 'MM/DD/YYYY',
                                    };
                                } else if (maskFormat === 'HH:MM') {
                                    // Time mask
                                    maskOptions = {
                                        alias: 'datetime',
                                        inputFormat: 'HH:mm',
                                        placeholder: 'HH:MM'
                                    };
                                } else if (maskFormat === '9999 9999 9999 9999') {
                                    // Credit card mask
                                    maskOptions = {
                                        mask: '9999 9999 9999 9999'
                                    };
                                } else if (maskFormat === '$999.99') {
                                    // Currency mask
                                    maskOptions = {
                                        alias: 'numeric',
                                        groupSeparator: '',
                                        digits: 2,
                                        digitsOptional: false,
                                        prefix: '$',
                                        rightAlign: false,
                                        allowMinus: false,
                                    };
                                } else if (maskFormat === '(999) 999-9999') {
                                    // Phone mask
                                    maskOptions = {
                                        mask: '(999) 999-9999'
                                    };
                                } else if (maskFormat === '999-99-9999') {
                                    // SSN mask
                                    maskOptions = {
                                        mask: '999-99-9999'
                                    };
                                } else if (maskFormat === '99999-9999') {
                                    // Zip code mask
                                    maskOptions = {
                                        mask: '99999-9999'
                                    };
                                } else {
                                    // Default - use the format as is
                                    maskOptions = {
                                        mask: maskFormat
                                    };
                                }
                                
                                // Apply the mask and store reference for validation
                                const im = new Inputmask(maskOptions);
                                im.mask(input);
                            }
                        });
                    }
                    
                    // Custom Select using Choices JS
                    if(document.querySelectorAll('select[data-ht-select]')) {
                        document.querySelectorAll('select[data-ht-select]').forEach((select) => {
                            const searchable = select.getAttribute('data-searchable') === '1';
                            const maxselect = select.getAttribute('data-maxselect') ? parseInt(select.getAttribute('data-maxselect')) : -1;
                            
                            try {
                                new Choices(select, {
                                    searchEnabled: searchable,
                                    itemSelectText: '',
                                    maxItemCount: maxselect,
                                    removeItemButton: true,
                                    placeholder: true,
                                    placeholderValue: '',
                                    shouldSort: false,
                                });
                            } catch (error) {
                                console.warn('Choices initialization error:', error);
                            }
                        });
                    }
                </script>
            <?php
        }else{
            echo '<div class="form_no_select">' .__('Please Select contact form.','ht-contactform'). '</div>';
        }
    }
}

Plugin::instance()->widgets_manager->register_widget_type( new HTForm_Elementor_Widget() );