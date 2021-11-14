<?php

namespace App;

class Board
{
    private array $status = [];
    private int $next = 1;
    private int $winner = 0;
    private int $err = 0;
    private string $msg = '';
        
    public function __construct() {
        $this->initStatus();
    }

    /**
     * Initialize an empty board
     * @return void
     */
    private function initStatus() :void
    {
        $this->status = [];
        for($i=0;$i<=2;$i++){
            $this->status[$i] = [];
            for($j=0;$j<=2;$j++){
                $this->status[$i][$j] = 0;
            }
        }
    }

    /**
     * Put a move
     * 
     * @param int $player
     * @param int $x
     * @param int $y
     * 
     * @return bool
     */
    public function move(int $player, int $x, int $y) :bool
    {
        if (!$this->check($player, $x, $y)) {
            return false;
        }

        $this->status[$x][$y] = $player;
        $this->next = match($this->next) {
            1 => 2,
            2 => 1
        };

        $this->winner = $this->checkEnd();
        return true;
    }
   
    /**
     * Check if a move is valid
     * setting internal error status if not
     * 
     * @param int $player
     * @param int $x
     * @param int $y
     * 
     * @return bool
     */
    private function check(int $player, int $x, int $y) :bool
    {
        $this->err=0;
        $this->msg='';

        if (0 !== $this->winner) {
            $this->err = 2;
            $this->msg = 'Game already terminated';
            return false;
        }

        if ($player !== $this->next) {
            $this->err = 3;
            $this->msg = 'Unexpected player';
            return false;
        }

        if ($x<0 || $x>2 || $y<0 || $y>2) {
            $this->err = 4;
            $this->msg = 'Illegal move';
            return false;
        }

        if (0 !== $this->status[$x][$y]) {
            $this->err = 5;
            $this->msg = 'Position already taken';
            return false;
        }

        return true;
    }

    /**
     * Check if a game has reachd the end.
     * 
     * @return int possible values are:
     *      0: the game is not ended
     *      1: player 1 is the winner
     *      2: player 2 is the winner
     *      -1: the game is a draw
     */
    private function checkEnd() :int
    {
        foreach([1,2] as $player) {
            
            foreach($this->status as $x => $row) {
                // row
                if ($this->status[$x][0] === $player && $this->status[$x][1] === $player && $this->status[$x][2] === $player) {
                    return $player;
                }

                foreach ($row as $y => $val) {
                    // col
                    if ($this->status[0][$y] === $player && $this->status[1][$y] === $player && $this->status[2][$y] === $player) {
                        return $player;
                    }
                }
            }
            // diag
            if ($this->status[0][0] === $player && $this->status[1][1] === $player && $this->status[2][2] === $player) {
                return $player;
            }
            if ($this->status[0][2] === $player && $this->status[1][1] === $player && $this->status[2][0] === $player) {
                return $player;
            }

        }

        // draw
        $drawn = true;
        for($i=0;$i<=2;$i++){
            for($j=0;$j<=2;$j++){
                if ($this->status[$i][$j] === 0) {
                    $drawn = false;
                    continue 2; // dirty, but helps performance
                }
            }
        }
        return $drawn ? -1 : 0;
    }

    /**
     * Transform the board in a JSON string
     * 
     * @return string
     */
    public function __toString() :string
    {
        $ret = [
            'board' => $this->status,
            'next' => $this->next
        ];
        if ($this->err>0) {
            $ret = array_merge($ret, [
                'err' => $this->err,
                'msg' => $this->msg,
            ]);
        }
        if ($this->winner !== 0) {
            $ret = array_merge($ret, [
                'winner' => $this->winner,
            ]);
        }
        return json_encode($ret);
    }

}