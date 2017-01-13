<?php

namespace App\Http\Controllers;


use App\Models\Credential;
use GuzzleHttp\Client;

class AuthController extends Controller
{
    public function authorizeSlack()
    {
        $code = $_GET['code'];
        $client = new Client();
        $response = $client->request('GET', 'https://slack.com/api/oauth.access',
            array(
                'query' => [
                    'client_id' => env('SLACK_CLIENT_ID'),
                    'client_secret' => env('SLACK_CLIENT_SECRET'),
                    'code' => $code
                ]
            ));
        $response = json_decode($response->getBody(), true);
        if ($response['ok'] === true) {
            if (isset($response['access_token'])) {
                $credential = new Credential();
                $credential->access_token = $response['access_token'];
                $credential->team_id = $response['team_id'];
                $credential->bot_user_id = $response['bot']['bot_user_id'];
                $credential->bot_access_token = $response['bot']['bot_access_token'];
                $credential->save();
            }
            $result = "Authorized";
        } else {
            $result = $response['error'];
        }
        return view('Auth/add', ['result' => $result]);
    }

    /** Redirects user to teams links Page
    *
    *
    */
    public function redirectUsertoTeamLinks()
    {
        $code = $_GET['code'];
        $client = new Client();
        $response = $client->request('GET', 'https://slack.com/api/oauth.access',
            array(
                'query' => [
                    'client_id' => env('SLACK_CLIENT_ID'),,
                    'client_secret' => env('SLACK_CLIENT_SECRET'),
                    'redirect_uri' => 'http://linxer.herokuapp.com/Auth/signin',
                    'code' => $code
                ]
            )
        );
        $response = json_decode($response->getBody(), true);
        $team_id = $response['team']['id'];
        $access_token = $response['access_token'];
        if ($response['ok'] === true) {
            $interactUser = $client->request('GET', 'https://slack.com/api/users.identity?token=' . $access_token);
            $interactResponse = json_decode($interactUser->getBody()->getContents(), true);
            $teamId = $interactResponse['team']['id'];
            $teamName = $interactResponse['team']['name'];
            return redirect("/links/$teamId-$teamName");
        } else {
            $errorMsg = $response['error'];
        }
        return view('Auth/signin', ['result' => $errorMsg]);
    }
}
