<?php
/**
 * Created by PhpStorm.
 * User: J
 * Date: 24/01/2017
 * Time: 12:35
 */

namespace App\Custom;


use App\Slack\SlackMessage;
use Illuminate\Support\Facades\Log;


class Conjure {

    public static function addToConjure($email) {
        $ch = curl_init();
        $cookieFile = "cookie.txt";
        $loginUrl="https://hng.conjure.io/session/login";
        $addUrl="https://hng.conjure.io/projectusers/rest";
        $postfields = array(
     'email' => env("CONJURE_EMAIL"),
     'password' => env("CONJURE_PASSWORD")
 );
        $userAgent = "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.99 Safari/537.36";

        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_URL, $loginUrl);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));

        $resp = curl_exec($ch);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $addUrl="https://hng.conjure.io/users/rest";

        curl_setopt($ch, CURLOPT_URL, $addUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Accept: application/json, text/javascript, */*; q=0.01
Origin: https://hng.conjure.io
X-Requested-With: XMLHttpRequest
Content-Type: application/json
Referer: https://hng.conjure.io/p/621/factory
Accept-Encoding: gzip, deflate, br
Accept-Language: en-US,en;q=0.8"
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
            "email" => $email,
            "group" => 1,
            "name" => "",
            "status" => "pending")));

        $resp = curl_exec($ch);
        $resp = curl_exec($ch);
Log::info("Conjure response: $resp");


        return ($resp=="[\"User has already been added. To resent pending user's invite, click \"Edit\" then \"Resent Invite\"\"]");
    }

    public static function sendAddResult($success, array $data)
    {
        $team = $data['team_id'];
        $user = $data['event']['user'];
        $channel = $data['event']['channel'];

        if($success) {
            $text = "Added to Conjure! <@$user>";
            $message = new SlackMessage($team, $channel, $text);
            return $message->send();
        } else {
$text = "Couldn't add you to Conjure, <@$user>";
            $message = new SlackMessage($team, $channel, $text);
            return $message->send();
        }
    }

}