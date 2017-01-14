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
            $userId = $this->request['event']['user'];
            if ($this->request['event']['subtype'] == "channel_join") {

                $options = array(
                    "Hey there, @<$userId>! Welcome to the Hotels.ng remote internship Slack team. I'm hibot, your friendly neighbourhood bot.\nHere's everything you need to know to get up and running :point_right: https://sites.google.com/hotels.ng/internship/home\nGreat to have you here. We'e gonna have lots of ~fun~ coding/design together!",
                    "Hi, @<$userId>! Welcome to the Hotels.ng remote internship Slack team.\nGot any questions? Go here first :point_right: https://sites.google.com/hotels.ng/internship/home\nOops...Forgive my manners...my name's hibot, but my friends call me...hibot :stuck_out_tongue_winking_eye: I hope we'll be friends :blush:",
                    "Welcome to the Hotels.ng remote internship Slack team, @<$userId>!\nCheck out :point_right: https://sites.google.com/hotels.ng/internship/home to know how to get started.\nOh, and my name's hibot, but then...I'm just a bot :disappointed:"
                );
                $data = array(
                    "team_id" => $this->request['team_id'],
                    "channel" => $this->request['event']['channel'],
                    "text" => $options[array_rand($options)]
                );

                //respond

                $response = $this->respond($data);
            }
        }
            /*if ($this->request['event']['type'] == "team_join") {
                $firstName= isset($this->request['event']['user']["profile"]["first_name"]) ? $this->request['event']['user']["profile"]["first_name"] : "@<$userId>";

            }
        }*/
    }


    /*
     * Might be useful later

    public function parseText($text)
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
*/


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

}
