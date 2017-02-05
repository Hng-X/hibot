<?php

namespace App\Bot\Actions;

use App\Slack\SlackMessage;
use Gitlab\Api\Projects;
use Gitlab\Api\Users;
use Illuminate\Support\Facades\Log;

class Gitlab extends Action
{

    public function run()
    {
        return $this->addToGitlab($this->data["username"], $this->data["project"]);
    }

    protected function addToGitlab($username, $projectName = "getting-started")
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
            return null;
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

    public function respond($result)
    {
        $this->sendGitlabAddResult($result, $this->data["project"], $this->request);
    }

    protected function sendGitlabAddResult($result, $project, array $data)
    {
        $team = $data['team_id'];
        $user = $data['event']['user'];
        $channel = $data['event']['channel'];

        if ($result != null && !isset($result["message"])) {
            $text = "Added to Gitlab project: $project! <@$user>";
            $message = new SlackMessage($team, $channel, $text);
            return $message->send();
        } else {
            $text = "Sorry, I couldn't add you to *$project*, <@$user>. Please ensure you're signed up on gitlab.com, and that you posted your correct gitlab username (including capitalisation, if neccessary), then try again. Also be sure you typed the correct project name.";
            $message = new SlackMessage($team, $channel, $text);
            return $message->send();
        }
    }
}