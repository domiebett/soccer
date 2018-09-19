<?php

namespace App\Scrapers\Competitions;

require_once(public_path('bots/LIB_http.php'));
require_once(public_path('bots/LIB_parse.php'));

use App\Scrapers\BaseScraper;
use App\Models\Competition;

class EspnScraper extends BaseScraper {

//    const ESPN_COMPETITION_URL = "http://www.espn.com/football/story/_/id/21087321/soccer-leagues-competitions";
    const ESPN_COMPETITION_URL = "file:///Users/dominicbett/Documents/Downloaded%20Webpages/Soccer%20Leagues%20and%20Competitions.html";
    
    const COMPETITION_OPENING_TAGS = '<aside class="inline-table">';
    const COMPETITION_CLOSING_TAGS = '</aside>';
    
    const COMPETITION_ROW_OPENING_TAGS = '<tr class="last">';
    const COMPETITION_ROW_CLOSING_TAGS = '</tr>';
    
    private $competitions;
    
    public function __construct()
    {
        $this->webPage = null;
        $this->competitions = [];
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
                
        $rowContents = $this->retrieveColumns($tableRows);
        
        $this->competitions = $this->formatCompetitions($rowContents);
        
    }
    
    /**
     * Saves competitions to the db
     * 
     * @return null
     */
    public function saveCompetitions()
    {
        if (count($this->competitions) <= 0) {
            print "\nThere are no competitions to save";
            return;
        }
        
        foreach ($this->competitions as $competition) {
            try {
                Competition::create($competition);
                print "\nSuccess: Saved " . $competition['name'] . " successfully";
            } catch (\Illuminate\Database\QueryException $exc) {
                print "\nError: " . $competition['name'] . " couldn't be saved";
            }
        }
    }
    
    /**
     * Getter for competitions fetched.
     * 
     * @return array - Competitions currently existing
     */
    public function getCompetitions()
    {
        return $this->competitions;
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
    
    /**
     * Create an array of competitions compatible with the Competition model
     * 
     * @param array $rowContents
     * 
     * @return array
     */
    private function formatCompetitions($rowContents)
    {
        $competitions = [];
        
        foreach ($rowContents as $rowContent) {
            $firstColumn = $rowContent[0];
            
            $imageSource = $this->retrieveCompetitionLogo($firstColumn);
            $competitionName = $this->retrieveCompetitionName($firstColumn);
            
            $competitionInfo = [
                'logo' => $imageSource,
                'name' => $competitionName,
            ];
            
            $competition = array_merge(
                    $competitionInfo,
                    $this->competitionLinks($rowContent)
            );
            
            $competitions[] = $competition;
        }
        
        return $competitions;
    }
    
    /**
     * Fetches the competition name from the column string
     * 
     * @param string $column - the column with the data
     * 
     * @return array
     */
    private function retrieveCompetitionName($column)
    {
        $linkElement = return_between(
                $column,
                "<a",
                "</a>",
                INCL);
        return strip_tags($linkElement);
    }
    
    /**
     * Fetches the competition logo from the column string
     * 
     * @param string $column - the column with the data
     * 
     * @return string - href attribute of the logo image
     */
    private function retrieveCompetitionLogo($column)
    {
        $imageElement = return_between(
                $column,
                "<img",
                ">",
                INCL);
        return get_attribute($imageElement, "src");
    }
    
    /**
     * Gets the links for resources for a certain competition
     * 
     * @param array $rowContent - all columns within a row
     * 
     * @return array - links for the resources for a competition
     */
    private function competitionLinks($rowContent)
    {
        $tableColumn = $rowContent[1];
        $tableLink = get_attribute($tableColumn, "href");

        $scoresColumn = $rowContent[2];
        $scoresLink = get_attribute($scoresColumn, "href");

        $fixturesColumn = $rowContent[3];
        $fixturesLink = get_attribute($fixturesColumn, "href");
        
        return [
            'table_link' => $tableLink,
            'fixtures_link' => $fixturesLink,
            'scores_link' => $scoresLink,
            'results_link' => $fixturesLink
        ];
    }
}
