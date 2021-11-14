# Tic-Tac-Toe backend 

Backend of a Tic-Tac-Toe game [https://en.wikipedia.org/wiki/Tic-tac-toe](https://en.wikipedia.org/wiki/Tic-tac-toe)

It's a simple application written on top of Lumen PHP Framework in order to gain advantage of its powerful routing and caching.

As you can see in Routing section the game creation needs a HTTP POST request at the /game path, without paremeters. 
It returns a json with the game unique identifier, like the following:

```json
{"id":"7293dbeaca2c0049d791d859a4999351"}
```

With the game unique identifier it's possible to start playing with a series of HTTP PUT requests at the /move path. This enpoint needs four parameters, in this order:

* Game unique identifier
* Player number. Only 1 and 2 are accepted values, and the first move should be from player 1
* Position X. Allowed values are (0,1,2)
* Position Y Allowed values are (0,1,2)

The position where to put the player flag is obtained by the intersection of X and Y on the board data structure, that is a 3x3 matrix like the following

```code
   0 1 2 
   
0   | |
   -----
1   | |
   -----
2   | |
```

For example this request /move/7293dbeaca2c0049d791d859a4999351/1/1/2 will generate the following move for the player 1:

```code
   0 1 2 
   
0   | |
   -----
1   | |O
   -----
2   | |
```

The returned json includes a data structure with the representation of the full board, and the indication of the next player that can move:

```json
{"board":[[0,0,0],[0,0,1],[0,0,0]],"next":2}
```

When a player wins, or the game is a drawn, the endpoint comunicate it to the client with the winner pameter, valorized with the player number, or -1 in case of draw.

```json
{"board":[[1,0,2],[2,2,0],[1,1,1]],"next":2,"winner":1} 
```

In case of error they are notified to the client with the err and msg parameters, following an exampler:

```json
{"board":[[0,0,0],[0,0,0],[0,0,0]],"next":1,"err":3,"msg":"Unexpected player"} 
```

## How to run it

To run this demo Docker is required, and you have to follow the next steps:

### Clone this repo

```bash
git clone https://github.com/g4b0/tictactoe.git
cd tictactoe
```

### Build and run it

```bash
docker-compose up --build --detach
```

Now Tic-Tac-Toe backend is listening on localhost:8000. If port 8000 is already used in your environment you can change it in docker-compose.yml


### Run unit tests

```bash
docker exec -it <CONTAINER-NAME> vendor/bin/phpunit
```

### Run a complete game

```bash
docker exec -it <CONTAINER-NAME> php artisan run:game
```

## Code organization

### Routing 

The two endpoints (game and move) are declared in routes\web.php

```php
$router->post('/game', 'GameController@create');
$router->put('/move/{id}/{player}/{x}/{y}', 'GameController@move');
```

### Controller

Controller logic is handled in app\Http\Controllers\GameController.php, a standard Lumen controller with two functions the handle respectively the game and move endpoints.

### Business logic

The business logic is fitted into a class rappresenting the game's board: app\Board.php

### Game state 

The game state is stored through the Lumen's Cache Facade, that by default is configured to use the file cache driver, which stores the serialized, cached objects on the server's filesystem.

### Tests

A small test suite is provided, that checks only the healt of the two enpoints. All tests are coded in tests\GameControllerTest.php

### FE simulator

An artisan console command is provided in order to simulate full games. It resides in app\Console\Commands\RunGameCommand.php.
By changing the $move property you can configure and test different games

