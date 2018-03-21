<?php
/**
 * This class is used to print common HTML tags
 */
namespace Core\Utils;

class Html {
    public static function beginBody() {
        echo "<body>";
    }

    public static function beginForm($method = 'get') {
        echo "<form action='$_SERVER[REQUEST_URI]' method='" . htmlspecialchars($method) . "'>";
    }

    public static function beginHead() {
        echo "<head>";
    }

    public static function beginPage($lang = 'en') {
        echo "<!DOCTYPE html><html lang='" . htmlspecialchars($lang) . "'>";
    }

    public static function buildTable(array $rows, array $headings) {
        echo '<table><tr>';
        foreach ($headings as $heading) {
            echo "<th>$heading</th>";
        }
        echo '</tr>';
        foreach ($rows as $row) {
            echo '<tr>';
            foreach ($row as $value) {
                echo "<td>$value</td>";
            }
            echo '</tr>';
        }
        echo '</table>';
    }

    public static function charset($charset = 'utf-8') {
        echo "<meta charset='" . htmlspecialchars($charset) . "'>";
    }

    public static function endBody() {
        echo "</body>";
    }

    public static function endForm() {
        echo '</form>';
    }

    public static function endHead() {
        echo "</head>";
    }

    public static function endPage() {
        echo '</html>';
    }

    public static function heading(string $text, int $type = 2) {
        echo "<h$type>" . htmlspecialchars($text) . "</h$type>";
    }

    public static function image(string $src, string $alt = '') {
        echo "<img src='" . htmlspecialchars($src) . "' alt='" . htmlspecialchars($alt) . "'>";
    }

    public static function includeJS($url) {
        echo "<script src='/js/" . htmlspecialchars($url) . ".js'></script>";
    }

    public static function includeCSS($url) {
        echo "<link rel='stylesheet' href='/css/" . htmlspecialchars($url) . ".css'>";
    }

    public static function submit(string $text, string $name = '') {
        $name = !empty($name) ? " name = '" . htmlspecialchars($name) . "'" : "";
        echo "<input type='submit'$name value='" . htmlspecialchars($text) . "'></input>";
    }

    public static function meta($name, $content) {
        echo "<meta name='" . htmlspecialchars($name) . "' content='" . htmlspecialchars($content) . "'>";
    }

    public static function paragraph($text) {
        echo '<p>' . htmlspecialchars($text) . '</p>';
    }

    public static function title($title) {
        echo '<title>' . htmlspecialchars($title) . '</title>';
    }
}