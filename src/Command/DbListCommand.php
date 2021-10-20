<?php

namespace App;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\ChoiceQuestion;


class DbListCommand extends Command
{
    protected static $defaultName = 'db:list';
    protected static $defaultDescription = 'Show the records.';

    private $conn;

    public function __construct(){
        
        $connectionParams = [
            'url' => Database::getDatabaseUrl()
        ];
        $this->conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams);
        parent::__construct();

    }

    protected function configure(): void
    {
        $this
            ->setHelp('Show datas form a table.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        

        $io = new SymfonyStyle($input, $output);

        $sm = $this->conn->getSchemaManager();      
        //======1. Get the table;=========
        $tables = $sm->listTables();
        $tbs=[];
        foreach ($tables as $key => $value) {
            $tbs[]=$value->getName();
        }
        if(!$tbs){

            $io->error("NO Tables.");

        }else{

            $queryBuilder = $this->conn->createQueryBuilder();

            $helper = $this->getHelper('question');
            $table = new ChoiceQuestion('Please select the table',
            $tbs,0);
            $table->setErrorMessage('Table %s is not exited.');
            $table_name = $helper->ask($input, $output, $table);
            $res=$queryBuilder->select('*')->from($table_name)->fetchAllAssociative();

            //Get the columns
            $columns = $sm->listTableColumns($table_name);
            $header_fields=[];
            foreach ($columns as $column) {     
                $header_fields[]=$columnname=$column->getName();               
                
            }

            $arr_cols=[];
            foreach ($res as $key => $value) {               
               $arr_cols[]=$value;
            }
        
            $io->table($header_fields,$arr_cols);

        }


      
      
        
        return Command::SUCCESS;
    }
}
