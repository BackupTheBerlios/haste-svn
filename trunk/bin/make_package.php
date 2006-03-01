<?php
/**
 * This is the package.xml generator for Haste
 *
 * @access public
 * @category pear
 * @author halt feits <halt.feits@gmail.com>
 */
 
// http://pear.php.net/package/PEAR_PackageFileManager/docs/1.6.0a4/PEAR_PackageFileManager/PEAR_PackageFileManager2.html#methodaddReplacement
require_once('PEAR/PackageFileManager2.php');

$description = 'Haste is Ethna Supplement';
$varsion = '0.9.0';

$config = array(
    'baseinstalldir' => 'Ethna/Haste',
    'packagedirectory' => dirname(dirname(__FILE__)),
    'filelistgenerator' => 'file',
    'ignore' => array('CVS/', 'make_package.php', 'package.xml', 'make_doc.php'),
    'changelogoldtonew' => false,
    'description' => $description,
    'simpleoutput' => true,

    );
 
print("Start Script\n");
$packagexml = new PEAR_PackageFileManager2;
$packagexml->setOptions($config);
$packagexml->setPackage('Haste');
$packagexml->setSummary('Haste - Ethna support Pack');
$packagexml->setDescription($description);
$packagexml->setLicense('BSD', 'http://www.opensource.org/licenses/bsd-license.php');
$packagexml->setReleaseVersion($varsion);
$packagexml->setAPIVersion($varsion);
$packagexml->setReleaseStability('alpha');
$packagexml->setAPIStability('alpha');
$packagexml->setNotes('Haste');

$packagexml->setPackageType('php');
$packagexml->addRole('tpl', 'php');

$packagexml->addMaintainer('lead', 'halt' , 'halt feits', 'halt.hde@gmail.com');

$packagexml->setPhpDep('4.1.0');
$packagexml->setPearinstallerDep('1.3.5');
$packagexml->setPackageType('php');

//$packagexml->setChannel('channel.php.gr.jp');
$packagexml->setChannel('pear.php.net');

$packagexml->generateContents();


// note use of debugPackageFile() - this is VERY important
if (isset($_GET['make']) || $_SERVER['argv'][1] == 'make') {
    debug_print("writePackageFile\n");
    $result = $packagexml->writePackageFile();
} else {
    $result = $packagexml->debugPackageFile();
    debug_print("debugPackageFile\n");
}

if (PEAR::isError($result)) {
    debug_print($result->getMessage()."\n");
    exit();
}
debug_print("End Script\n");

function debug_print($message)
{
    return print($message);
}
?>
