# PURPOSE

The purpose of this tool is to provide the convenience to browse your code or notes from your browser, having the directory tree structure in a left column, and the text of the file you select on the right. It supports displaying Markdown format!

# PREREQUISITES

You will need a web server that can execute PHP code to start on your PC. PHP provides a built-in obe which is ideal for the job.


# HOW TO INSTALL

The tool was designed to have all code in a single file (index.php), making it convenient to install at the root of the directory tree that you want to browse. To install, you can downlowd the software using the git clone or the provided zip file, or just download the index.php file, by clicking [here](index.php).


# HOW TO RUN

You can integrate the directory tree you want to browse with a PHP capable web server like apache, or - the proposed way - just start an instance of the built-in web server PHP provides. Assuming you have PHP installed on your PC, open a shell at the directory you wish to explore and run:

```
php -S 0.0.0.0:33333 -t .
```

Alternatively, you can use the scripts supplied in `scripts` folder. Copy first the one for your OS to the root folder where the index.php resides.


The above will start a web server that listens on port 33333. To browse it via your web browser, head on to:

[http://127.0.0.1:33333/](http://127.0.0.1:33333/)


# MARKDOWN FORMAT SUPPORT

Intended to work on files with .md extension only. To properly display Markdown files, you will need the [cebe/markdown](https://github.com/cebe/markdown) markdown parser. To install it, go to the root directory where the index.php file resides and run:

```
composer require cebe/markdown
```


# PREVIEW

![Preview image](preview/preview.png?raw=true "Preview image")
