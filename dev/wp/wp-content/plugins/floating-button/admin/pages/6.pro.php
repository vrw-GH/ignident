<?php
/**
 * Page Name: Upgrade to Pro
 *
 */

use FloatingButton\WOWP_Plugin;

$features = [
        'core'             => [
                [
                        'title' => __( 'Attract Animation', 'floating-button' ),
                        'desc'  => __( 'Customize the main button with attention-grabbing animations to engage visitors',
                                'floating-button' ),
                ],
                [
                        'title' => __( 'Open Animations', 'floating-button' ),
                        'desc'  => __( 'Choose from 8 stunning animations to reveal sub-buttons, adding a dynamic touch to your floating buttons ',
                                'floating-button' ),
                ],

                [
                        'title' => __( 'Deeper Analytics', 'floating-button' ),
                        'desc'  => __( ' Empower with a deeper understanding of how users interact with your menu! This powerful feature allows you to track user clicks on specific menu items within Google Analytics. ',
                                'floating-button' ),
                ],


        ],
        'functional-links' => [
                [
                        'title' => __( 'Translate', 'floating-button' ),
                        'desc'  => __( 'Empower your visitors to translate your website content in real-time. Integrate this link type to break down language barriers and cater to a global audience.',
                                'floating-button' ),
                    'demo' => 'https://demo.wow-estore.com/floating-button-pro/translate/',
                ],
                [
                        'title' => __( 'Social Sharing', 'floating-button' ),
                        'desc'  => __( 'Boost your website\'s reach by incorporating a "Share" link. Choose from a staggering 29 different social media services, allowing users to effortlessly share your content across their preferred platforms.',
                                'floating-button' ),
                    'demo' => 'https://demo.wow-estore.com/floating-button-pro/share/'
                ],
                [
                        'title' => __( 'Next/Previous Post', 'floating-button' ),
                        'desc'  => __( 'Simplify post navigation for readers. These link types automatically direct users to the next or previous post within the current category, keeping them engaged and exploring related content. ',
                                'floating-button' ),
                ],
                [
                        'title' => __( 'Menu', 'floating-button' ),
                        'desc'  => __( 'Display a specific menu upon clicking a button, selectable from the menus created on the Menu page.',
                                'floating-button' ),
                    'demo' => 'https://demo.wow-estore.com/floating-button-pro/actions/'
                ],
                [
                        'title' => __( 'Forced Download', 'floating-button' ),
                        'desc'  => __( 'Offer downloadable resources like brochures, ebooks, or software directly through your floating menus. This eliminates the need for users to navigate to separate download pages. ',
                                'floating-button' ),
                ],
                [
                        'title' => __( 'Scroll To Top/Bottom', 'floating-button' ),
                        'desc'  => __( 'Provide users with convenient links to instantly scroll to the top or bottom of your webpage. This is particularly helpful for long pages or content-heavy sections.',
                                'floating-button' ),
                        'demo' => 'https://demo.wow-estore.com/floating-button-pro/actions/'
                ],
                [
                        'title' => __( 'Smooth Scroll', 'floating-button' ),
                        'desc'  => __( 'Enhance user experience with smooth scrolling animations. This link type ensures a visually pleasing and seamless transition when users navigate to different sections of your webpage.',
                                'floating-button' ),
                    'demo' => 'https://demo.wow-estore.com/floating-button-pro/scrolling/'
                ],
                [
                        'title' => __( 'Print', 'floating-button' ),
                        'desc'  => __( 'With a single click on the Print link, users can initiate the built-in printing function of their web browser. No more cumbersome text selection or manual copying.',
                                'floating-button' ),
                        'demo' => 'https://demo.wow-estore.com/floating-button-pro/actions/'
                ],
                [
                        'title' => __( 'Email', 'floating-button' ),
                        'desc'  => __( 'Integrate an Email link into your floating menu, allowing users to effortlessly initiate email communication. This streamlines the process for users who may have questions, require additional information, or want to express feedback.',
                                'floating-button' ),
                ],
                [
                        'title' => __( 'One-Click Calling', 'floating-button' ),
                        'desc'  => __( 'Provide a Telephone link within your floating menu, enabling users to directly initiate a phone call to your business with a single click. This is particularly valuable for websites with a strong focus on customer service or those offering phone consultations.',
                                'floating-button' ),
                ],
                [
                        'title' => __( 'User Links', 'floating-button' ),
                        'desc'  => __( 'This includes Login links for effortless account access, Logout links for secure sign-outs, Registration links for simplified account creation, and Password Recovery links for stress-free password retrieval, all readily available within the menu, empowering users to manage their accounts and interact with your website seamlessly. ',
                                'floating-button' ),
                ],
        ],

        'icons'              => [
                [
                        'title' => __( 'Custom Icons', 'floating-button' ),
                        'desc'  => __( 'Break free from the limitations of pre-defined icon libraries. Custom icons allow you to utilize any image or icon that complements your website\'s design.',
                                'floating-button' ),
                ],
                [
                        'title' => __( 'Icon Settings', 'floating-button' ),
                        'desc'  => __( 'Rotate and flip icons to enhance visual appeal.', 'floating-button' ),
                ],
                [
                        'title' => __( 'Emoji and Letter', 'floating-button' ),
                        'desc'  => __( ' Sometimes, a simple emoji or letter can be the most effective way to represent a menu item. Float Menu Pro allows you to utilize emojis or individual letters as icons, offering a playful and informal touch to your menus.',
                                'floating-button' ),
                ],
        ],
        'visibility-control' => [
                [
                        'title' => __( 'Hiding/Showing', 'floating-button' ),
                        'desc'  => __( 'Allows you to control the visibility of your floating buttons based on the user\'s scroll position on the webpage.',
                                'floating-button' ),
                    'demo' => 'demo.wow-estore.com/floating-button-pro/show-after-position/'
                ],
                [
                        'title' => __( 'Activate by URL', 'floating-button' ),
                        'desc'  => __( 'Target specific pages based on URL parameters (e.g., show a floating menu only on a page with URL parameter)',
                                'floating-button' ),
                ],
                [
                        'title' => __( 'Activate by Referrer URL', 'floating-button' ),
                        'desc'  => __( 'To display different floating menus for visitors arriving from specific websites.',
                                'floating-button' ),
                ],
                [
                        'title' => __( 'Display Rules', 'floating-button' ),
                        'desc'  => __( 'Control exactly where your popup appear using page types, post categories/tags, author pages, taxonomies and date archives.',
                                'floating-button' ),
                ],
                [
                        'title' => __( 'Devices Rules', 'floating-button' ),
                        'desc'  => __( 'Ensure optimal menu visibility across all devices with options to hide/remove on specific screen sizes.',
                                'floating-button' ),
                ],
                [
                        'title' => __( 'User Role Permissions', 'floating-button' ),
                        'desc'  => __( 'Define which user roles (e.g., Administrator, Editor, Author) have the ability to see the menu items. This can be helpful for displaying internal menus relevant only to website administrators or managing menus for specific user groups.',
                                'floating-button' ),
                ],
                [
                        'title' => __( 'Multilingual Support', 'floating-button' ),
                        'desc'  => __( 'For websites catering to a global audience, Float Menu Pro allows you to restrict menu visibility to specific languages. This ensures users only see menus relevant to their chosen language setting. ',
                                'floating-button' ),
                ],
                [
                        'title' => __( 'Scheduling', 'floating-button' ),
                        'desc'  => __( 'Schedule menu appearances based on specific days, times, and dates. This allows you to promote temporary events or campaigns without cluttering your website permanently. ',
                                'floating-button' ),
                ],
                [
                        'title' => __( 'Browser Compatibility', 'floating-button' ),
                        'desc'  => __( 'Ensure your menus display correctly across a wide range of browsers. If necessary, you can choose to hide menus for specific browsers to address compatibility issues with outdated software versions.',
                                'floating-button' ),
                ],
        ],
];

