<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>SMTP Diagnostic Test</h2>\n";

// 1. Check PHP Extensions
echo "<h3>1. PHP Extensions</h3>\n";
echo "OpenSSL: " . (extension_loaded('openssl') ? '✓ Enabled' : '✗ Disabled') . "<br>\n";
echo "Sockets: " . (extension_loaded('sockets') ? '✓ Enabled' : '✗ Disabled') . "<br>\n";
echo "CURL: " . (extension_loaded('curl') ? '✓ Enabled' : '✗ Disabled') . "<br>\n";

// 2. Check if .env file exists
echo "<h3>2. Environment File Check</h3>\n";
if (file_exists('.env')) {
    echo "✓ .env file found<br>\n";
    
    // Read .env file manually
    $env_content = file_get_contents('.env');
    $lines = explode("\n", $env_content);
    $env_vars = [];
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $env_vars[trim($key)] = trim($value);
        }
    }
    
    echo "SMTP_HOST: " . (isset($env_vars['SMTP_HOST']) ? $env_vars['SMTP_HOST'] : 'NOT SET') . "<br>\n";
    echo "SMTP_PORT: " . (isset($env_vars['SMTP_PORT']) ? $env_vars['SMTP_PORT'] : 'NOT SET') . "<br>\n";
    echo "SMTP_USERNAME: " . (isset($env_vars['SMTP_USERNAME']) ? $env_vars['SMTP_USERNAME'] : 'NOT SET') . "<br>\n";
    echo "SMTP_PASSWORD: " . (isset($env_vars['SMTP_PASSWORD']) ? 'SET (length: ' . strlen($env_vars['SMTP_PASSWORD']) . ')' : 'NOT SET') . "<br>\n";
    echo "SMTP_ENCRYPTION: " . (isset($env_vars['SMTP_ENCRYPTION']) ? $env_vars['SMTP_ENCRYPTION'] : 'NOT SET') . "<br>\n";
} else {
    echo "✗ .env file not found<br>\n";
}

// 3. Test Network Connectivity
echo "<h3>3. Network Connectivity Test</h3>\n";
$smtp_hosts = [
    'smtp.gmail.com' => [587, 465, 25],
    'smtp.outlook.com' => [587],
    'smtp.yahoo.com' => [587, 465]
];

foreach ($smtp_hosts as $host => $ports) {
    echo "<strong>Testing $host:</strong><br>\n";
    foreach ($ports as $port) {
        echo "&nbsp;&nbsp;Port $port: ";
        $connection = @fsockopen($host, $port, $errno, $errstr, 5);
        if ($connection) {
            echo "✓ Connected<br>\n";
            fclose($connection);
        } else {
            echo "✗ Failed ($errstr)<br>\n";
        }
    }
}

// 4. Check PHPMailer
echo "<h3>4. PHPMailer Check</h3>\n";
if (file_exists('vendor/autoload.php')) {
    echo "✓ Composer autoloader found<br>\n";
    require_once 'vendor/autoload.php';
    
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        echo "✓ PHPMailer class available<br>\n";
        
        // Test basic PHPMailer initialization
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            echo "✓ PHPMailer object created successfully<br>\n";
        } catch (Exception $e) {
            echo "✗ PHPMailer creation failed: " . $e->getMessage() . "<br>\n";
        }
    } else {
        echo "✗ PHPMailer class not found<br>\n";
    }
} else {
    echo "✗ Composer autoloader not found. Please run 'composer install'<br>\n";
}

// 5. Simple SMTP Test (if everything looks good so far)
if (isset($env_vars) && isset($env_vars['SMTP_HOST']) && class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    echo "<h3>5. SMTP Authentication Test</h3>\n";
    
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = $env_vars['SMTP_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $env_vars['SMTP_USERNAME'];
        $mail->Password = $env_vars['SMTP_PASSWORD'];
        $mail->SMTPSecure = $env_vars['SMTP_ENCRYPTION'];
        $mail->Port = $env_vars['SMTP_PORT'];
        
        // Enable debug output
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = function($str, $level) {
            echo nl2br(htmlspecialchars($str));
        };
        
        // Test connection only (don't send email)
        echo "Testing SMTP connection...<br>\n";
        echo "<div style='background:#f0f0f0; padding:10px; margin:10px 0; font-family:monospace; font-size:12px;'>\n";
        
        $mail->SMTPConnect();
        echo "</div>\n";
        echo "✓ SMTP connection successful!<br>\n";
        
        $mail->smtpClose();
        
    } catch (Exception $e) {
        echo "</div>\n";
        echo "✗ SMTP connection failed: " . $e->getMessage() . "<br>\n";
    }
}

// 6. System Information
echo "<h3>6. System Information</h3>\n";
echo "PHP Version: " . phpversion() . "<br>\n";
echo "Operating System: " . php_uname() . "<br>\n";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "<br>\n";

?>