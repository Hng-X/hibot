<?php

namespace Hibot\Http\Controllers;

use Hibot\Models\Link;

/**
 * Handles requests through the browser ie at the live site
 */
class WebController extends Controller
{
    public function viewLinks($teamSlug)
    {

        $team = explode('-', $teamSlug);
        $links = Link::where('team_id', $team[0])->get();
        return view("listing", ["links" => $links, "teamName" => $team[1]]);
    }

    /** Retrieves all a team's links
     *
     * @param $team The id of the team
     * @param $queryString The query string
     */
    public function getAllLinks($team)
    {

    }

    /** Searches for a team's links matching a given search query
     *
     * @param $team The id of the team
     * @param $queryString The query string
     */
    public function search($team, $queryString)
    {
//parse query string
//perform search

    }
}
