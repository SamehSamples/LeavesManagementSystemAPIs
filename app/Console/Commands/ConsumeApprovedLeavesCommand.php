<?php

namespace App\Console\Commands;

use App\Repository\Interfaces\EmployeeLeaveRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ConsumeApprovedLeavesCommand extends Command
{
    private EmployeeLeaveRepositoryInterface $repository;

    public function __construct(EmployeeLeaveRepositoryInterface $repository)
    {
        parent::__construct();
        $this->repository=$repository;
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'consume:approvedLeaves';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'change status of all approved leaves that has already started';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try{
            $count = $this->repository->updateLeaveStatus();
            info('Approved Leaves properly consumed - affected records count = ' . $count . ' - ' . Carbon::now()->toDayDateTimeString());
            return Command::SUCCESS;
        }catch(\Exception $ex){
            info('Error occurred while consuming approved leaves - ' . Carbon::now()->toDayDateTimeString());
            return Command::FAILURE;
        }
    }
}
