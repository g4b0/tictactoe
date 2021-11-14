<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

use App\Board;

class GameController extends Controller
{
    
    /**
     * Create a unique identifier and put a new board
     * in cache. 
     * 
     * Returns a JSON with the unique identifier
     * eg: {"id":"7293dbeaca2c0049d791d859a4999351"}
     */
    public function create()
    {
        $id = md5(uniqid(more_entropy:true));
        Cache::set($id, new Board());

        $content = json_encode(['id' => $id]);
        return response($content, 201)
                    ->header('Content-Type', 'application/json');
    }

    /**
     * Get the board from the cache, and try to put a move.
     * Put the updated board into the cache only if the move is succesful
     * 
     * @param string $id
     * @param int $player
     * @param int $x
     * @param int $y
     * 
     * Returns a JSON with the board status
     * eg: {"board":[[0,0,0],[0,1,0],[0,0,0]],"next":2}
     */
    public function move(string $id, int $player, int $x, int $y)
    {
        $board = Cache::get($id);

        if (null === $board) {
            $ret = [
                'err' => 1, 
                'msg' =>'Board not found'
            ];
            return response(json_encode($ret), 404)
                    ->header('Content-Type', 'application/json');
        }

        if ($board->move($player, $x, $y)) {
            Cache::set($id, $board);
        }

        return response($board, 200)
                    ->header('Content-Type', 'application/json');
    }

}
