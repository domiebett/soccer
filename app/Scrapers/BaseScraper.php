<?php

namespace App\Scrapers;

require_once(public_path('bots/LIB_parse.php'));

class BaseScraper {

    public function __construct()
    {
        
    }
    
    protected function fetchPage(String $pageUrl) {
        return http_get(
                $target = $pageUrl,
                $ref = "");
    }
    
    /**
     * Splits string by tags and removes the tags.
     * 
     * @param string $html - string of HTML to be split
     * @param string $htmlTag = string rep of the tag
     * 
     * @return array - all content within the tags split
     */
    protected function htmlSplit(string $html, string $htmlTag) {
        $elements = parse_array($html, "<$htmlTag>", "</$htmlTag");
        $splitElements = [];
        
        foreach ($elements as $element) {
            
            $cleanedElement = return_between(
                    $element,
                    "<$htmlTag>",
                    "</$htmlTag",
                    EXCL);
            
            $splitElements[] = $cleanedElement;
        }
        
        return $splitElements;
    }
}
