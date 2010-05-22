#!/usr/bin/env php
<?php
namespace beamdown;
/**
 * BeamDown - a (semi-)Markdown to TeX (Beamer) converter
 *      written by Florian Anderiasch <fa at art-core dot org>
 *      version 1.0, 2010-05-22
 */
error_reporting(E_ALL & E_STRICT);

require 'BeamDown.php';

if ($argc < 2) {
    echo "usage: {$argv[0]} <filename>" . PHP_EOL;
    exit(1);
}

$bd = new BeamDown();

// this line is purely optional
$bd->setTemplateDir('./templates/default');
$tpl = $bd->getTemplateDir();

// get the input from this file
$r = $bd->readfile($argv[1]);

// you don't want to show them here when outputting to STDOUT, but might be handy
// echo $bd->showErrors();

// you could as well skip this parameter, or use your own string
echo $bd->build($bd->getText());
?>
