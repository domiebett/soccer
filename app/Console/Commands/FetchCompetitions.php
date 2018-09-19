<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Scrapers\Competitions\EspnScraper;

class FetchCompetitions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:competitions {service}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch competitions from the ESPN website';

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
        $this->fetchCompetitions($this->argument('service'));
    }

    private function fetchCompetitions(String $service)
    {
        switch ($service) {
            case 'espn':
                $this->fetchFromEspn();
                break;
            
            default:
                echo 'You have to specify the service to fetch data from';
                break;
        }
    }
    
    private function fetchFromEspn()
    {
        $espn = new EspnScraper();
        
        print "\nFetching competitions...";
        $espn->fetchCompetitions();
        print "\nDone!";
        
        print "\n\nSaving competitions...";
        $espn->saveCompetitions();
        print "\nDone!";
    }
}
