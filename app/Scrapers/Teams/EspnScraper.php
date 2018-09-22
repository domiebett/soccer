<?php

namespace App\Scrapers\Teams;

require_once(public_path('bots/LIB_http.php'));
require_once(public_path('bots/LIB_parse.php'));

use App\Models\Competition;
use App\Scrapers\BaseScraper;

class EspnScraper extends BaseScraper {
    
    // table tags have classes to distinguish it from any other tables
    const TABLE_OPENING_TAGS = '<table class="mod-data">';
    const TABLE_CLOSING_TAGS = '</table>';
    
    const TABLE_ROWS_OPENING_TAGS = '<tr class>';
    const TABLE_ROWS_CLOSING_TAGS = '</tr>';
    
    // tbody has no > tag to address tags with and without class attribute.
    const TABLE_BODY_OPENING_TAGS = '<tbody';
    const TABLE_BODY_CLOSING_TAGS = '</tbody>';
    
    // thead has no > tag to address tags with and without class attribute.
    const TABLE_HEAD_OPENING_TAGS = '<thead';
    const TABLE_HEAD_CLOSING_TAGS = '</thead>';
    
    private $competitions;
    private $competitionTeams = [];
    private $teams;
    
    public function __construct() {
        parent::__construct();
        $this->competitions = [];
        $this->teams = [];
        $this->fetchEspnCompetitionLinks();
    }
    
    /**
     * Fetch links from db for competitions for competition pages
     * containing specific competition teams
     */
    public function fetchEspnCompetitionLinks()
    {
        $this->competitions = Competition::where('service', 'espn')
                ->select('id', 'competition_link', 'name')
                ->get();
    }
    
    /**
     * Initial method to fetch teams from all competitions.
     */
    public function fetchCompetitionTeams() {
        foreach($this->competitions as $competition) {
            $competitionTeams =
                    $this->fetchSingleCompetitionTeams($competition);
            $this->teams[] = $competitionTeams;
        }
    }
    
    /**
     * Fetch all the teams from a single competition
     * 
     * @param Competition $competition
     * 
     * @return Array - containing teams from the competition
     */
    private function fetchSingleCompetitionTeams($competition) {
        print("\n  Fetching teams for " . $competition['name']);
        $competitionLink = $competition['competition_link'];
        
        $competitionLink = "file:///Users/dominicbett/Documents/Downloaded%20Webpages/English%20Premier%20League%20News,%20Stats,%20Scores%20-%20ESPN.html";
        $competitionLink = "file:///Users/dominicbett/Documents/Downloaded%20Webpages/UEFA%20Champions%20League%20News,%20Stats,%20Scores%20-%20ESPN.html";
                
        $webPage = $this->fetchPage($competitionLink);
                
        $parsedTable = $this->extractParsedTable($webPage["FILE"]);
        
        $teams = $this->extractTeamsFromTable($parsedTable);
                
        return $teams;
    }
    
    /**
     * Persists the teams to the db.
     * 
     * @return null
     */
    public function saveCompetitionTeams() {
        if (count($this->teams) <= 0) {
            print("There are no teams to save.");
            return;
        }
        
        print("\nSaving teams...");
        foreach (array_flatten($this->teams, 1) as $team) {
            try {
                \App\Models\Team::create($team);
                print"\n  Saved ".$team['name'];
            } catch (\Illuminate\Database\QueryException $ex) {
                print("\n  Couldn't be saved");
            }
        }
        print("\nTeams saved successfully");
    }
    
    /**
     * Parses a table to get all its headers and bodies
     * 
     * @param String $webpage
     * 
     * @return String - containing formatted header and body of the table
     */
    private function extractParsedTable(String $webpage) {
        $parsedTable = [];
        
        $teamsTable = return_between(
                $webpage,
                self::TABLE_OPENING_TAGS,
                self::TABLE_CLOSING_TAGS,
                EXCL);
                        
        $tableBodies = parse_array(
                $teamsTable,
                self::TABLE_BODY_OPENING_TAGS,
                self::TABLE_BODY_CLOSING_TAGS);
        
        $tableHeaders = parse_array(
                $teamsTable,
                self::TABLE_HEAD_OPENING_TAGS,
                self::TABLE_HEAD_CLOSING_TAGS
                );
        
        $parsedTable['bodies'] = $tableBodies;
        $parsedTable['headers'] = $tableHeaders;
        
        return $parsedTable;
    }
    
    /**
     * Get teams from a parsed table above. Gets both teams from group
     * stage competitions(such as UEFA) and league competitions (such as
     * EPL).
     * 
     * @param Array $parsedTable - Table that has been parsed/processed
     * 
     * @return Array - Array of all teams from competition
     */
    private function extractTeamsFromTable($parsedTable) {
        $teams = [];
        
        $tableHeaders = $parsedTable['headers'];
        $tableBodies = $parsedTable['bodies'];
        
        $groupNames = $this->extractGroupNames($tableHeaders);
        
        for ($i = 0; $i < count($groupNames); $i++) {
            $groupName = $groupNames[$i];
            array_push(
                    $teams,
                    $this->extractTeamsFromBody($tableBodies[$i], $groupName));
        }
        
        return array_flatten($teams, 1);
    }
    
    /**
     * Collects the group name from the titles of tables (such as Group A,
     * Group B in UEFA). Default league set up return 'TEAM'
     * 
     * @param Array $tableHeaders - all headers from a table
     * 
     * @return Array - array of table group names
     */
    private function extractGroupNames($tableHeaders) {
        $groupNames = [];
        
        foreach ($tableHeaders as $tableHeader) {
            $headerColumns = parse_array($tableHeader, "<th", "</th>");
            $firstColumn = $headerColumns[0];
            $groupName = strip_tags($firstColumn);
            
            $groupNames[] = $groupName;
        }
        
        return $groupNames;
    }
    
    /**
     * Gets all the team names from the provided table body and
     * assigns the given group name to the team.
     * 
     * @param String $tableBody - a body from a table
     * @param String $groupName - optional group name for teams
     * 
     * @return Array
     */
    private function extractTeamsFromBody($tableBody, $groupName = "") {
        $teams = [];
        
        $bodyRows = parse_array($tableBody, "<tr", "</tr>");
        
        foreach ($bodyRows as $bodyRow) {
            $bodyRowColumns = parse_array($bodyRow, "<td", "</td>");
            $firstColumn = $bodyRowColumns[0];
            $teamName = strip_tags($firstColumn);
            $teamLinkTags = return_between($firstColumn,
                    '<a', '</a>', EXCL);
            $teamLink = get_attribute($teamLinkTags, 'href');
            
            $team = [];
            $team['name'] = $teamName;
            $team['group'] = $groupName;
            $team['team_link'] = $teamLink;
            
            $teams[] = $team;
            
            print("\n    Retrieved $teamName");
        }
        
        return $teams;
    }
}

