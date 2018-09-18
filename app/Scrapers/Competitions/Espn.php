<?php

namespace App\Scrapers\Competitions;

require_once(public_path('bots/LIB_http.php'));
require_once(public_path('bots/LIB_parse.php'));

use App\Scrapers\BaseScraper;

class EspnScraper extends BaseScraper {

    const ESPN_COMPETITION_URL = "http://www.espn.com/football/story/_/id/21087321/soccer-leagues-competitions";
    
    const COMPETITION_OPENING_TAGS = '<aside class="inline-table">';
    const COMPETITION_CLOSING_TAGS = '</aside>';
    
    const COMPETITION_ROW_OPENING_TAGS = '<tr class="last">';
    const COMPETITION_ROW_CLOSING_TAGS = '</tr>';
    
    public function __construct()
    {
        $this->webPage = null;
        $this->fetchPage();
    }
    
    /**
     * Fetches the web page
     */
    public function fetchPage()
    {
        $this->webPage = http_get(
                $target = self::ESPN_COMPETITION_URL,
                $ref = "");
    }

    /**
     * Fetches the competitions from the web-page.
     */
    public function fetchCompetitions()
    {
        $tableRows = $this->retrieveCompetitionTableRows();
        
        print_r($tableRows);
        
        $rowContents = $this->retrieveColumns($tableRows);
        
        print_r($rowContents);
    }
    
    /**
     * Retrieves rows from the competitions table
     * 
     * @return array - of rows from the table
     */
    private function retrieveCompetitionTableRows() {
        $tables = return_between(
                $this->webPage['FILE'],
                self::COMPETITION_OPENING_TAGS,
                self::COMPETITION_CLOSING_TAGS,
                EXCL);
        
        return parse_array(
                $tables,
                self::COMPETITION_ROW_OPENING_TAGS,
                self::COMPETITION_ROW_CLOSING_TAGS);
    }
    
    /**
     * Retrieves columns from all the rows of a table
     * 
     * @param array $tableRows - the table rows
     * 
     * @return array - columns from each row of the table
     */
    private function retrieveColumns($tableRows = []) {
        $rowColumns = [];
        
        foreach($tableRows as $tableRow) {
            $rowContents = return_between(
                    $tableRow,
                    self::COMPETITION_ROW_OPENING_TAGS,
                    self::COMPETITION_ROW_CLOSING_TAGS,
                    EXCL);
            
            $columns = $this->htmlSplit($rowContents, 'td');
            
            $rowColumns[] = $columns;
        }
        
        return $rowColumns;
    }
}
