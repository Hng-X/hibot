<?php
namespace App\Http\Controllers;

use App\Http\Middleware\EventsMiddleware;
use App\Jobs\HandleSlackEvent;
use App\Models\Credential;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BotController extends Controller
{
    /**
     * Ensures the app has been verified and the token is correct
     */
    function __construct()
    {
        $this->middleware(EventsMiddleware::class);
    }

    /**
     * Handles events received from Slack
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function receive(Request $request)
    {
        $data = $request->all();
        dispatch(new HandleSlackEvent($data));
        return response('Ok', 200);
    }

    public function test()
    {
        $data = [];
        $data['text'] = "Hi guys... @channel :blush:";
        $data['channel'] = "#bot-testing";
        $data['response_type'] = "saved";
        $data['team_id'] = "T32HFDCLR";

        $response = $this->respond($data);
        if ($response['ok'] === true) {
            return view('Auth/add', ['result' => "OK"]);
        } else return view('Auth/add', ['result' => $response['error']]);
    }

    /**
     * Posts responses to Slack
     */
    public function respond(array $data)
    {
        //if ($data['response_type'] == 'saved')
        $client = new Client();
        $response = $client->request('GET', 'https://slack.com/api/chat.postMessage',
            ['query' => [
                'token' => Credential::where('team_id', $data['team_id'])->first()->bot_access_token,
                'channel' => $data['channel'],
                'text' => $data['text']
            ]
            ]);
        return json_decode($response->getBody(), true);
    }

    public function events(Request $request)
    {
        if ($request->input("type") == "url_verification") {
            return response($request->input("challenge"), 200);
        }
        
        //return $next($request);
    }

}
