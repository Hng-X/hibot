<?php

namespace App\Custom;

use App\Slack\SlackMessage;
use Buzz\Exception\ClientException;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Log;

class PivotalTracker
{

    public static function addToPivotal($email, $projId = "1961795")
    {
        $client = new Client();
        Log::info("email:".$email);

        //add user to project
        try{
            $req = new Request("POST", "https://www.pivotaltracker.com/services/v5/projects/$projId/memberships",
                array("X-TrackerToken" => env("PIVOTAL_TRACKER_TOKEN"),
                    "Content-Type" => "application/json"),
            json_encode(array(
                "email" => $email,
                "role" => "member"
            )));
            Log::info("Request:_".$req->getBody());
            $resp = $client->send($req);
        } catch (ClientException $e) {
            Log::info("Resp add: " . $e->getMessage());
        }
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