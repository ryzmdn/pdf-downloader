<?php
session_start();

define('PDF_DIRECTORY', 'secure_pdfs/');
define('MAX_DOWNLOADS_PER_HOUR', 10);
define('ALLOWED_FILE_TYPES', ['pdf']);

ini_set('display_errors', 0);
error_reporting(E_ALL);
error_log("Download attempt initiated");

class PDFDownloader
{
    private $file;
    private $validFiles = [
        'guide' => 'user_guide.pdf',
        'tech' => 'technical_doc.pdf',
        'report' => 'report_2024.pdf',
    ];

    public function __construct($fileId)
    {
        $this->file = $this->sanitizeInput($fileId);
    }

    private function sanitizeInput($input)
    {
        return htmlspecialchars(strip_tags(trim($input)));
    }

    private function validateRequest()
    {
        if (empty($this->file)) {
            throw new Exception("No file specified");
        }

        if (!array_key_exists($this->file, $this->validFiles)) {
            throw new Exception("Invalid file requested");
        }

        if (!$this->checkRateLimit()) {
            throw new Exception("Download limit exceeded");
        }

        return true;
    }

    private function checkRateLimit()
    {
        $hour = date('Y-m-d-H');
        if (!isset($_SESSION['downloads'][$hour])) {
            $_SESSION['downloads'] = [$hour => 1];
            return true;
        }

        if ($_SESSION['downloads'][$hour] >= MAX_DOWNLOADS_PER_HOUR) {
            return false;
        }

        $_SESSION['downloads'][$hour]++;
        return true;
    }

    private function getFileType($filename)
    {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }

    private function logDownload($success)
    {
        $logEntry = date('Y-m-d H:i:s') . " | IP: " . $_SERVER['REMOTE_ADDR'] .
        " | File: " . $this->file . " | Status: " . ($success ? 'Success' : 'Failed') . "\n";
        file_put_contents('download_log.txt', $logEntry, FILE_APPEND);
    }

    public function process()
    {
        try {
            $this->validateRequest();

            $filename = $this->validFiles[$this->file];
            $filepath = PDF_DIRECTORY . $filename;

            if (!file_exists($filepath)) {
                throw new Exception("File not found");
            }

            if (!in_array($this->getFileType($filepath), ALLOWED_FILE_TYPES)) {
                throw new Exception("Invalid file type");
            }

            $this->logDownload(true);

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
            header('Content-Length: ' . filesize($filepath));
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: public');

            $chunk_size = 1024 * 1024;
            $handle = fopen($filepath, 'rb');

            while (!feof($handle)) {
                echo fread($handle, $chunk_size);
                flush();
            }

            fclose($handle);
            exit;

        } catch (Exception $e) {
            $this->logDownload(false);
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }
}

if (isset($_GET['file'])) {
    $downloader = new PDFDownloader($_GET['file']);
    $downloader->process();
}
