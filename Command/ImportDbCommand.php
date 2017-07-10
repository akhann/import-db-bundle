<?php

namespace Akhann\Bundle\ImportDbBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * Class ImportDbCommand
 * @author Sydney Moutia <sydney@akhann.com>
 */
class ImportDbCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('akhann:database:import')
            ->setDescription('Import database from remote server')
                ->addOption(
                    'force',
                    null,
                    InputOption::VALUE_NONE,
                    'Execute commands'
                );
            ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $force = $input->getOption('force');

        $io = new SymfonyStyle($input, $output);
        $io->title('Import Database');

        $username = $this->getContainerParameter('remote_server', 'ssh_username');
        $remoteHost = $this->getContainerParameter('remote_server', 'ssh_host');
        $keyFilePath = $this->getContainerParameter('remote_server', 'ssh_key_file');
        $key = new \Crypt_RSA();
        $key->loadKey(file_get_contents($keyFilePath));
        $ssh = new \Net_SSH2($remoteHost);

        if (!$ssh->login($username, $key)) {
            exit('Login Failed');
        }

        $host = $this->getContainerParameter('remote_server', 'mysql_host');
        $user = $this->getContainerParameter('remote_server', 'mysql_username');
        $password = $this->getContainerParameter('remote_server', 'mysql_password');
        $dbname = $this->getContainerParameter('remote_server', 'mysql_dbname');
        $tmpDir = $this->getContainerParameter('remote_server', 'tmp_dir');

        $filename = $this->generateDumpFilename($dbname);
        $compressFilename = sprintf('%s.tgz', $filename);
        $localTmpDir = $this->getContainerParameter('local_server', 'tmp_dir');
        $localDbname = $this->getContainerParameter('local_server', 'mysql_dbname');
        $localHost = $this->getContainerParameter('local_server', 'mysql_host');
        $localUser = $this->getContainerParameter('local_server', 'mysql_username');
        $localPassword = $this->getContainerParameter('local_server', 'mysql_password');

        //Dump Database
        $mysqlDumpCmd = $this->generateMysqlDumpCmd($host, $user, $password, $dbname, $tmpDir, $filename);
        $output->writeln($mysqlDumpCmd);
        if ($force) {
            $ssh->exec($mysqlDumpCmd);
        }

        //compress dump and remove sql file
        $compressDumpCmd = sprintf('cd %s && tar cvzf %s %s && rm %s', $tmpDir, $compressFilename, $filename, $filename);
        $output->writeln($compressDumpCmd);
        if ($force) {
            $ssh->exec($compressDumpCmd);
        }

        //download dump file, extract
        $downloadFileCmd = sprintf('scp %s@%s:%s/%s %s/%s && cd %s && tar xvzf %s', $username, $remoteHost, $tmpDir, $compressFilename, $localTmpDir, $compressFilename, $localTmpDir, $compressFilename);
        $output->writeln($downloadFileCmd);
        if ($force) {
            exec($downloadFileCmd);
        }

        if ($force) {
            //drop database
            $forceInput = new ArrayInput(array(
               '--force' => null,
            ));
            $cmd = $this->getapplication()->find('doctrine:database:drop');
            $cmd->run($forceInput, $output);

            //create database
            $normalInput = new ArrayInput(array());
            $cmd = $this->getapplication()->find('doctrine:database:create');
            $cmd->run($normalInput, $output);
        }

        //import db
        $importDbCmd = sprintf('mysql -h %s -u %s -p%s %s < %s/%s', $localHost, $localUser, $localPassword, $localDbname, $localTmpDir, $filename);
        $output->writeln($importDbCmd);
        if ($force) {
            exec($importDbCmd);
        }

        //cleanup
        $localCleanupCmd = sprintf('rm %s/%s %s/%s', $localTmpDir, $filename, $localTmpDir, $compressFilename);
        if ($force) {
            exec($localCleanupCmd);
        }
        $remoteCleanupCmd = sprintf('rm %s/%s %s/%s', $tmpDir, $filename, $tmpDir, $compressFilename);
        if ($force) {
            $ssh->exec($remoteCleanupCmd);
        }

        $io->success('Database imported');
    }

    protected function generateId()
    {
        return uniqid(time());
    }

    protected function generateMysqlDumpCmd($host, $user, $password, $dbname, $tmpDir, $filename)
    {
        return sprintf('mysqldump -h %s -u %s -p%s %s > %s/%s',
            $host, $user, $password, $dbname, $tmpDir, $filename);
    }

    protected function generateDumpFilename($dbname)
    {
        $id = $this->generateId();

        return sprintf('db_%s_%s.sql', $dbname, $id);
    }

    protected function getContainerParameter($server, $key)
    {
        return $this->getContainer()->getParameter(sprintf('akhann_import_db.%s.%s', $server, $key));
    }
}
