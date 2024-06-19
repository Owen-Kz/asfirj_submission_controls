require 'vendor/autoload.php';

use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader;

// Function to merge multiple PDFs into one
function mergePDFs($files, $outputFile) {
    $pdf = new Fpdi();

    foreach ($files as $file) {
        $pageCount = $pdf->setSourceFile($file);
        for ($i = 1; $i <= $pageCount; $i++) {
            $template = $pdf->importPage($i);
            $pdf->AddPage();
            $pdf->useTemplate($template);
        }
    }

    $pdf->Output($outputFile, 'F');
}
