<?php

/**
 * Gera PDF simples (sem bibliotecas externas) para cartelas de sorteio.
 * Layout:
 * - A4 retrato
 * - Quadrados de 60mm
 * - Máx. 3 colunas x 3 linhas por página
 * - Números de 1 até N em negrito, um por quadrado
 */
class SorteioPdfGenerator
{
    private const PAGE_WIDTH_PT = 595.276;  // A4
    private const PAGE_HEIGHT_PT = 841.890; // A4
    private const SQUARE_MM = 60.0;
    private const COLUMNS = 3;
    private const ROWS = 3;

    /**
     * @param int $maxNumber Valor máximo (1..100)
     */
    public function generate(int $maxNumber): string
    {
        $numbers = range(1, $maxNumber);
        $chunks = array_chunk($numbers, self::COLUMNS * self::ROWS);
        $streams = [];

        foreach ($chunks as $chunk) {
            $streams[] = $this->buildPageStream($chunk);
        }

        return $this->buildPdfDocument($streams);
    }

    /**
     * Monta comandos PDF da página.
     *
     * @param int[] $numbers
     */
    private function buildPageStream(array $numbers): string
    {
        $square = $this->mmToPt(self::SQUARE_MM);
        $totalGridWidth = self::COLUMNS * $square;
        $totalGridHeight = self::ROWS * $square;
        $marginX = (self::PAGE_WIDTH_PT - $totalGridWidth) / 2.0;
        $marginTop = (self::PAGE_HEIGHT_PT - $totalGridHeight) / 2.0;

        $commands = [];
        $commands[] = '0 0 0 RG';
        $commands[] = '0 0 0 rg';
        $commands[] = '1.5 w';

        foreach ($numbers as $index => $number) {
            $row = intdiv($index, self::COLUMNS);
            $col = $index % self::COLUMNS;

            $x = $marginX + ($col * $square);
            $yTop = $marginTop + ($row * $square);
            $y = self::PAGE_HEIGHT_PT - $yTop - $square;

            $commands[] = sprintf('%.3F %.3F %.3F %.3F re S', $x, $y, $square, $square);

            $label = (string) $number;
            $fontSize = $this->fontSizeFor($number);
            $textWidth = $this->estimateTextWidth($label, $fontSize);
            if ($textWidth > ($square - 20.0)) {
                $fontSize *= ($square - 20.0) / $textWidth;
                $textWidth = $this->estimateTextWidth($label, $fontSize);
            }

            $textX = $x + (($square - $textWidth) / 2.0);
            $textY = $y + (($square - $fontSize) / 2.0) + ($fontSize * 0.25);

            $commands[] = sprintf(
                'BT /F1 %.2F Tf 1 0 0 1 %.3F %.3F Tm (%s) Tj ET',
                $fontSize,
                $textX,
                $textY,
                $this->escapePdfText($label)
            );

            // Sublinha sempre o número, centralizado com a largura estimada do texto.
            $underlineY = $textY - max(2.5, $fontSize * 0.10);
            $commands[] = sprintf(
                '%.3F %.3F m %.3F %.3F l S',
                $textX,
                $underlineY,
                $textX + $textWidth,
                $underlineY
            );
        }

        return implode("\n", $commands) . "\n";
    }

    /**
     * @param string[] $streams
     */
    private function buildPdfDocument(array $streams): string
    {
        $objects = [];
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[2] = ''; // Preenchido após montar páginas
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>';

        $kids = [];
        foreach ($streams as $stream) {
            $pageObj = count($objects) + 1;
            $contentObj = $pageObj + 1;

            $kids[] = $pageObj . ' 0 R';
            $objects[$pageObj] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ' .
                self::PAGE_WIDTH_PT . ' ' . self::PAGE_HEIGHT_PT .
                '] /Resources << /Font << /F1 3 0 R >> >> /Contents ' . $contentObj . ' 0 R >>';

            $objects[$contentObj] = '<< /Length ' . strlen($stream) . " >>\nstream\n" . $stream . "endstream";
        }

        $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . count($kids) . ' >>';

        $pdf = "%PDF-1.4\n";
        $offsets = [0 => 0];
        $maxObject = count($objects);

        for ($i = 1; $i <= $maxObject; $i++) {
            $offsets[$i] = strlen($pdf);
            $pdf .= $i . " 0 obj\n" . $objects[$i] . "\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . ($maxObject + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= $maxObject; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= "trailer\n<< /Size " . ($maxObject + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n" . $xrefOffset . "\n%%EOF";

        return $pdf;
    }

    private function fontSizeFor(int $number): float
    {
        if ($number < 10) {
            return 150.0;
        }

        if ($number < 100) {
            return 120.0;
        }

        return 100.0;
    }

    private function mmToPt(float $mm): float
    {
        return $mm * 72.0 / 25.4;
    }

    private function estimateTextWidth(string $text, float $fontSize): float
    {
        // Aproximação suficiente para centralizar dígitos em Helvetica-Bold.
        return strlen($text) * ($fontSize * 0.60);
    }

    private function escapePdfText(string $text): string
    {
        return str_replace(
            ['\\', '(', ')'],
            ['\\\\', '\(', '\)'],
            $text
        );
    }
}
