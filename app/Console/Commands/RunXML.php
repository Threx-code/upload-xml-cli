<?php

namespace App\Console\Commands;

use App\Contracts\XMLInterface;
use App\Http\Controllers\Api\XMLController;
use App\Http\Requests\XMLUploadRequest;
use App\Models\User;
use CURLFile;
use Illuminate\Console\Command;
use App\Enums\XMLEnum;
use App\Services\XMLService;
use Illuminate\Http\Request;

class RunXML extends Command
{
    protected array $options = XMLEnum::OPTIONS;
    protected static XMLInterface $repository;

    public function __construct(XMLInterface $repository)
    {
        self::$repository = $repository;
        parent::__construct();
    }


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
   protected $signature = 'xml:upload';

    /**
     * The console command description.
     * /var/www/html/coffee_feed.xml
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

        $url = $file = null;
        $type = strtolower($this->anticipate('type', ['local', 'remote']));
        $this->newLine();
        if(!in_array($type, XMLEnum::OPTIONS, true)){
            return $this->info('the type should be between [local or remote]');
        }
        if($type == 'local'){
            $file = $this->ask('enter the path to your xml file');
        }else{
            $url = $this->ask('enter valid url to your xml file');
            if(filter_var($url, FILTER_VALIDATE_URL) === false){
                return $this->info('invalid url entered');
            }
        }

        $token = $this->ask('enter the google token copied from the browser');

        $data = [
            'mode' => 'cli',
            'type' =>  $type,
            'url' => $url,
            'file' => $file,
            'token' => trim($token)
        ];

        $this->newLine();
        $this->info('Uploading your file');
        $result = self::$repository::uploadXMLFileToGoogleSheet($data);
        if(is_array($result)){
            if(in_array('success', $result, true)){
                $this->newLine();
                $this->info($result['success']);
            }else{
                print_r($result);
            }
        }
        else{
            $this->info($result);
        }

    }
}
