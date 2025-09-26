<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;
use Tests\Traits\WithChat;

class ChatControllerTest extends TestCase
{
    use RefreshDatabase, WithChat;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpWithChat();
    }

    public function test_it_returns_reverb_configuration(): void
    {
        $this->actingAs($this->client);

        $response = $this->getJson(route('api.v1.chat.reverb-config'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    'type',
                    'attributes' => [
                        'key',
                        'host',
                        'port',
                        'scheme',
                    ],
                ],
            ]);
    }

    public function test_all_chat_endpoints_require_authentication(): void
    {
        $routes = [
            ['GET', route('api.v1.chat.rooms.index')],
            ['POST', route('api.v1.chat.rooms.store')],
            ['GET', route('api.v1.chat.rooms.show', ['chatRoom' => $this->chatRoom])],
            ['GET', route('api.v1.chat.rooms.messages.index', ['chatRoom' => $this->chatRoom])],
            ['POST', route('api.v1.chat.rooms.messages.store', ['chatRoom' => $this->chatRoom])],
            ['POST', route('api.v1.chat.rooms.join', ['chatRoom' => $this->chatRoom])],
            ['DELETE', route('api.v1.chat.rooms.leave', ['chatRoom' => $this->chatRoom])],
            ['GET', route('api.v1.chat.reverb-config')],
        ];

        foreach ($routes as [$method, $url]) {
            $response = $this->json($method, $url);
            $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        }
    }
}
