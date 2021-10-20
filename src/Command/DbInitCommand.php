<?php

namespace App;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

use Doctrine\DBAL\Schema\Table;

use App\Database;

class DbInitCommand extends Command
{
    protected static $defaultName = 'db:init';
    protected static $defaultDescription = 'Create a table to the dababase';

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
            ->setHelp('Create table if not exited')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        
        $sm = $this->conn->getSchemaManager();
        $io = new SymfonyStyle($input, $output);
        //======1. Get the tables are exited;=========
        $tables = $sm->listTables();
        $tbs=[];
        foreach ($tables as $key => $value) {
            $tbs[]=$value->getName();
        }
        //=====2. ask user to enter a name for table;=====
       

        $tablename=$this->createATable($input,$output,$tbs,$io);
        if($tablename) {

            $this->conn->query("
                create table ".$tablename."(
                       id INT NOT NULL AUTO_INCREMENT,
                       PRIMARY KEY (id)
                        );
                        ");

            $io->success("The table [".$tablename."] created successfull.");
            //Add some columns for this table;
            $this->addColumnForTable($input,$output,$tablename,$io); 
        }
        
        return Command::SUCCESS;
    }

    protected  function createATable($input,$output,$tbs,$io)
    {
        $helper = $this->getHelper('question');
        $question = new Question('Please enter the name of the table:');
        $tb_name = $helper->ask($input, $output, $question);
        if(!$tb_name){

            $io->error('The name is invalid');
            if($this->askUserToConfirm("Do you want to create a table now? [y=yes,n=no]: ",$input,$output,$io)){
                return $this->createATable($input,$output,$tbs,$io);
            }
            return false;
            

        }else{

            //Check the name is exited or not.
            
            if(in_array($tb_name, $tbs)){

                $io->error('The table ['.$tb_name.'] is exited. Try another one please.');
                if($this->askUserToConfirm("Do you want to create a table now? [y=yes,n=no]: ",$input,$output,$io)){
                    return $this->createATable($input,$output,$tbs,$io);
                }
                return false;


            }else{

                return $tb_name;

            }

        }
    }


    protected function askUserToConfirm($msg,$input,$output,$io){

            $helper = $this->getHelper('question');
            $question_c = new ConfirmationQuestion($msg, false);
            if(!$helper->ask($input, $output, $question_c)){
               $io->success('Thanks for your using. You can use [php index.php db:add] to create a table next time.');
                return false;
            }
            return true;


    }

    protected function addColumnForTable($input,$output,$tablename,$io){


            $helper = $this->getHelper('question');
            $question_c = new ConfirmationQuestion("Do you want to add column for this table ?[y=yes,n=no] ", false);
            if(!$helper->ask($input, $output, $question_c)){
               $io->success('Thanks for your using. ');
                return false;
            }
            return $this->addColumn($input,$output,$tablename,$io);       



    }   

    protected function addColumn($input,$output,$tablename,$io){

        
        

         
         $helper = $this->getHelper('question');
         $question = new Question('Please enter the name of the column:');
         $column_name = $helper->ask($input, $output, $question);
         if(!$column_name){

            $io->error("Please enter the name of the column.[Press Ctrl+C to exit] :");
            return $this->addColumn($input,$output,$tablename,$io);
         }else{

             $this->conn->query("
                            alter table ".$tablename." add column ".$column_name." varchar(300) null;
                        ");
            

            return $this->addColumnForTable($input,$output,$tablename,$io);


         }
         return false;

    }
}