?>

    <div class="wpie-block-tool is-white">

        <div class="wowp-pro-upgrade">
            <div>
                <h3>Unlock PRO Features</h3>
                <p>Upgrade to Floating Button Pro and get advanced features like</p>
                <div class="buttons">
                    <a href="<?php
                    echo esc_url( WOWP_Plugin::info( 'pro' ) ); ?>" target="_blank" class="button button-primary">Get
                        Floating Button Pro  </a>
                    <a href="<?php
                    echo esc_url( WOWP_Plugin::info( 'demo' ) ); ?>" target="_blank" class="button-link">Try
                        Demo <span>→</span></a>
                </div>
            </div>
            <dl class="wowp-pro__profits">
                <div class="wowp-pro__profit">
                    <dt><span class="wpie-icon wpie_icon-money-time"></span>No Yearly Fees</dt>
                    <dd>One-time payment. Use it forever.</dd>
                </div>
                <div class="wowp-pro__profit">
                    <dt><span class="wpie-icon wpie_icon-refund"></span>14-Day Money-Back Guarantee</dt>
                    <dd>Try it risk-free. Get a full refund if you are not satisfied.</dd>
                </div>
                <div class="wowp-pro__profit">
                    <dt><span class="wpie-icon wpie_icon-cloud-data-sync"></span>Lifetime Free Updates</dt>
                    <dd>Always stay up to date for no extra cost.</dd>
                </div>
                <div class="wowp-pro__profit">
                    <dt><span class="wpie-icon wpie_icon-customer-support"></span>Priority Support</dt>
                    <dd>Fast, friendly, and expert help whenever you need it.</dd>
                </div>
            </dl>

        </div>

        <div class="wowp-pro-features">


            <h3 class="wpie-tabs">
                <?php
                $i = 0;
                foreach ( $features as $key => $feature ) {
                    $class = ( $i === 0 ) ? ' selected' : '';
                    $i ++;
                    $name = str_replace( '-', ' ', $key );
                    echo '<label class="wpie-tab-label' . esc_attr( $class ) . '" for="features-' . absint( $i ) . '">' . esc_html( ucwords( $name ) ) . '</label>';
                } ?>
            </h3>

            <div class="wpie-tabs-contents">

                <?php
                $i = 0;
                foreach ( $features as $key => $feature ) {
                    $i ++;
                    echo '<input type="radio" class="wpie-tab-toggle" name="features" value="1" id="features-' . absint( $i ) . '" ' . checked( 1,
                                    $i, false ) . '>';
                    echo '<div class="wpie-tab-content">';
                    echo '<dl>';
                    foreach ( $feature as $value ) {
                        echo '<div>';
                        echo '<dt>' . esc_html( $value['title'] );

                        if ( isset( $value['link'] ) ) {
                            echo '<a href="' . esc_url( $value['link'] ) . '" target="_blank">How It Works <span class="wpie-icon wpie_icon-chevron-down"></span></a> ';
                        }
                        if ( isset( $value['demo'] ) ) {
                            echo '<a href="' . esc_url( $value['demo'] ) . '" target="_blank">Try the Demo <span class="wpie-icon wpie_icon-chevron-down"></span></a>';
                        }
                        echo '</dt>';
                        echo '<dd>' . esc_html( $value['desc'] ) . '</dd>';
                        echo '</div>';
                    }
                    echo '</dl>';
                    echo '</div>';
                } ?>


            </div>

        </div>

    </div>
<?php
