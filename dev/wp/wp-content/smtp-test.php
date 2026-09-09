<?php
echo __DIR__ . "<br>";
require_once __DIR__ . '/../wp-load.php';

$result = wp_mail(
    'victor.wright@outlook.de',
    'SMTP Test',
    'If you received this, SMTP is working.'
);
print_r('Result ('.$result.')');

# vw testing!-----------
if (WP_DEBUG_LOG) {
   file_put_contents(
       WP_CONTENT_DIR . '/debug-email-utility.log',
       PHP_EOL . 'Debug:'.WP_DEBUG_LOG . 'Result = [' .$result . '] *:' . print_r(SMTP_HOST.' '.SMTP_USER,true),
       FILE_APPEND
   );
}
# ----------------------

#var_dump(SMTP_HOST,SMTP_USER,"!");
#var_dump($result);
