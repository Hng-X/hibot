<?php

if (!function_exists("bot_action")) {
    function bot_action(\Hibot\Bot\Actions\Action $action)
    {
        $result = $action->run();
        if (method_exists($action, "respond")) {
            $action->respond($result);
        }
    }
}