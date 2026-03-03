<?php
//! set var $appDataFile=?? (ie: "composer|appinfo.json") in the calling php, before "include" statement.
isset($dev_msg) ? null: $dev_msg='';
(!isset($appDataFile))
   ? $dev_msg .= "<script>console.info('❗No APPINFO file set')</script>"
   : loadEnvs($appDataFile, "APP_");

#$app_env = $_SERVER['ENV_KEY'] ??
$app_env = getenv('APP_ENV') ; //is there "runtime" .env.??? file
if ($app_env != null) {
   loadEnvs(".env.$app_env");
   $dev_msg .= '<script>console.info("ℹ️'.$app_env.' ENV selected")</script>';
} else {
   loadEnvs(".env");
   $dev_msg .= "<script>console.info('ℹ️Default ENV selected')</script>";
}

  // default (is to OVERWRITE appEnvFile variables)

# if(file_exists(__DIR__.'/'.$env_file))
# {
#     #require_once(__DIR__ . '/vendor/autoload.php');
#     (Dotenv\Dotenv::createUnsafeImmutable(__DIR__,$file))->load();
#     error_log("Environment loaded from ".$env_file);
# } else {
#     error_log("*WARNING* environment file not found: ".$env_file);
# }

#--------------------------------------------------

function loadEnvs($env_file, $Pfx = ""): void // loads any env's if available, returns nothing.
{
   #echo "<script>alert('Check ($env_file)');</script>";
   if (file_exists("./$env_file")) {
      $path = '.';
   } else if (file_exists("../$env_file")) {
      $path = '../';
   } else if (file_exists("../../$env_file")) {
      $path = '../../';
   } else if (file_exists($_SERVER['HTTP_HOST'] . "/$env_file")) {
      $path = $_SERVER['HTTP_HOST'];
   }

   if (isset($path)) {
      (new getDotEnvs("$path/$env_file"))->load($Pfx);
      error_log("Environment loaded from " . $env_file);
   } else {
      error_log("*WARNING* environment file not found: " . $env_file);
   }
};

class getDotEnvs
{
   /* The directory where the .env file can be located.
    * @var string
    */
   protected $path;

   public function __construct(string $path)
   {
      try {
         if (!file_exists($path)) {
            throw new InvalidArgumentException(sprintf('%s does not exist: ', $path));
         }
         $this->path = $path;
      } catch (\Throwable $e) {
         $dev_msg .= "<script>alert('$e->getMessage()')</script>";
      }
   }

   public function load($Pfx): void
   {
      try {
         if (!is_readable($this->path)) {
            throw new RuntimeException(sprintf('%s file is not readable', $this->path));
         }

         $lines = file($this->path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
         $prefix = '';
         $prefixAdd = '';
         foreach ($lines as $line) {
            $line = trim($line);
            #json format: "name": "Victor Wright",
            if (strpos($line, '#') === 0) {
               continue;
            };
            if (strpos($line, '{') === 0) { //first line               
               continue;
            };
            if (strpos($line, '{') === Strlen($line) - 1) { //opening {
               $pattern = '/"(.*?)"/';
               preg_match($pattern, $line, $matches);
               if (isset($matches[1])) {
                  $prefix .=  $matches[1] . "_";
               };
               continue;
            };
            if (strpos($line, '[') === Strlen($line) - 1) { // line is a []               
               $pattern = '/"(.*?)"/';
               preg_match($pattern, $line, $matches);
               if (isset($matches[1])) {
                  $prefixAdd .=  $matches[1] . "_";
               };
               continue;
            };
            if (strpos($line, ']') >= Strlen($line) - 2) { // line is a []
               $prefixAdd = "";
               continue;
            };
            if (strpos($line, '}') >= Strlen($line) - 2) { // closing {} or last line
               $prefix = "";
               continue;
            };
            $line = str_ireplace("://", "(//)", $line); //* temp catch http://
            $line = preg_replace('/([A-Z])(:)([\d])/i', '${1}§${3}', $line);
            $line = str_ireplace(":", "=", $line);
            $line = str_ireplace("§", ":", $line);
            $line = str_ireplace("(//)", "://", $line); //* put it back to http://
            list($name, $value) = explode('=', $line, 2); //! will not work for a value which includes "="
            $name = strtoupper($Pfx . $prefix . $prefixAdd . trim(trim($name), '"'));
            $value = trim(trim($value), '",');

            #var_dump($name . "   " . $value . '<br>');

            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
               putenv(sprintf('%s=%s', $name, $value));
               //    $_ENV[$name] = $value;
               //    $_SERVER[$name] = $value;               
               if (!defined($name)) define($name, $value);
            }
         }
         error_log("Environment loaded.");
      } catch (\Throwable $e) {
         $dev_msg .= "<script>alert('$e->getMessage()')</script>";
         error_log("*WARNING* environment file not found.");
      }
   }
}
