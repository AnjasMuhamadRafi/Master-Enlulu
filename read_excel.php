<?php
// Read Database Enlulu.xlsx structure
$zipPath = "C:\\Users\\Enlulu-PC\\Documents\\Database Enlulu.xlsx";

if (!file_exists($zipPath)) {
    die("File not found: $zipPath");
}

$zip = new ZipArchive();
if ($zip->open($zipPath) !== TRUE) {
    die("Cannot open zip file");
}

// Read shared strings if available (for cell values)
$sharedStrings = [];
if ($zip->locateName('xl/sharedStrings.xml') !== false) {
    $xmlStr = $zip->getFromName('xl/sharedStrings.xml');
    $xml = simplexml_load_string($xmlStr);
    if ($xml) {
        foreach ($xml->si as $index => $string) {
            if (isset($string->t)) {
                $sharedStrings[] = (string)$string->t;
            }
        }
    }
}

// Read sheet1
$sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
$xml = simplexml_load_string($sheetXml);

if (!$xml) {
    die("Cannot parse sheet XML");
}

echo "=== DATABASE ENLULU.XLSX STRUCTURE ===\n\n";

$rowCount = 0;
foreach ($xml->sheetData->row as $row) {
    if ($rowCount >= 2) break; // First 2 rows
    
    $rowData = [];
    foreach ($row->c as $cell) {
        $value = '';
        
        if (isset($cell->v)) {
            $cellValue = (string)$cell->v;
            
            // Check if it's a shared string reference
            if (isset($cell['t']) && (string)$cell['t'] === 's') {
                $stringIndex = (int)$cellValue;
                $value = $sharedStrings[$stringIndex] ?? '';
            } else {
                $value = $cellValue;
            }
        }
        
        $rowData[] = $value;
    }
    
    echo "Row " . ($rowCount + 1) . ":\n";
    echo implode(" | ", array_slice($rowData, 0, 50)) . "\n\n";
    
    $rowCount++;
}

$zip->close();
echo "✓ File read successfully\n";
