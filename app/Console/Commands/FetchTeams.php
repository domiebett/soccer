<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FetchTeams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:teams {service}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch teams from competitions in the specified service';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->fetchTeams($this->argument('service'));
    }
    
    /**
     * Chooses a scraper for the specified service
     * 
     * @param String $service - service to scrape
     */
    public function fetchTeams(String $service) {
        switch ($service) {
            case 'espn':
                $this->fetchFromEspn();
                break;
            
            default:
                echo 'You have to specify the service to fetch data from';
                break;
        }
    }
    
    /**
     * Fetches teams from the ESPN team scraper
     */
    public function fetchFromEspn() {
        $espnScraper = new \App\Scrapers\Teams\EspnScraper();
        print("\nFetching teams...");
        $espnScraper->fetchCompetitionTeams();
        $espnScraper->saveCompetitionTeams();
    }
}
