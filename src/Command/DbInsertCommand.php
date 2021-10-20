<?php

namespace App;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;


class DbInsertCommand extends Command
{
    protected static $defaultName = 'db:insert';
    protected static $defaultDescription = 'Insert the records.';

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
            ->setHelp('Insert datas to a table.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        

        $io = new SymfonyStyle($input, $output);
        $sm = $this->conn->getSchemaManager();      
        
        $tables = $sm->listTables();
        $tbs=[];
        foreach ($tables as $key => $value) {
            $tbs[]=$value->getName();
        }
        if(!$tbs){

            $io->error("NO Tables.");

        }else{

            
            $helper = $this->getHelper('question');
            $table = new ChoiceQuestion('Please select the table',
            $tbs,0);
            $table->setErrorMessage('Table %s is not exited.');
            $table_name = $helper->ask($input, $output, $table);

            //Get the columns
            $columns = $sm->listTableColumns($table_name);
            $insert_datas=[];
            foreach ($columns as $column) {     
                $columnname=$column->getName();
                if($columnname!="id") $insert_datas[$column->getName()]=$this->getInputData($input,$output,$column->getName(),$io);
                
            }
            $this->conn->insert($table_name,$insert_datas);
            $io->success("Inserted successfully. use php index.php db:list to show the datas.");
        }
        
        
        return Command::SUCCESS;
    }


    protected function getInputData($input,$output,$column,$io){


            $helper = $this->getHelper('question');
            $question = new Question('Please enter the value of ['.$column.'] :');
            $value = $helper->ask($input, $output, $question);
            if(!$value){
            $io->error('Please enter the '.$column);           
            return $this->getInputData($input,$output,$column,$io);

            }
            return $value;
    }
}
