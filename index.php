<?php

if (file_exists('vendor/autoload.php'))
{
    require_once('vendor/autoload.php');
}

// CodeIgniter function: 
function get_dir_file_info($source_dir, $top_level_only = TRUE, $_recursion = FALSE)
{
    static $_filedata = array();
    $relative_path = $source_dir;

    if ($fp = @opendir($source_dir))
    {
        // reset the array and make sure $source_dir has a trailing slash on the initial call
        if ($_recursion === FALSE)
        {
            $_filedata = array();
            $source_dir = rtrim(realpath($source_dir), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        }

        // Used to be foreach (scandir($source_dir, 1) as $file), but scandir() is simply not as fast
        while (FALSE !== ($file = readdir($fp)))
        {
            if (is_dir($source_dir.$file) && $file[0] !== '.' && $top_level_only === FALSE)
            {
                get_dir_file_info($source_dir.$file.DIRECTORY_SEPARATOR, $top_level_only, TRUE);
            }
            elseif ($file[0] !== '.')
            {
                $_filedata[$file] = get_file_info($source_dir.$file);
                $_filedata[$file]['relative_path'] = $relative_path;
            }
        }

        closedir($fp);
        return $_filedata;
    }

    return FALSE;
}

// CodeIgniter function:
function get_file_info($file, $returned_values = array('name', 'server_path', 'size', 'date'))
{
    if ( ! file_exists($file))
    {
        return FALSE;
    }

    if (is_string($returned_values))
    {
        $returned_values = explode(',', $returned_values);
    }

    foreach ($returned_values as $key)
    {
        switch ($key)
        {
            case 'name':
                $fileinfo['name'] = basename($file);
                break;
            case 'server_path':
                $fileinfo['server_path'] = $file;
                break;
            case 'size':
                $fileinfo['size'] = filesize($file);
                break;
            case 'date':
                $fileinfo['date'] = filemtime($file);
                break;
            case 'readable':
                $fileinfo['readable'] = is_readable($file);
                break;
            case 'writable':
                $fileinfo['writable'] = is_really_writable($file);
                break;
            case 'executable':
                $fileinfo['executable'] = is_executable($file);
                break;
            case 'fileperms':
                $fileinfo['fileperms'] = fileperms($file);
                break;
        }
    }

    return $fileinfo;
}


function build_sorter($key)
{
    return function ($a, $b) use ($key) {
        return strnatcasecmp($a[$key], $b[$key]);
    };
}


// -------------------------------------------------
// -------------------------------------------------
// -------------------------------------------------

define ('CONST_MARKDOWN_CLASS_MISSING', '<div style="background-color: red">To display Markdown files properly, install the <a href="https://github.com/cebe/markdown" style="color: black; font-weight: bold;">cebe/markdown library.</a><br/><br/>To install, run (in the root directory): <br/><br/><span style="font-weight: bold; color: black;">composer require cebe/markdown</span><br/><br/></div>');
define ('CONST_PROJECT_NAME', 'PHP directory explorer');
define ('CONST_PROJECT_VERSION', 'v2.1');

$html_code = '
<!DOCTYPE html>
<html>
<head>
<title>##HTML_TITLE##</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>

body {
  font-size: 14px;
  font-family: "Consolas", "Arial";
  text-shadow: 0 2px 2px #b6701e;
  color: white;
  background-color: #111;
  overflow: hidden;
}

section {
  color: #fff;
  text-align: left;
}

div {
  height: 100%;
}

article {
  position: absolute;
  top: 0px;
  left: 50%;
  transform: translate(-50%, 0%);
  width: 100%;
  padding: 0px;
}

h1 {
  font-size: 1.75rem;
  margin: 0 0 0.75rem 0;
}

a, a:focus {
  color: #e67e22;
}

a:hover {
  color: #fba508;
}

/* Pattern styles */
.container {
  display: table;
  width: 100%;
}

.left-half {
  background-color: #111;
  position: absolute;
  left: 0px;
  width: 20%;
  overflow-y: auto;
}

.right-half {
  background-color: #222;
  position: absolute;
  right: 0px;
  width: 80%;
  overflow-y: auto;
}

.padded {
    padding: 20px;
}

</style>

</head>
<body>

<section class="container">
  <div class="left-half">
    <article>
        <div class="padded">
            ##SEND_WARNING##
            <div style="min-height:100px;">
            <h1 style="color:#fba508;">##LEFT_TITLE##</h1>
            </div>
            <br/>
##LEFT_AREA##
        </div>
    </article>
  </div>
  <div class="right-half">
    <article>
        <div class="padded">
##RIGHT_AREA##
        </div>
    </article>
  </div>
</section>
     
</body>
</html> 
';

$HTML_TITLE = CONST_PROJECT_NAME;
$WARNING_TEXT = '';
$LEFT_TITLE = 'BROWSE';
$LEFT_AREA = '';
$RIGHT_AREA = '';

// var_dump(get_declared_classes());
// exit;

// echo $template;
// echo '<pre>';

$root_path = __DIR__;
if (isset($_GET['path']))
{
    $requested_path = $_GET['path'];
    $requested_full_path = $root_path . $requested_path;
    $HTML_TITLE = $requested_path . ' - ' . $HTML_TITLE;
}
else 
{
    $requested_path = '';
    $requested_full_path = $root_path;
}

if ($requested_path === '/')
{
    header('Location: /');
    exit;
}


// -------------------------------------------------
// LEFT AREA PROCESSING:
if (is_dir($requested_full_path))
{
    $LEFT_TITLE = ($requested_path === '') ? 'BROWSE' : $requested_path;
    
    $directory_objects = get_dir_file_info($requested_full_path, TRUE, FALSE);
    usort($directory_objects, build_sorter('name'));
    
    foreach ($directory_objects as $directory_object)
    {
        $LEFT_AREA .= '<a href="?path=' . $requested_path . '/' . basename($directory_object['name']) . '">' . basename($directory_object['name']) . '</a><br />';
    }

    if ($requested_full_path !== $root_path && $requested_path !== '/')
    {
        $LEFT_AREA = '<a href="?path=' . str_replace('\\','/', dirname($requested_path)) . '">..</a><br />' . $LEFT_AREA;
    }
}
elseif (is_file($requested_full_path))
{
    $LEFT_TITLE = (dirname($requested_path) === '' || str_replace('\\','/', dirname($requested_path)) === '/') ? 'BROWSE' : dirname($requested_path);
    
    $directory_objects = get_dir_file_info(dirname($requested_full_path), TRUE, FALSE);
    usort($directory_objects, build_sorter('name'));
    
    foreach ($directory_objects as $directory_object)
    {
        $print_requested_path = (str_replace('\\','/', dirname($requested_path)) === '/') ? str_replace('\\','/', dirname($requested_path)) : str_replace('\\','/', dirname($requested_path)) . '/';
        $LEFT_AREA .= '<a href="?path=' . $print_requested_path . basename($directory_object['name']) . '">' . basename($directory_object['name']) . '</a><br />';
    }

    if ($requested_full_path !== $root_path && str_replace('\\','/', dirname($requested_path)) !== '/')
    {
        $LEFT_AREA = '<a href="?path=' . str_replace('\\','/', dirname(dirname($requested_path))) . '">..</a><br />' . $LEFT_AREA;
    }
}
else
{
    // misspelled URL, redirect to root
    header('Location: /');
    exit;
}

$html_code = preg_replace('/##LEFT_TITLE##/', $LEFT_TITLE, $html_code);
$html_code = preg_replace('/##LEFT_AREA##/', $LEFT_AREA, $html_code);


// -------------------------------------------------
// RIGHT AREA PROCESSING:
if (is_file($requested_full_path))
{
    $RIGHT_AREA = file_get_contents($requested_full_path);

    if (mb_substr(mb_strtolower($requested_path), mb_strlen($requested_path) - 2, mb_strlen($requested_path)) === 'md')
    {
        if (class_exists('\cebe\markdown\Markdown'))
        {
            $parser = new \cebe\markdown\Markdown();
            $RIGHT_AREA = '<pre>' . "\n" . $parser->parse($RIGHT_AREA) . "\n" . '</pre>';
        }
        else
        {
            $WARNING_TEXT = CONST_MARKDOWN_CLASS_MISSING;
        }        
    }
    else
    {
        $RIGHT_AREA = '<pre>' . "\n" . '<p>' . htmlspecialchars($RIGHT_AREA) . '</p>' . "\n" . '</pre>';
    }
}
elseif (is_dir($requested_full_path))
{
    $RIGHT_AREA = '<pre></pre>';
}

$html_code = preg_replace('/##RIGHT_AREA##/', $RIGHT_AREA, $html_code);

$html_code = preg_replace('/##SEND_WARNING##/', $WARNING_TEXT, $html_code);
$html_code = preg_replace('/##HTML_TITLE##/', $HTML_TITLE, $html_code);

// -------------------------------------------------
// FINALLY PRINT:

echo $html_code;
