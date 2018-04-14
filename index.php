<?php

if (file_exists('vendor/autoload.php'))
{
    require_once('vendor/autoload.php');
}

// initialize classes:

if (class_exists('Websemantics\FileIcons\FileIcons'))
{
    $web_icons = new Websemantics\FileIcons\FileIcons();
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

function detect_file_type($requested_path)
{
    // $pic_file_extensions = array('png', 'jpg', 'jpeg', 'bmp');
    // $markdown_file_extensions = array('md');
    $pic_file_extensions = explode('|', CONST_PICTURE_EXTENSIONS);
    $markdown_file_extensions = explode('|', CONST_MARKDOWN_EXTENSIONS);
    
    //check for image files first:
    foreach ($pic_file_extensions as $extension)
    {
        // echo $extension;
        
        if (mb_substr(mb_strtolower($requested_path), mb_strlen($requested_path) - mb_strlen($extension), mb_strlen($requested_path)) === $extension)
        {
            return 'PICTURE';
        }
    }

    //check for markdown files:
    foreach ($markdown_file_extensions as $extension)
    {
        // echo $extension;

        if (mb_substr(mb_strtolower($requested_path), mb_strlen($requested_path) - mb_strlen($extension), mb_strlen($requested_path)) === $extension)
        {
            return 'MARKDOWN';
        }
    } 
}


function file_size_human_friendly($file_size)
{
    if ($file_size < 1024) {
        $ret_val = $file_size . ' Bytes';
    }        
    elseif ($file_size < 1048576) {
        $ret_val = round($file_size/1024, 1) . ' KB';
    }        
    else {
        $ret_val = round($file_size/1048576, 1) . ' MB';
    }
    //
    return $ret_val;
}

// -------------------------------------------------
// -------------------------------------------------
// -------------------------------------------------

define ('CONST_MARKDOWN_CLASS_MISSING', '<div style="background-color: red">To display Markdown files properly, install the <a href="https://github.com/cebe/markdown" style="color: black; font-weight: bold;">cebe/markdown library.</a><br/><br/>To install, run (in the root directory): <br/><br/><span style="font-weight: bold; color: black;">composer require cebe/markdown</span><br/><br/></div>');
define ('CONST_PROJECT_NAME', 'PHP directory explorer');
define ('CONST_PROJECT_VERSION', 'v2.1');
define ('CONST_PICTURE_EXTENSIONS', 'png|jpg|jpeg|bmp');
define ('CONST_MARKDOWN_EXTENSIONS', 'md');

$websemantics_css = (class_exists('Websemantics\FileIcons\FileIcons')) ? $web_icons::includeCss() : "";

$html_code = '
<!DOCTYPE html>
<html>
<head>
<title>##HTML_TITLE##</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
' . $websemantics_css . '
<style>

body {
  font-family: "Roboto Mono", "Roboto", "lucida grande", tahoma, verdana, arial, sans-serif;
  font-size: 16px;
  font-weight: 400;
  line-height: 1.5rem;
//   text-shadow: 0 1px 1px #b6701e;
  color: #9c9c9c;
  background-color: #111;
  overflow: hidden;
}

section {
//   color: #111;
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
  text-decoration: none;
}

a:hover {
  color: #fba508;
  text-decoration: none;
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


:root {
    background: #222;
    height: 100%;
  }
  
  ::-webkit-scrollbar {
      display: none;
  }
  
  ::selection {
      background: rgba(128,128,128,.2);
  }
  
  /* ~ */
  
  a {
      color: #49c;
  }
  
  body {
      -webkit-font-smoothing: antialiased;
      background: #222;
      color: #9c9c9c;
      height: 100%;
  }
  
  code {
      color: #9c9c9c;
  }
  
  html {
      overflow: -moz-scrollbars-none;
  }
  
  pre {
      margin: 0;
      white-space: -moz-pre-wrap;
      white-space: -o-pre-wrap;
      white-space: pre-wrap;
      word-wrap: break-word;
  }
  
  span.fa-home {
    width: 18px;
  }
  
  /*
   *
   * nav
   *
   */
  
  nav {
      padding: 20px;
  }
  
  nav a {
      color: #777;
  }
  
  nav section.right {
      float: right;
  }
  
  /*
   *
   * td
   *
   */
  
  div.td {
      float: left;
      width: 35%;
  }
  
  div.td div {
      padding: 20px;
  }
  
  /*
   *
   * head
   *
   */
  
  div.head {
      background: #222;
      color: #777;
  }
  
  div.head a {
      color: #ddd;
  }
  
  div.head strong {
      font-weight: normal;
      color: #ddd;
  }
  
  span.highlight {
      color: #7d7;
  }
  
  /*
   *
   * ~
   *
   */
  
  div.body {
      background: #222;
      bottom: 0;
      color: #777;
      left: 0;
      position: absolute;
      right: 0;
      top: 128px;
  }
  
  div.body a {
      color: #ddd;
  }
  
  div.body strong {
      font-weight: normal;
      color: #ddd;
  }
  
  /*
   *
   * output
   *
   */
  
  .output a {
      border: none;
      color: #59b;
      padding: 0;
  }
  
  .output a code {
      color: #59b;
  }
  
  .output blockquote {
      background: none;
      border-left: 3px solid #777;
      color: #777;
      margin: 20px 0;
      padding: 0 0 0 10px;
  }
  
  .output code {
      background: #333;
      border: 1px solid #333;
      padding: 2px 5px;
  }
  
  .output div.footnotes {
      font-size: 14px;
  }
  
  .output h1, .output h2, .output h3, .output h4, .output h5, .output h6 {
      line-height: normal;
      margin: 20px 0;
  }
  
  .output hr {
      background: transparent url("data:image/gif;base64,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") repeat-x 0 0;
      border: 0 none;
      color: #ccc;
      height: 4px;
      padding: 0;
      margin: 15px 0;
  }
  
  .output ol {
      padding-left: 64px;
  }
  
  .output pre {
      background: #111;
      border: 1px solid #111;
      border-radius: 3px;
      /* color: #555; */
      margin-bottom: 10px;
      padding: 10px;
  }
  
  .output pre code {
      background: none;
      border: none;
      padding: 0;
  }
  
  .output table {
      border-width: 1px 0 0 1px;
      border-color: #bbb;
      border-style: solid;
  }
  
  .output table td, .output table th {
      border-width: 0 1px 1px 0;
      border-color: #bbb;
      border-style: solid;
      padding: 10px;
  }
  
  /*
   *
   * prettify
   *
   */
  
  span.pln {
      color: #fff;
  }
  
  span.tag {
      color: #995c8e;
  }
  
  span.atn {
      color: #997a5c;
  }
  
  span.pun {
      color: #995c8e;
  }
  
  span.atv {
      color: #5c5c99;
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
        if (class_exists('Websemantics\FileIcons\FileIcons'))
        {
            $class_name = $web_icons->getClassWithColor(basename($directory_object['name']));
            $file_name_to_print = '<i class="' . $class_name . '"></i>&nbsp;&nbsp;' . basename($directory_object['name']);
        }
        else
        {
            $file_name_to_print = basename($directory_object['name']);
        }
        $LEFT_AREA .= '<a href="?path=' . $requested_path . '/' . basename($directory_object['name']) . '" title="' . basename($directory_object['name']) . ' - ' . file_size_human_friendly(basename($directory_object['size'])) . '">' . $file_name_to_print . '</a><br />';
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
        if (class_exists('Websemantics\FileIcons\FileIcons'))
        {   
            $class_name = $web_icons->getClassWithColor(basename($directory_object['name']));
            $file_name_to_print = '<i class="' . $class_name . '"></i>&nbsp;&nbsp;' . basename($directory_object['name']);
        }
        else
        {
            $file_name_to_print = basename($directory_object['name']);
        }
        $LEFT_AREA .= '<a href="?path=' . $print_requested_path . basename($directory_object['name']) . '" title="' . basename($directory_object['name']) . ' - ' . file_size_human_friendly(basename($directory_object['size'])) . '">' . $file_name_to_print . '</a><br />';
    }

    if ($requested_full_path !== $root_path && str_replace('\\','/', dirname($requested_path)) !== '/')
    {
        $LEFT_AREA = '<a href="?path=' . str_replace('\\','/', dirname(dirname($requested_path))) . '">..</a><br />' . $LEFT_AREA;
    }
}
else
{
    // misspelled URL, redirect to root:
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

    if (detect_file_type($requested_path) === 'MARKDOWN')
    {
        if (class_exists('Parsedown'))
        {
            $parser = new Parsedown();
            $RIGHT_AREA = '<div class="output">' . "\n" . $parser->text($RIGHT_AREA) . "\n" . '</div>';
        }
        else
        {
            $WARNING_TEXT = CONST_MARKDOWN_CLASS_MISSING;
        }
    }
    elseif (detect_file_type($requested_path) === 'PICTURE')
    {
        $RIGHT_AREA = '<div class="output" style="text-align:center;"><img src="' . $requested_path . '"</></div>';
    }
    else // assume its a text file, attempt to print it
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
