<?php
/**
 *
 * PHP version >= 7.0
 *
 * @category Console_Command
 * @package  App\Console\Commands
 */

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;

/**
 * Class deletePostsCommand
 *
 * @category Console_Command
 * @package  App\Console\Commands
 */
class RunGameCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "run:game";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Run a complete game";

    /**
     * The list of played moves. Each move is a set 
     * composed by 3 numbers:
     *  - the player (1 or 2)
     *  - the row (0, 1, 2)
     *  - the column (0, 1, 2)
     *
     * @var array
     */
    private $moves = [ // Win by 1
        [1,0,0],
        [2,1,1],
        [1,2,2],
        [2,0,2],
        [1,2,0],
        [2,1,0],
        [1,2,1],
    ];
    // private $moves = [ // Win by 2 - another move
    //     [1,0,0],
    //     [2,0,2],
    //     [1,1,1],
    //     [2,1,2],
    //     [1,2,0],
    //     [2,2,2],
    //     [1,0,1],
    // ];
    // private $moves = [ // Draw
    //     [1,0,0],
    //     [2,0,2],
    //     [1,2,0],
    //     [2,1,0],
    //     [1,2,2],
    //     [2,1,1],
    //     [1,1,2],
    //     [2,2,1],
    //     [1,0,1],
    // ];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $gameId = $this->create()?->id;
        echo "Game ID is $gameId\n\n";
        
        foreach ($this->moves as $move) {
            $board = $this->move($gameId, $move[0], $move[1], $move[2]);

            if (isset($board->board)) {
                $this->draw($board->board);
            }
            
            if (($board->err??0) > 0) {
                $this->error("Err: {$board->err} - {$board->msg}");
            } else {
                echo match(($board->winner??0)) {
                    1, 2 => "THE WINNER IS PLAYER {$board->winner}",
                    -1 => "GAME IS A DRAW",
                    0 => "GAME CAN CONTINUE"
                };
            }

            echo "\n\n";
        }        
    }

    /**
     * Call the creation endpoint, and returns the elaborated body
     * 
     * @return mixed
     */
    private function create() :mixed
    {
        $this->info("Creating a new game");

        $url = "http://host.docker.internal:8000/game";
        $this->warn("POST $url");

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $apiResponse = curl_exec($ch);

        return json_decode($apiResponse);
    }

    /**
     * Call the put move endpoint, and returns the elaborated body
     * 
     * @param string $id
     * @param int $player
     * @param int $x
     * @param int $y
     * 
     * @return mixed
     */
    private function move(string $id, int $player, int $x, int $y) :mixed
    {
        $this->info("Putting a move for player $player");

        $url = "http://host.docker.internal:8000/move/$id/$player/$x/$y";
        $this->warn("PUT $url");

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_PUT, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $apiResponse = curl_exec($ch);

        $this->info($apiResponse);

        return json_decode($apiResponse);
    }

    
    /**
     * Draw the boart to the console
     * 
     * @param array $board
     * 
     * @return void
     */
    private function draw(array $board) :void
    {
        echo "\n";
        foreach ($board as $rowNum => $row) {
            foreach ($row as $col => $val) {
                echo match($val) {
                    0 => ' ',
                    1 => 'O',
                    2 => 'X'
                };

                if ($col<2) {
                    echo "|";
                }
            }

            if ($rowNum<2) {
                echo "\n-----\n";
            }
        }
        echo "\n\n";
    }
    
}