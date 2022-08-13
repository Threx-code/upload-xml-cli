<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Enums\XMLEnum;


class RunXML extends Command
{
    protected $options = XMLEnum::OPTIONS;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
   // protected $signature = 'xml:upload {filename : xml file to be uploaded} {type? : the type if it\'s local or remote}  {api_config?*}';

    protected $signature = 'xml:upload';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //$this->options();
        //$this->option('filename');
        echo implode(',', [1,2,3,4,5]);

        //$file = $this->argument('filename');

    }
}
