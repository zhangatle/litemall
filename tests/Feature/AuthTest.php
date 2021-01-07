<?php


namespace Tests\Feature;


use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AuthTest extends TestCase
{
    use DatabaseTransactions;

    public function testRegister()
    {
        $response = $this->post('wx/auth/register', ["username" => "zhangatle2", "password" => "123456", "mobile" => "13185808612", "code" => "1234"]);
        echo $response->content();
        $response->assertStatus(200);
        $result = $response->getOriginalContent();
        $this->assertEquals(0,$result['errno']);
        $this->assertNotEmpty($result['data']);
    }
}
