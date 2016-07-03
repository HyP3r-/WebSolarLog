<?php
class CsvWriter {
    public function getCsvData() {
    }

    public function writeCsvData($filename, $data) {
        $this->writeToFile($filename, $data, 'w+');
    }

    // The @ will ignore warnings
    public function readCsvData($filename) {
        return file($filename);
    }

    public function appendCsvData($filename, $data) {
        $this->writeToFile($filename, $data, 'a+');
    }

    private function writeToFile($filename, $data, $mode) {
        $fh = fopen($filename, $mode) or die("can't open " . $filename . " file");
        fwrite($fh, $data);
        fclose($fh);
    }
}

?>