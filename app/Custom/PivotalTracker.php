<?php

namespace App\Custom;

use App\Slack\SlackMessage;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class PivotalTracker
{

    public static function addToPivotal($email, $projId = "1961795")
    {
        $client = new Client();
        Log::info("email:".$email);

        //add user to project
        $resp = $client->request("POST",
            "https://pivotaltracker.com/services/v5/projects/$projId/memberships?token=".env("PIVOTAL_TRACKER_TOKEN"),
            array(
                "form_params" => [
                    "email" => $email,
                    "role" => "member"],
                "headers" => [
                    "Content-Type: " => "application/json"]));
        $resp = json_decode($resp->getBody(), true);
        Log::info("Resp add: " . print_r($resp, true));
        return $resp;
    }

    public static function sendPivotalAddResult($result, array $data, $project="Factory_core")
    {
        $team = $data['team_id'];
        $user = $data['event']['user'];
        $channel = $data['event']['channel'];

        if ($result != null && $result["role"] == "member") {
            $text = "Added to Pivotal project: $project! <@$user>";
            $message = new SlackMessage($team, $channel, $text);
            return $message->send();
        } else {
            $text = "Sorry, I couldn't add you to *$project*, <@$user>. Please double-check your email and try again. ";
            $message = new SlackMessage($team, $channel, $text);
            return $message->send();
        }
    }
}