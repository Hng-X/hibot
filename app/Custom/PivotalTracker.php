<?php

namespace App\Custom;

use App\Slack\SlackMessage;
use Illuminate\Support\Facades\Log;

class PivotalTracker
{

    public static function addToPivotal($email, $projId = "1961795")
    {
        Log::info("email:".$email);

        $ch = curl_init();
        $cookieFile = "cookie.txt";

        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $postfields = array(
        "email" => $email,
            "role" => "member"
        );

        curl_setopt($ch, CURLOPT_URL, "https://www.pivotaltracker.com/services/v5/projects/$projId/memberships");

        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postfields));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "X-TrackerToken: ".env("PIVOTAL_TRACKER_TOKEN"),
            "Content-Type: application/json"
        ));
        $resp = curl_exec($ch);
        Log::info("Request add: " . http_build_query($postfields));
        $resp = json_decode($resp, true);
        Log::info("Resp add: " . print_r($resp, true));
        return $resp;
    }

    public static function sendPivotalAddResult($result, array $data, $project="Factory_core")
    {
        $team = $data['team_id'];
        $user = $data['event']['user'];
        $channel = $data['event']['channel'];

        if ($result != null && array_key_exists("role", $result) && $result["role"] == "member") {
            $text = "Added to Pivotal project: $project! <@$user>";
            $message = new SlackMessage($team, $channel, $text);
        } else if ($result != null && array_key_exists("general_problem", $result) && preg_match("/is already a project member/", $result["general_problem"])) {
            $text = "Looks like you've already been added, <@$user>.";
            $message = new SlackMessage($team, $channel, $text);
        } else {
            $text = "Oops, something must have gone wrong, <@$user>.  Please make sure you've signed up on pivotaltracker.com, and try again.";
            $message = new SlackMessage($team, $channel, $text);
        }
        return $message->send();

    }
}