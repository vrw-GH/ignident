<?php
/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 *
 * This file acts as an optimized endpoint file for the Koko Analytics plugin.
 */

// path to pageviews.php file in uploads directory
define('KOKO_ANALYTICS_BUFFER_FILE', '/homepages/7/d548269671/htdocs/clickandbuilds/IgnidentDentalHealthcareProducts/wp-content/uploads/pageviews.php');

// path to functions.php file in Koko Analytics plugin directory
require '/homepages/7/d548269671/htdocs/clickandbuilds/IgnidentDentalHealthcareProducts/wp-content/plugins/koko-analytics/src/functions.php';

// function call to collect the request data
KokoAnalytics\collect_request();