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
        if ($this->request['event']['type'] == "message") {
            $rawText=$this->request['event']['text'];
            $parsedText=$this->parseText($rawText);
            if($parsedText) {
                $matchea=[];
                preg_match("/[\w+]/i", $parsedText['username'], $matches);
               $result=$this->addToGitlab($matches[0]);
              if ($result) {}
        }
}
        /*if ($this->request['event']['type'] == "message") {
            $userId = $this->request['event']['user'];
            if ($this->request['event']['subtype'] == "channel_join") {

                $options = array(
                    "Hey there, <@$userId>! Welcome to the Hotels.ng remote internship Slack team. I'm hibot, your friendly neighbourhood bot.\nHere's everything you need to know to get up and running :point_down:\nhttps://sites.google.com/hotels.ng/internship/home\nGreat to have you here. We'e gonna have lots of ~fun~ coding/design together!",
                    "Hi, <@$userId>! Welcome to the Hotels.ng remote internship Slack team.\nGot any questions? Go here first :point_right: https://sites.google.com/hotels.ng/internship/home\nThe name's hibot. Peace!",
                    "Welcome to the Hotels.ng remote internship Slack team, <@$userId>!\nCheck out :point_right: https://sites.google.com/hotels.ng/internship/home for how to get started.\nMy name's hibot. Pleased to meet you. :wave:"
                );
                $data = array(
                    "team_id" => $this->request['team_id'],
                    "channel" => $this->request['event']['channel'],
                    "text" => $options[array_rand($options)]
                );

                //respond

                $response = $this->respond($data);
            }
        }*/

    }


    public function parseText($text)
    {
        $tokens = explode(' ', $text);
        $botUserId=Credential::where('team_id', $this->request['team_id'])->get()->first()->bot_user_id;
        if (in_array("<@$botUserId>", $tokens)) {
            if (!empty(preg_grep("/add/i", $tokens)) || (!empty(preg_grep("/gitlab/i", $tokens))) || (!empty(preg_grep("/username/i", $tokens)))) {
                return array(
                    'type' => 'add-gitlab',
                    'username' => preg_grep("/@-[\w+]/i", $tokens)[0]
            }/* else if ($tokens[1] == "find" || $tokens[1] == "search") {
                return array(
                    'type' => 'search',
                    'query_terms' => array_slice($tokens, 2));
            }*/
          }
return null;
        }
return null;
    }


    /**
     * Posts responses to Slack
     */
    public function respond(array $data)
    {
        $client = new Client();
        $response = $client->request('GET', 'https://slack.com/api/chat.postMessage',
            array(
                'query' => [
                    'token' => Credential::where('team_id', $data['team_id'])->first()->bot_access_token,
                    'channel' => $data['channel'],
                    'text' => $data['text']
                ]
            ));
        return json_decode($response->getBody(), true);
    }

public function addToGitlab ($username) {
    $client = new \Gitlab\Client('http://git.yourdomain.com/api/v3/'); // change here $client->authenticate('your_gitlab_token_here', \Gitlab\Client::AUTH_URL_TOKEN); // change here 

//get user's Gitlab user id

//add user to project
$project = new \Gitlab\Model\Project(1, $client);
$user=project->addMember($userId, $accessLevel);
}

}
