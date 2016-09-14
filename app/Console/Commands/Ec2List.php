<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use AWS;

class Ec2List extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'ec2:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display list of Ec2 instances';

    /**
     * The Ec2Client class instance.
     *
     * @var \Aws\Ec2\Ec2Client
     */
    protected $ec2client;

    /**
     * Display list of Ec2 instances
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->ec2client = AWS::createClient('ec2');
    } //  Ec2List :: __construct()

    /**
     * Display list of Ec2 instances
     *
     * @return mixed
     */
    public function handle()
    {
      $di = $this->ec2client->DescribeInstances();
      $filtered = $di->search(
        'Reservations[*].Instances[].{
          nickname:     Tags[*] | [?Key==`Name`].Value | [0],
          private_ip:   NetworkInterfaces[0].PrivateIpAddress,
          state:        State.Name,
          instance_id:  InstanceId
        }'
      );
      usort($filtered, 'self::compare_func');
//    dd($filtered);
      print "
Tag Name      Private IP    Status         Instance ID
------------------------------------------------------------
";
      foreach ($filtered as $i => $e)  {
        printf("%-14s%-14s%-11s%-20s\n",
          $e['nickname'],
          $e['private_ip'],
          $e['state'],
          $e['instance_id']);
      }
    } //  Ec2List :: handle()

    function compare_func($a, $b)  {
    return strcmp($a['nickname'], $b['nickname']);
  } //  Ec2List :: compare_func()

} //  class Ec2List
