<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Enums\XMLEnum;
use App\Services\GoogleSheetClient;
use Google\Client as GoogleClient;

class RunXML extends Command
{
    protected $options = XMLEnum::OPTIONS;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xml:upload {filename} {type?}  {api_config?*}';

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
        echo implode(',', []);
        $client = new GoogleClient();
        $google = new GoogleSheetClient($client);
        $abc = $google->createTitle();
        print_r($abc);
        $file = $this->argument('filename');
        $xmlObject = simplexml_load_string(file_get_contents($file), 'SimpleXMLElement', LIBXML_COMPACT | LIBXML_PARSEHUGE|LIBXML_NOCDATA);
        $json = json_decode(json_encode($xmlObject, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
        foreach($json as $key => $value){
            foreach($value as $newKey => $newValue){
                foreach($newValue as $hhh){
                    //print_r($hhh);
                }

            }
        }
    }
}
