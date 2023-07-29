<?php
function createDirectoryStructure($dir, $indent = 0, $fileList = array(), $excludeDirs = array('.git', 'node_modules', 'vendor','.idea','.vscode','public','dist','translations','logs','scripts','assets'), $excludeFiles = array('.DS_Store'))
{
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && !in_array($file, $excludeDirs) && !in_array($file, $excludeFiles)) {
            if (is_dir($dir . '/' . $file)) {
                $fileList[] = str_repeat('  ', $indent) . '[' . $file . ']';
                $fileList = createDirectoryStructure($dir . '/' . $file, $indent + 1, $fileList);
            } else {
                $fileList[] = str_repeat('  ', $indent) . $file;
            }
        }
    }
    return $fileList;
}

$directoryToScan = './'; // Inserisci il percorso della cartella che desideri analizzare
$structure = createDirectoryStructure($directoryToScan);

$file = fopen('directory_structure.txt', 'w');
fwrite($file, implode(PHP_EOL, $structure));
fclose($file);

echo 'File "directory_structure.txt" creato con successo!';
?>
