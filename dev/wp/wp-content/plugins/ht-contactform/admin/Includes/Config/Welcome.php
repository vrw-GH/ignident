<?php
namespace HTContactFormAdmin\Includes\Config;
class Welcome {
    private static $instance = null;

    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct() {
        
    }

    public function welcome() {
        return [
            'content' => [
                'title' => __('Welcome to HT Contact Form', 'ht-contactform'),
                'description' => __('HT Contact Form empowers you to create beautiful, functional forms without any coding knowledge. Whether you need contact forms, surveys, lead generation forms, or feedback collectors, our intuitive drag-and-drop builder makes it simple. Start creating powerful forms in minutes and watch your conversion rates soar!', 'ht-contactform'),
                'buttons' => [
                    [
                        'label' => __('Create Your First Form', 'ht-contactform'),
                        'url' => admin_url('admin.php?page=htcontact-form&path=editor'),
                    ],
                    [
                        'label' => __('Read the Full Guide', 'ht-contactform'),
                        'url' => 'https://hasthemes.com/docs/ht-contact-forms/ht-contact-forms/',
                        'target' => '_blank',
                    ],
                ]
            ],
            'features' => [
                [
                    'title' => __('Drag and Drop', 'ht-contactform'),
                    'description' => __('Easily drag and drop form fields to create your perfect form.', 'ht-contactform'),
                    'icon' => 'IconDragDrop'
                ],
                [
                    'title' => __('Entry Management', 'ht-contactform'),
                    'description' => __('Track and manage all form submissions in one convenient dashboard.', 'ht-contactform'),
                    'icon' => 'IconDatabase'
                ],
                [
                    'title' => __('Form Builder', 'ht-contactform'),
                    'description' => __('Build forms with ease using our intuitive form builder.', 'ht-contactform'),
                    'icon' => 'IconBuildingStore'
                ],
                [
                    'title' => __('Email Notifications', 'ht-contactform'),
                    'description' => __('Receive email notifications when form submissions are received.', 'ht-contactform'),
                    'icon' => 'IconMail'
                ],
                [
                    'title' => __('Form Preview', 'ht-contactform'),
                    'description' => __('Preview your form to ensure it looks and functions as expected.', 'ht-contactform'),
                    'icon' => 'IconEye'
                ],
                [
                    'title' => __('Form Editor', 'ht-contactform'),
                    'description' => __('Edit your form at any time to make changes or updates.', 'ht-contactform'),
                    'icon' => 'IconEdit'
                ],
            ]
        ];
    }

    public function banners() {
        return [
            $this->cookieray_banner(),
        ];
    }

    public function cookieray_banner() {
        ob_start();
        ?>
            <div class="htcontact-form-cookieray-banner">
                <h2><?php echo esc_html__( 'Stay Cookie & GDPR Compliant', 'ht-contactform' ); ?></h2>
                <p class="htcontact-form-cookieray-desc"><?php echo esc_html__( 'Add CookieRay — a free cookie consent plugin to keep your site GDPR compliant.', 'ht-contactform' ); ?></p>
                <div class="htcontact-form-cookieray-features">
                    <ul class="htcontact-form-cookieray-feature-list">
                        <li><?php echo esc_html__( 'Customizable Consent Banner', 'ht-contactform' ); ?></li>
                        <li><?php echo esc_html__( 'Built-in Cookie Scanner', 'ht-contactform' ); ?></li>
                        <li><?php echo esc_html__( 'GDPR & CCPA Ready', 'ht-contactform' ); ?></li>
                    </ul>
                </div>
                <div class="htcontact-form-cookieray-action-btn">
                    <a class="htcontact-form-cookieray-btn" href="<?php echo esc_url('https://wordpress.org/plugins/cookieray/?utm_source=htcontactform&utm_medium=htcf7dashboard&utm_campaign=cookieray');?>" target="_blank" rel="noopener noreferrer">
                        <span class="htcontact-form-cookieray-btn-text"><?php echo esc_html__('Get CookieRay Free','ht-contactform'); ?></span>
                    </a>
                </div>
            </div>
        <?php
        return ob_get_clean();
    }

    public function extcf7_banner() {
        ob_start();
        ?>
            <div class="htcontact-form-banner">
                <h2><?php echo esc_html__( 'Purchase CF7 Pro Extensions', 'ht-contactform' ); ?></h2>
                <div class="htcontact-form-banner-features">
                    <ul class="htcontact-form-banner-feature-list">
                        <li><?php echo esc_html__( 'Already Submitted Notice', 'ht-contactform' ); ?></li>
                        <li><?php echo esc_html__( 'Repeater Field', 'ht-contactform' ); ?></li>
                        <li><?php echo esc_html__( 'Popup Form Response', 'ht-contactform' ); ?></li>
                        <li><?php echo esc_html__( 'Advanced Telephone', 'ht-contactform' ); ?></li>
                        <li><?php echo esc_html__( 'Acceptance Field', 'ht-contactform' ); ?></li>
                        <li><?php echo esc_html__( 'Drag & Drop File Upload', 'ht-contactform' ); ?></li>
                    </ul>
                </div>

                <div class="htcontact-form-banner-action-btn">
                    <a class="htcontact-form-banner-btn" href="<?php echo esc_url('https://hasthemes.com/plugins/cf7-extensions/?utm_source=htcontactform&utm_medium=htcf7dashboard&utm_campaign=extension');?>" target="_blank">
                        <span class="htcontact-form-banner-btn-text"><?php echo esc_html__('Get Pro Now','ht-contactform'); ?></span>
                        <span class="htcontact-form-banner-btn-icon"><img src="<?php echo HTCONTACTFORM_PL_URL ?>assets/images/icon/white-plus.png" alt="<?php echo esc_attr__('Get Pro Now','ht-contactform'); ?>"></span>
                    </a>
                </div>
            </div>
        <?php
        return ob_get_clean();
    }
}