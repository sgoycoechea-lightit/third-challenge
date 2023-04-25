<?php namespace Acme;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\Table;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;


class ShowCommand extends Command {

    private $apiKey = "41fb8ff7";

    public function configure() {
        $this->setName('show')
             ->addArgument('title', InputArgument::REQUIRED, 'Movie title.')
             ->setDescription('Show movie data.')
             ->addOption('fullPlot', null, InputOption::VALUE_NONE, 'Display the full plot.');
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $movieTitle = $input->getArgument('title');
        $fullPlot = $input->getOption('fullPlot');
        $movieData = $this->getMovieData($movieTitle, $fullPlot);

        if (is_null($movieData)) {
            $output->writeln("We couldn't find that movie.");
            return 0;
        }

        $movieDataArray = $this->dictionaryToArray($movieData);

        $table = new Table($output);
        $table->setRows($movieDataArray)
              ->render();

        return 0;
    }

    private function dictionaryToArray($dict) {
        $array = [];
        foreach($dict as $key=>$value) {
            if (!is_array($value)) {
                array_push($array, [$key, $value]);
            }
        }
        return $array;
    }

    private function getMovieData($movieTitle, $fullPlot) {
        $client = new Client();
        $plotValue = $fullPlot ? "full" : "short";
        $url = "http://www.omdbapi.com/?apikey={$this->apiKey}&t={$movieTitle}&plot={$plotValue}";

        $request = new Request('GET', $url);
        $response = $client->send($request);
        $response_body = (string)$response->getBody();
        $movieData = json_decode($response_body);

        return $this->isValidResponse($movieData) ? $movieData : null;
    }

    private function isValidResponse($movieData) {
        $responseKey = "Response";
        return property_exists($movieData, $responseKey) && $movieData->$responseKey == "True";
    }
}