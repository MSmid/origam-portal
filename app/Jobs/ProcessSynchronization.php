<?php

namespace App\Jobs;

use App\DataSource;
use App\Events\SynchronizationCompleted;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessSynchronization implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $datasource;
    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Datasource $datasource)
    {
        $this->datasource = $datasource;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      $ds = $this->datasource;
      $client = new Client();
      $url = env('ORIGAM_BASE_URL') . '/' . $ds->value('url');
      $promise = $client->getAsync($url)->then(
          function ($res) {
            return $res;
          },
          function($error) {
            return $error->getMessage();
          }
        );
      $response = $promise->wait();

      dd($response);
      // event(new SynchronizationCompleted($response, $ds->value('id')));
    }
}
