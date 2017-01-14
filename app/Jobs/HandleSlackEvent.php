<?php

namespace App\Jobs;

use App\Models\Credential;
use Gitlab\Api\Projects;
use Gitlab\Api\Users;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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
            $rawText = $this->request['event']['text'];
            $parsedText = $this->parseText($rawText);
            if (isset($parsedText["type"])) {
                if ($parsedText["type"] == "gitlab-add") {
                    $result = $this->addToGitlab($parsedText["username"]);
                    if ($result) {
                        $user=$this->request['event']['user'];
                        $data = array(
                            "team_id" => $this->request['team_id'],
                            "channel" => $this->request['event']['channel'],
                            "text" => "Added to Gitlab! <@$user>"
                        );

                        $response = $this->respond($data);
                    }
                }
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
        $botUserId = Credential::where('team_id', $this->request['team_id'])->get()->first()->bot_user_id;
        if (preg_match("/<@$botUserId>/", $text)) {
            $matches = [];
            if (preg_match("/@-(\w+)/i", $text, $matches)) {
                return array(
                    'type' => 'gitlab-add',
                    'username' => $matches[1]
                );
            }
        }
        return [];
    }


    /**
     * Posts responses to Slack
     */
    public
    function respond(array $data)
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

    public function addToGitlab($username)
    {
        //authenticate
        $client = new \Gitlab\Client('http://gitlab.com.com/api/v3/'); // change here $client->authenticate('your_gitlab_token_here', \Gitlab\Client::AUTH_URL_TOKEN); // change here
        $client->authenticate(env('GITLAB_TOKEN'), \Gitlab\Client::AUTH_HTTP_TOKEN);

        //get user's Gitlab user id
        $api=new Users($client);
        $users=$api->search($username);
        Log::info("Obtained search results: ".print_r($users, true));
        /*
        $userId = "";
        foreach ($users as $user) {
            if($user["username"] == $username) {
                $userId=$user["id"];
                Log::info("Obtained user: ".print_r($user, true));
                break;
            }
        }

        //get project
        $api = new Projects($client);
        $projects = $api->accessible();
        $projId = "";
        foreach ($projects as $project) {
            if($project["weburl"] == "https://gitlab.com/hng-interns/getting-started"
            || $project["weburl"] == "http://gitlab.com/hng-interns/getting-started" ) {
                $projId=$project["id"];
                Log::info("Obtained user: ".print_r($project, true));
                break;
            }
        }
        if(!$projId) {
            throw new \ErrorException("couldnt get project");
        }

        //add user to project
        $project = new \Gitlab\Model\Project($projId, $client);
        $user = $project->addMember($userId, 30);
        Log::info("Result of add: " . print_r($user, true));*/
        return $user;
    }

}
