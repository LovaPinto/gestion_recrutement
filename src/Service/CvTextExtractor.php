<?php
namespace App\Service;

use Smalot\PdfParser\Parser;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\Table;

class CvTextExtractor
{
    public function extract(string $path, string $mime): string
    {
        return match (true) {
            str_contains($mime, 'pdf') => $this->pdf($path),
            str_contains($mime, 'word') || str_contains($mime, 'officedocument') => $this->docx($path),
            str_contains($mime, 'image') => $this->image($path),
            default => '',
        };
    }

    private function pdf(string $path): string
    {
        $parser = new Parser();
        return strtolower($parser->parseFile($path)->getText());
    }

    private function docx(string $path): string
    {
        $phpWord = IOFactory::load($path);
        $text = '';

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $el) {
                $text .= $this->extractElementText($el) . ' ';
            }
        }

        return strtolower($text);
    }

    private function extractElementText($el): string
    {
        $text = '';

        switch (get_class($el)) {
            case TextRun::class:
                foreach ($el->getElements() as $child) {
                    $text .= $this->extractElementText($child) . ' ';
                }
                break;

            case Text::class:
                $text .= $el->getText();
                break;

            case Table::class:
                foreach ($el->getRows() as $row) {
                    foreach ($row->getCells() as $cell) {
                        foreach ($cell->getElements() as $cellEl) {
                            $text .= $this->extractElementText($cellEl) . ' ';
                        }
                    }
                }
                break;

            default:
                break;
        }

        return $text;
    }

    private function image(string $path): string
    {
        $cmd = sprintf(
            'tesseract %s stdout -l eng+fra',
            escapeshellarg($path)
        );
        return strtolower(shell_exec($cmd) ?? '');
    }
}
