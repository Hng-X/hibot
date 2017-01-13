<?php

namespace App\Jobs;

use App\Models\Credential;
use App\Models\Link;
use App\Models\LinkTag;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class HandleSlackEvent implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $request;

    /**
     * Create a new job instance.
     *
     * @param array $request
     */
    public function __construct(array $request)
    {
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //determine what kind of message this is: add, search, or not for us
        $rawText = $this->request['event']['text'];
        $parsedText = $this->getMessageTypeAndParseText($rawText);

        $data = [];
        if ($parsedText['type'] == 'add') {
            //add link to db
            $url = $this->sanitizeAndVerifyUrl($parsedText["link"]);
            if ($url) {
                $attributes = array(
                    "team_id" => $this->request['team_id'],
                    "url" => $url,
                    "user_id" => $this->request['event']['user'],
                    "channel_id" => $this->request['event']['channel'],
                    "title" => $this->getTitle($url)
                );
                $link = Link::create($attributes);

                //respond
                $teamLinksUrl = "https://slack.com/oauth/authorize?scope=identity.basic,identity.email,identity.team&client_id=104593454705.107498116711&redirect_uri=http://linxer.herokuapp.com/Auth/signin";
                $data['text'] = "Done! :+1: See all your team's links <$teamLinksUrl|here>. ";
                $data['channel'] = $this->request['event']['channel'];
                $data['team_id'] = $this->request['team_id'];
                $data['response_type'] = "saved";

                $response = $this->respond($data);
                Log::info("Received add response:" . print_r($response, true));
            }
        }
        elseif ($parsedText['type'] == 'search') {
            //check if the tag corresponds to any link for the particular team
            
            $tag_term0 = $parsedText['query_terms'];
            $tag_term = implode("", $tag_term0);
        

            $team = $this->request['team_id'];
            $check = Link::where('title','=', $tag_term) 
                        ->get();
                        //where('team_id',$team)
                        //searching by title for now
            
            if($check) {
                $num = count($check);
                if($num > 0) {
                    ($num == 1) ? $num_link = 'link' : $num_link = 'links';

                    //$get_links = [];
                    $sn = 1;

                    $links = "";
                    foreach ($check as $link) {
                        //$output_text["body"] = "$sn <$link->url|$link->title>\n";
                        $links .= "$sn <$link->url|$link->title>\n";
                        //array_push($output_text['body'], $content);
                        $sn++;
                    }

                    $teamName = "";
                    $teamLinksUrl = "https://slack.com/oauth/authorize?scope=identity.basic,identity.email,identity.team&client_id=104593454705.107498116711&redirect_uri=http://linxer.herokuapp.com/Auth/signin";

                    $output_text = "yo! i got `$num` $num_link on *$tag_term* \n\n $links \n\n See all your team's links <$teamLinksUrl|here>";               
                }
                else {
                   // $outputtext = "Oga, i no see *$tag_term* for here o!";
                    $output_text = "Oga, i no see am for here o!"; //*$tag_term*                                
                }
            
                //respond                
                $data['text'] = $output_text;
                $data['channel'] = $this->request['event']['channel'];
                $data['team_id'] = $this->request['team_id'];
                $data['response_type'] = "saved";

                $response = $this->respond($data);
                Log::info("Received search response:" . print_r($response, true));            
            }
        }
    }


    public function getMessageTypeAndParseText($text)
    {
        $tokens = explode(' ', $text);
        $botUserId=Credential::where('team_id', $this->request['team_id'])->get()->first()->bot_user_id;
        if ($tokens[0] == "<@$botUserId>") {
            if ($tokens[1] == "add" || $tokens[1] == "save") {
                return array(
                    'type' => 'add',
                    'link' => trim($tokens[2], "<>"),
                    'tags' => array_slice($tokens, 3));
            } else if ($tokens[1] == "find" || $tokens[1] == "search") {
                return array(
                    'type' => 'search',
                    'query_terms' => array_slice($tokens, 2));
            }
        }
    }

    private function sanitizeAndVerifyUrl($text)
    {
        $text = filter_var($text, FILTER_SANITIZE_URL);
        return filter_var($text, FILTER_VALIDATE_URL);
    }

    private function getTitle($url)
    {
        try {
        $str = file_get_contents($url);
        if (strlen($str) > 0) {
            $str = trim(preg_replace('/\s+/', ' ', $str)); // supports line breaks inside <title>
            preg_match("/\<title\>(.*)\<\/title\>/i", $str, $title); // ignore case
            return $title[1];
        }
       } catch (ErrorException $e) {
        return parse_url($url)["host"];
        }
    }

    /**
     * Posts responses to Slack
     */
    public function respond(array $data)
    {
        //if ($data['response_type'] == 'saved')
        $client = new Client();
        $response = $client->request('GET', 'https://slack.com/api/chat.postMessage',
            ['query' => [
                'token' => Credential::where('team_id', $data['team_id'])->first()->bot_access_token,
                'channel' => $data['channel'],
                'text' => $data['text']
            ]
            ]);
        return json_decode($response->getBody(), true);
    }

}
