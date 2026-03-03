<?php

$course_page_url = sprintf('?page=%s', 'ays-pb');
$lessons_page_url = admin_url( 'edit.php?post_type=flessons' );

$popup_page_url = sprintf('?page=%s', 'ays-pb');
$questions_page_url = sprintf('?page=%s', 'ays-pb-questions');
$new_questions_page_url = sprintf('?page=%s&action=%s', 'ays-pb-questions', 'add');

?>
<div class="wrap">
    <!-- Hero Section -->
    <section class="pb-hero">
        <div class="pb-hero-container">
            <div class="pb-logo">
                <img class="logo" src="<?php echo esc_url( AYS_PB_ADMIN_URL ) . '/images/wp-popup-box-plugin-logo.png'; ?>" alt="Popup Box" title="Popup Box"/>
            </div>
            <h2 class="pb-hero-title"><?php echo esc_html__("Welcome to Popup Box", 'ays-popup-box'); ?></h2>
            <p class="pb-hero-subtitle"><?php echo esc_html__("Create Powerful Popups That Convert.", 'ays-popup-box'); ?></p>
            <div class="pb-hero-buttons">                
                <a class="pb-btn pb-btn-primary" href="<?php echo esc_url( $popup_page_url ); ?>">
                    <svg width="16" height="20" viewBox="0 0 16 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M13.9495 7.91656V5.66656C13.9495 4.26644 13.9495 3.56636 13.6812 3.03159C13.4452 2.56119 13.0687 2.17874 12.6054 1.93905C12.0789 1.66656 11.3896 1.66656 10.011 1.66656H4.75975C3.38116 1.66656 2.69186 1.66656 2.16531 1.93905C1.70215 2.17874 1.32558 2.56119 1.08958 3.03159C0.821289 3.56636 0.821289 4.26644 0.821289 5.66656V14.3333C0.821289 15.7334 0.821289 16.4334 1.08958 16.9683C1.32558 17.4386 1.70215 17.8211 2.16531 18.0608C2.69186 18.3333 3.38116 18.3333 4.75975 18.3333H9.02642M9.02642 9.16656H4.10334M5.74436 12.4999H4.10334M10.6674 5.83324H4.10334M11.0777 12.5018C11.2223 12.0844 11.5076 11.7324 11.8832 11.5082C12.2588 11.284 12.7004 11.2021 13.1299 11.2769C13.5593 11.3517 13.9487 11.5784 14.2293 11.9169C14.5098 12.2554 14.6634 12.6839 14.6628 13.1263C14.6628 14.3754 12.818 14.9999 12.818 14.9999M12.8417 17.4999H12.85" stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>

                    <?php echo esc_html__("Create Popup", 'ays-popup-box'); ?>
                </a>
            </div>
        </div>
    </section>

    <!-- Quick Start Steps Section -->
    <section class="pb-steps">
        <div class="pb-steps-container">
            <h2 class="pb-steps-title"><?php echo esc_html__("Quick Setup Guide", 'ays-popup-box'); ?></h2>
            <div class="pb-steps-content">
                <div class="pb-steps-list">
                    <h3 class="pb-steps-sub-title"><?php echo esc_html__("4 Simple Steps", 'ays-popup-box'); ?></h3>
                    <ol class="pb-ordered-list">
                        <li class="pb-step-item">
                            <div class="pb-step-number">1</div>
                            <div class="pb-step-text">
                                <p class="pb-step-title"><?php echo esc_html__("Create popup", 'ays-popup-box'); ?></p>
                            </div>
                        </li>
                        <li class="pb-step-item">
                            <div class="pb-step-number">2</div>
                            <div class="pb-step-text">
                                <p class="pb-step-title"><?php echo esc_html__("Add content", 'ays-popup-box'); ?></p>
                            </div>
                        </li>
                        <li class="pb-step-item">
                            <div class="pb-step-number">3</div>
                            <div class="pb-step-text">
                                <p class="pb-step-title"><?php echo esc_html__("Adjust settings", 'ays-popup-box'); ?></p>
                            </div>
                        </li>
                        <li class="pb-step-item">
                            <div class="pb-step-number">4</div>
                            <div class="pb-step-text">
                                <p class="pb-step-title"><?php echo esc_html__("Embed in a Page shortcode", 'ays-popup-box'); ?></p>
                            </div>
                        </li>
                    </ol>
                </div>
                <div class="pb-video-container">
                    <div class="pb-video-wrapper">
                        <div class="pb-create-course-youtube-video">
                            <div class="ays-pb-youtube-placeholder pb-youtube-placeholder" data-video-id="Ofk1mxUF-9g">
                                <img src="<?php echo esc_url( AYS_PB_ADMIN_URL .'/images/youtube/best-wordpress-popup-plugin.webp'); ?>" width="480" height="265">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Video Resources Section -->
    <section class="pb-video-resources">
        <div class="pb-video-resources-container">
            <div class="pb-video-resources-header">
                <h2 class="pb-video-resources-title"><?php echo esc_html__("Learn with Video", 'ays-popup-box'); ?></h2>
            </div>
            <div class="pb-video-cards">
                <div class="pb-video-row">
                    <a href="https://youtu.be/p13bj5_qNfY?si=DxFq67wRMPkoazoW" target="_blank" class="pb-video-card">
                        <div class="pb-video-card-content">
                            <div class="pb-video-card-icon">
                                <svg class="pb-play-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="6 3 20 12 6 21 6 3"></polygon>
                                </svg>
                            </div>
                            <div class="pb-video-card-text">
                                <div class="pb-video-card-header">
                                    <h3 class="pb-video-card-title"><?php echo esc_html__("Popup Box Plugin Overview", 'ays-popup-box'); ?></h3>
                                    <svg class="pb-external-link" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M15 3h6v6"></path>
                                        <path d="M10 14 21 3"></path>
                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                    </svg>
                                </div>
                                <p class="pb-video-card-description"><?php echo esc_html__("Tutorial on setting up and customizing popups in WordPress.", 'ays-popup-box'); ?></p>
                                <span class="pb-video-duration"><?php echo esc_html__("2 min", 'ays-popup-box'); ?></span>
                            </div>
                        </div>
                    </a>

                    <a href="https://youtu.be/UCk-qohzhIU?si=sHpVqN-Opmpipg4C" target="_blank" class="pb-video-card">
                        <div class="pb-video-card-content">
                            <div class="pb-video-card-icon">
                                <svg class="pb-play-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="6 3 20 12 6 21 6 3"></polygon>
                                </svg>
                            </div>
                            <div class="pb-video-card-text">
                                <div class="pb-video-card-header">
                                    <h3 class="pb-video-card-title"><?php echo esc_html__("Build a Popup on Page Load", 'ays-popup-box'); ?></h3>
                                    <svg class="pb-external-link" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M15 3h6v6"></path>
                                        <path d="M10 14 21 3"></path>
                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                    </svg>
                                </div>
                                <p class="pb-video-card-description"><?php echo esc_html__("Walkthrough of popup display rules and targeting options.", 'ays-popup-box'); ?></p>
                                <span class="pb-video-duration"><?php echo esc_html__("5 min", 'ays-popup-box'); ?></span>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="pb-video-row">
                    <a href="https://youtu.be/Phsw4q2mDmE?si=GL_FHo4dtsQdamNm" target="_blank" class="pb-video-card">
                        <div class="pb-video-card-content">
                            <div class="pb-video-card-icon">
                                <svg class="pb-play-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="6 3 20 12 6 21 6 3"></polygon>
                                </svg>
                            </div>
                            <div class="pb-video-card-text">
                                <div class="pb-video-card-header">
                                    <h3 class="pb-video-card-title"><?php echo esc_html__("How to Setup Popup Triggers", 'ays-popup-box'); ?></h3>
                                    <svg class="pb-external-link" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M15 3h6v6"></path>
                                        <path d="M10 14 21 3"></path>
                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                    </svg>
                                </div>
                                <p class="pb-video-card-description"><?php echo esc_html__("How to design effective popup layouts and content.", 'ays-popup-box'); ?></p>
                                <span class="pb-video-duration"><?php echo esc_html__("14 min", 'ays-popup-box'); ?></span>
                            </div>
                        </div>
                    </a>
                    <a href="https://youtu.be/_VEAGGzKe_g?si=2YlPL_olSt7cHRLT" target="_blank" class="pb-video-card">
                        <div class="pb-video-card-content">
                            <div class="pb-video-card-icon">
                                <svg class="pb-play-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="6 3 20 12 6 21 6 3"></polygon>
                                </svg>
                            </div>
                            <div class="pb-video-card-text">
                                <div class="pb-video-card-header">
                                    <h3 class="pb-video-card-title"><?php echo esc_html__("Create Popup in One Minute", 'ays-popup-box'); ?></h3>
                                    <svg class="pb-external-link" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M15 3h6v6"></path>
                                        <path d="M10 14 21 3"></path>
                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                    </svg>
                                </div>
                                <p class="pb-video-card-description"><?php echo esc_html__("See how to create a full WordPress popup in under one minute.", 'ays-popup-box'); ?></p>
                                <span class="pb-video-duration"><?php echo esc_html__("1 min", 'ays-popup-box'); ?></span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Help Section -->
    <section id="pb-help-types" class="pb-help-demos-section">
        <div class="pb-help-container">
            <div class="pb-help-max-width">
                <div class="pb-help-header">
                    <h2 class="pb-help-title"><?php echo esc_html__("Support & Resources", 'ays-popup-box'); ?></h2>
                </div>
                <div class="pb-help-grid">
                    <div class="pb-help-card">
                        <div class="pb-help-card-header">
                            <div class="pb-help-icon-container">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="pb-help-icon">
                                    <path d="M12 7v14"></path>
                                    <path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"></path>
                                </svg>
                            </div>
                            <h3 class="pb-help-card-title"><?php echo esc_html__("Documentation", 'ays-popup-box'); ?></h3>
                        </div>
                        <div class="pb-help-card-content">
                            <p class="pb-help-card-description"><?php echo esc_html__("Access comprehensive guides and tutorials to master Popup Box.", 'ays-popup-box'); ?></p>
                            <a href="https://popup-plugin.com/docs/" class="pb-help-button" target="_blank">
                                <?php echo esc_html__("View Docs", 'ays-popup-box'); ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="pb-help-button-icon">
                                    <path d="M15 3h6v6"></path>
                                    <path d="M10 14 21 3"></path>
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                    <div class="pb-help-card">
                        <div class="pb-help-card-header">
                            <div class="pb-help-icon-container">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="pb-help-icon">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                            </div>
                            <h3 class="pb-help-card-title"><?php echo esc_html__("Community Forum", 'ays-popup-box'); ?></h3>
                        </div>
                        <div class="pb-help-card-content">
                            <p class="pb-help-card-description"><?php echo esc_html__("Join discussions with other educators and get help from the community.", 'ays-popup-box'); ?></p>
                            <a href="https://wordpress.org/support/plugin/ays-popup-box/" class="pb-help-button" target="_blank">
                                <?php echo esc_html__("Join Forum", 'ays-popup-box'); ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="pb-help-button-icon">
                                    <path d="M15 3h6v6"></path>
                                    <path d="M10 14 21 3"></path>
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                    <div class="pb-help-card">
                        <div class="pb-help-card-header">
                            <div class="pb-help-icon-container">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="pb-help-icon">
                                    <path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"></path>
                                </svg>
                            </div>
                            <h3 class="pb-help-card-title"><?php echo esc_html__("Contact Support", 'ays-popup-box'); ?></h3>
                        </div>
                        <div class="pb-help-card-content">
                            <p class="pb-help-card-description"><?php echo esc_html__("Get direct help from our support team for technical issues.", 'ays-popup-box'); ?></p>
                            <a href="https://popup-plugin.com/contact-us/" class="pb-help-button" target="_blank">
                                <?php echo esc_html__("Get Help", 'ays-popup-box'); ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="pb-help-button-icon">
                                    <path d="M15 3h6v6"></path>
                                    <path d="M10 14 21 3"></path>
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                    <div class="pb-help-card">
                        <div class="pb-help-card-header">
                            <div class="pb-help-icon-container">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="pb-help-icon">
                                    <rect width="20" height="14" x="2" y="3" rx="2"></rect>
                                    <line x1="8" x2="16" y1="21" y2="21"></line>
                                    <line x1="12" x2="12" y1="17" y2="21"></line>
                                </svg>
                            </div>
                            <h3 class="pb-help-card-title"><?php echo esc_html__("Demo", 'ays-popup-box'); ?></h3>
                        </div>
                        <div class="pb-help-card-content">
                            <p class="pb-help-card-description"><?php echo esc_html__("See Popup Box in action with our interactive demo and examples.", 'ays-popup-box'); ?></p>
                            <a href="https://demo.popup-plugin.com/" class="pb-help-button" target="_blank">
                                <?php echo esc_html__("Try Demo", 'ays-popup-box'); ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="pb-help-button-icon">
                                    <path d="M15 3h6v6"></path>
                                    <path d="M10 14 21 3"></path>
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section id="pb-review-settings" class="pb-review-settings-section">
        <div class="pb-review-settings-container">
            <p style="font-size:13px;text-align:center;font-style:italic;">                
                <span><?php echo esc_html__( "If you love our plugin, please do big favor and rate us on WordPress.org", 'ays-popup-box'); ?></span> 
                <a target="_blank" class="ays-rated-link" href='https://wordpress.org/support/plugin/ays-popup-box/reviews/'>
                    <span class="ays-dashicons ays-dashicons-star-empty"></span>
                    <span class="ays-dashicons ays-dashicons-star-empty"></span>
                    <span class="ays-dashicons ays-dashicons-star-empty"></span>
                    <span class="ays-dashicons ays-dashicons-star-empty"></span>
                    <span class="ays-dashicons ays-dashicons-star-empty"></span>
                </a>
            </p>
        </div>
    </section>
</div>

