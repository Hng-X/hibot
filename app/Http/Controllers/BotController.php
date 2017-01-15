<?php
namespace App\Http\Controllers;

use App\Http\Middleware\EventsMiddleware;
use App\Jobs\HandleSlackEvent;
use Illuminate\Http\Request;

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

}
