<?php

namespace ANOITCOM\InformationFormsBundle\Command;

use ANOITCOM\InformationFormsBundle\Services\Covid\CovidObjectsService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCovidObjectForRegions extends Command
{

    protected static $defaultName = 'covid:create';

    /**
     * @var CovidObjectsService
     */
    private $covidObjectsService;


    public function __construct(CovidObjectsService $covidObjectsService, $name = null)
    {
        parent::__construct($name);
        $this->covidObjectsService = $covidObjectsService;
    }


    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->covidObjectsService->createObjects();
    }
}