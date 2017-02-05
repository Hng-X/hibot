<?php

if (!function_exists("bot_action")) {
    function bot_action(\App\Actions\Action $action)
    {
        $result = $action->run();
        if (method_exists($action, "respond")) {
            $action->respond($result);
        }
    }
}