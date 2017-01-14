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
            Log::info("Parsed: ".print_r($this->request, true));

            if (isset($this->request['event']['user'])) {
                $userId = $this->request['event']['user'];
            }
            if (!isset($this->request['event']['subtype'])) {
                $rawText = $this->request['event']['text'];
                $parsedText = $this->parseText($rawText);
                Log::info("Parsed: ".print_r($parsedText, true));
                if (isset($parsedText["type"])) {
                    if ($parsedText["type"] == "gitlab-add") {
                        $result = $this->addToGitlab($parsedText["username"], $parsedText["project"]);
                        if (!isset($result["message"])) {
                            $user = $this->request['event']['user'];
                            $data = array(
                                "team_id" => $this->request['team_id'],
                                "channel" => $this->request['event']['channel'],
                                "text" => "Added to Gitlab! <@$user>"
                            );
                        } else {
                            $user = $this->request['event']['user'];
                            $data = array(
                                "team_id" => $this->request['team_id'],
                                "channel" => $this->request['event']['channel'],
                                "text" => "Sorry, I couldn't add you, <@$user>. Please ensure you're signed up on gitlab.com, and that you posted your correct gitlab username (including capitalisation, if neccessary), then try again."
                            );
                        }
                        $response = $this->respond($data);

                    } else if ($parsedText["type"] == "gitlab-add") {
                        $user = $this->request['event']['user'];
                        $data = array(
                            "team_id" => $this->request['team_id'],
                            "channel" => $this->request['event']['channel'],
                            "text" => "Hey, <@$user>. looks like you're having trouble with gitlab. Have you been added to the project? If you aren't sure, please post a message here, telling me your username and the project. Hope this helps"
                        );
                    }
                }
            } else if ($this->request['event']['subtype'] == "channel_join") {
                $options = array(
                    "Hey there, <@$userId>! Welcome to the Hotels.ng remote internship Slack team. I'm hibot, your friendly neighbourhood bot.\nHere's everything you need to know to get up and running :point_down:\nhttps://sites.google.com/hotels.ng/internship/home\nGreat to have you here. We'e gonna have lots of ~fun~ coding/design together!",
                    "Hi, <@$userId>! Welcome to the Hotels.ng remote internship Slack team.\nGot any questions? Go here first :point_right: https://sites.google.com/hotels.ng/internship/home\nThe name's hibot. Peace!",
                    "Welcome to the Hotels.ng remote internship Slack team, <@$userId>!\nCheck out :point_right: https://sites.google.com/hotels.ng/internship/home for how to get started.\nMy name's hibot. Pleased to meet you. :wave:"
                );
                $data = array(
                    "team_id" => $this->request['team_id'],
                    "channel" => "$userId",
                    "text" => $options[array_rand($options)]
                );

                //respond

                $response = $this->respond($data);
            }
        }

    }


    public function parseText($text)
    {
        $botUserId = Credential::where('team_id', $this->request['team_id'])->get()->first()->bot_user_id;
        if (preg_match("/<@$botUserId>/i", $text)) {
            $matches = [];
            if (preg_match("/username\s*(:)|(is)\s*(\w+)/i", $text, $matches)) {
                $parsed = array(
                    'type' => 'gitlab-add',
                    'username' => $matches[2]
                );
                if (preg_match("/project\s*(:)|(is)\s*(\w+-?\w+)/i", $text, $matches)) {
                    $parsed["project"] = strtolower($matches[2]);
                } else $parsed["project"] = "getting-started";
                Log::info("Parsed: " . print_r($parsed, true));
                return $parsed;
            } else if(preg_match("/(\b404\b)/i", $text)
                || (stripos($text, "gitlab")!==false
                    && stripos($text, "error")!==false
                    && stripos($text, "access"))) {
                $parsed = array(
                    'type' => 'gitlab-problem-access'
                );
                return $parsed;
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

    public function addToGitlab($username, $projectName = "getting-started")
    {
        //authenticate
        $client = new \Gitlab\Client('http://gitlab.com/api/v3/'); // change here $client->authenticate('your_gitlab_token_here', \Gitlab\Client::AUTH_URL_TOKEN); // change here
        $client->authenticate(env('GITLAB_TOKEN'), \Gitlab\Client::AUTH_HTTP_TOKEN);

        //get user's Gitlab user id
        $api = new Users($client);
        $users = $api->search($username);

        $userId = "";
        foreach ($users as $user) {
            if ($user["username"] == $username) {
                $userId = $user["id"];
                break;
            }
        }

        //get project
        $api = new Projects($client);
        $projects = $api->accessible();
        $projId = "";
        foreach ($projects as $project) {
            if ($project["web_url"] == "https://gitlab.com/hng-interns/$projectName"
                || $project["web_url"] == "http://gitlab.com/hng-interns/$projectName"
            ) {
                $projId = $project["id"];
                break;
            }
        }
        if (!$projId) {
            throw new \ErrorException("couldnt get project");
        }

        //add user to project

        /*$resp = $api->addMember($projId, $userId, 30);
        Log::info("Result of add: " . print_r($resp, true));
        */

        $ch = curl_init();
        $cookieFile = "cookie.txt";

        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $postfields = array(
            'user_id' => $userId,
            'access_level' => 30
        );

        curl_setopt($ch, CURLOPT_URL, "https://gitlab.com/api/v3/projects/$projId/members");

        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "PRIVATE-TOKEN: " . env('GITLAB_TOKEN')
        ));
        $resp = curl_exec($ch);
        Log::info("Request add: " . http_build_query($postfields));
        $resp = json_decode($resp, true);
        Log::info("Resp add: " . print_r($resp, true));
        return $resp;
    }
}