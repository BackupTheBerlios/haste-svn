<?php
/**
 * Generate Haste Document
 *
 * @author halt <halt.hde@gmail.com>
 */

$source = "";
$output = ""

$command = "/usr/local/bin/php '/usr/local/lib/php/PhpDocumentor/phpDocumentor/phpdoc.inc'";
$command.= " -d {$source}";
$command.= " -t {$output}";
$command.= " -o HTML:frames:earthli";
//$command.= " -o HTML:Smarty:HandS";
$command.= " --ignore 'tmp,*.tpl.php,make_doc.php'";
$command.= " --sourcecode on";
$command.= " --title Haste";

//print($command ."\n");

system($command);
?>
