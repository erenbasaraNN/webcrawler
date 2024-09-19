<?php

namespace App\Services;

use Exception;
use RuntimeException;

class MergePDF
{
    private string $logFile;

    public function __construct()
    {
        // Initialize a log file to track operations
        $this->logFile = '/tmp/mergepdf.log';
    }

    private function log(string $message): void
    {
        file_put_contents($this->logFile, $message . PHP_EOL, FILE_APPEND);
    }

    /**
     * Merges two PDFs and returns the path to the merged PDF.
     *
     * @param string $pdfUrl1 The URL of the first PDF.
     * @param string $pdfUrl2 The URL of the second PDF.
     * @return string|null The file path to the merged PDF, or null if merging fails.
     */
    public function merge(string $pdfUrl1, string $pdfUrl2): ?string
    {
        set_time_limit(300); // Increase to 5 minutes
        try {
            $this->log("Starting merge of two PDFs.");

            // Download both PDFs
            $pdf1Path = $this->downloadPdf($pdfUrl1);
            $pdf2Path = $this->downloadPdf($pdfUrl2);

            // Merge the downloaded PDFs using PDFTK
            $mergedPdfPath = $this->mergeWithPdftk([$pdf1Path, $pdf2Path]);

            $this->log("Merged PDF created at: $mergedPdfPath");

            // Clean up downloaded PDFs after merging
            $this->cleanupFiles([$pdf1Path, $pdf2Path]);

            return $mergedPdfPath;
        } catch (Exception $e) {
            // Log the exception and continue
            $this->log("An error occurred: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Downloads a PDF from a given URL.
     *
     * @param string $pdfUrl The URL of the PDF to download.
     * @return string The file path to the downloaded PDF.
     * @throws Exception If the download fails.
     */
    private function downloadPdf(string $pdfUrl): string
    {
        $this->log("Downloading PDF from: $pdfUrl");

        $tempPdfPath = tempnam('/tmp', 'pdf_'); // Use in-memory filesystem for speed
        $pdfContent = @file_get_contents($pdfUrl);

        if ($pdfContent === false) {
            $this->log("Failed to download PDF from $pdfUrl");
            throw new RuntimeException("Failed to download PDF from $pdfUrl");
        }

        file_put_contents($tempPdfPath, $pdfContent);
        $this->log("Successfully downloaded PDF to: $tempPdfPath");
        return $tempPdfPath;
    }

    /**
     * Merges multiple PDFs using PDFTK.
     *
     * @param array $pdfPaths Array of file paths to the PDFs to be merged.
     * @return string The file path to the merged PDF.
     * @throws Exception If the merging fails.
     */
    private function mergeWithPdftk(array $pdfPaths): string
    {
        // Define the output directory
        $outputDir = '/var/tmp/web_crawler/merged_pdf/';

        // Ensure the directory exists
        if (!is_dir($outputDir) && !mkdir($outputDir, 0777, true) && !is_dir($outputDir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $outputDir));
        }

        // Generate a unique merged PDF name directly with the '.pdf' extension
        $pdfSiteLink = 'merged_pdf_' . bin2hex(random_bytes(5)) . '.pdf';
        $mergedPdfPath = $outputDir . $pdfSiteLink;

        // Build the PDFTK command to merge PDFs
        $command = 'pdftk ' . implode(' ', $pdfPaths) . " cat output $mergedPdfPath";

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new RuntimeException("PDFTK merging failed.");
        }

        $this->log("PDFs merged successfully using PDFTK to: $mergedPdfPath");

        return $pdfSiteLink;
    }


    /**
     * Deletes the temporary PDF files after merging.
     *
     * @param array $filePaths Array of file paths to be deleted.
     */
    private function cleanupFiles(array $filePaths): void
    {
        foreach ($filePaths as $filePath) {
            if (file_exists($filePath)) {
                unlink($filePath);
                $this->log("Deleted temporary file: $filePath");
            }
        }
    }
}
