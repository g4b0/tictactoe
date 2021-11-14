<?php 
    
use Illuminate\Http\Response;

use App\Board;

use Illuminate\Support\Facades\Cache;

    
class GameControllerTest extends TestCase {


    /**
     * Test the game creation endpoint
     */
    public function testGameIsCreatedSuccessfully() {
        $response = $this->json('post', 'game')
            ->seeStatusCode(Response::HTTP_CREATED)
            ->seeJsonStructure(['id']);
    }

    /**
     * Test the put move endpoint
     */
    public function testFirstMoveOk() {

        $fakeHash = md5('fakeHash');

        Cache::shouldReceive('set')
                ->andReturn(true);

        Cache::shouldReceive('get')
                ->once()
                ->with($fakeHash)
                ->andReturn(new Board());

        $response = $this->json('put', "move/$fakeHash/1/1/1")
            ->seeStatusCode(200)
            ->seeJsonStructure(['board', 'next']);
    }
}