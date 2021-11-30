<?php

namespace App;

class Board
{
    private array $status = [];
    private int $next = 1;
    private int $winner = 0;
    private array $mate = [];
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
     * Given a player, returns the other
     *
     * @param integer $player
     * @return integer
     */
    private function getOtherPlayer(int $player): int
    {
        return match($player) {
            1 => 2,
            2 => 1
        };
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
        if (!$this->isValid($player, $x, $y)) {
            return false;
        }

        $this->setMove($player, $x, $y);
        $this->calcStatus($player);

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
    private function isValid(int $player, int $x, int $y) :bool
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
     * Write the move into the board
     * 
     * @param int $player
     * @param int $x
     * @param int $y
     */
    private function setMove(int $player, int $x, int $y)
    {
        $this->status[$x][$y] = $player;
    }

    /**
     * Check if a square is ownewd by a player
     *
     * @param integer $player
     * @param integer $x
     * @param integer $y
     * @return integer 1 if the player owns the square
     *                -1 if the opponent owns the square
     *                 0 if the square if free
     */
    private function checkSquare(int $player, int $x, int $y) : int
    {
        $other = $this->getOtherPlayer($player);
        return match($this->status[$x][$y]) {
            $player => 1,
            $other => -1,
            default => 0
        };
    }

    /**
     * Set the board status.
     * 
     * Checkmate edge case, the opponent (O) can win in the next move, 
     * so it's not a checkmate:
     * 
     *  O| |X
     *  -----
     *   |O|X
     *  -----
     *  O| |
     * 
     * Mate edge case, the player (X) move seems like a mate, but the 
     * next opponent (O) move can win:
     * 
     * O| |X
     * -----
     * X|X|
     * -----
     * O| |O
     * 
     * @param integer $player
     * @param integer $x
     * @param integer $y
     * @return void
     */
    private function calcStatus(int $player): void
    {
        $this->next = $this->getOtherPlayer($player);
        $this->winner = 0;
        $this->mate = [1=>0,2=>0];

        $possibleDraw = true;
        $cntDiagLr = [1=>0,2=>0];
        $cntDiagRl = [1=>0,2=>0];

        for($x=0; $x<3; $x++) {

            $cntRow = [1=>0,2=>0];
            $cntCol = [1=>0,2=>0];

            for($y=0; $y<3; $y++) {
                if (0 === $this->status[$x][$y]) {
                    $possibleDraw = false;
                }
                $cntRow[$player] += $this->checkSquare($player, $x, $y);
                $cntCol[$player] += $this->checkSquare($player, $y, $x);
                $cntRow[$this->next] += $this->checkSquare($this->next, $x, $y);
                $cntCol[$this->next] += $this->checkSquare($this->next, $y, $x);
            }

            if (3 === $cntRow[$player] || 3 === $cntCol[$player]) {
                $this->winner = $player;
            } elseif (2 === $cntRow[$player] || 2 === $cntCol[$player]) {
                $this->mate[$this->next]++;
            }

            // Edge case,
            if (2 === $cntRow[$this->next] || 2 === $cntCol[$this->next]) {
                $this->mate[$this->next]--;
            }

            $cntDiagLr[$player] += $this->checkSquare($player, $x, $x);
            $cntDiagRl[$player] += $this->checkSquare($player, $x, 2-$x);
            $cntDiagLr[$this->next] += $this->checkSquare($this->next, $x, $x);
            $cntDiagRl[$this->next] += $this->checkSquare($this->next, $x, 2-$x);
        }   

        if (3 === $cntDiagLr[$player] || 3 === $cntDiagRl[$player]) {
            $this->winner = $player;
        } elseif (2 === $cntDiagLr[$player] || 2 === $cntDiagRl[$player]) {
            $this->mate[$this->next]++;
        }

        // Edge case,
        if (2 === $cntDiagLr[$this->next] || 2 === $cntDiagRl[$this->next]) {
            $this->mate[$this->next]--;
        }

        if ($possibleDraw && 0 === $this->winner) {
            $this->winner = -1; 
        }
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
        if (array_sum($this->mate)>0) {
            $ret = array_merge($ret, [
                'mate' => $this->mate,
            ]);
        }
        return json_encode($ret);
    }

}