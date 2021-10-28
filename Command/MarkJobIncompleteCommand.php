<?php

namespace JMS\JobQueueBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use JMS\JobQueueBundle\Entity\Job;
use Symfony\Component\Console\Command\Command;
use JMS\JobQueueBundle\Entity\Repository\JobManager;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;

class MarkJobIncompleteCommand extends Command
{
    protected static $defaultName = 'jms-job-queue:mark-incomplete';

    private $em;
    private $jobManager;

    public function __construct(EntityManagerInterface $em, JobManager $jobManager)
    {
        parent::__construct();

        $this->em = $em;
        $this->jobManager = $jobManager;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Internal command (do not use). It marks jobs as incomplete.')
            ->addArgument('job-id', InputArgument::REQUIRED, 'The ID of the Job.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Job|null $job */
        $job = $this->em->createQuery("SELECT j FROM ".Job::class." j WHERE j.id = :id")
            ->setParameter('id', $input->getArgument('job-id'))
            ->getOneOrNullResult();

        if ($job === null) {
            $output->writeln('<error>Job was not found.</error>');

            return 1;
        }

        $this->jobManager->closeJob($job, Job::STATE_INCOMPLETE);

        return 0;
    }
}