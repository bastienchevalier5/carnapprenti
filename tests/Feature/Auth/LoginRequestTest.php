<?php

namespace Tests\Unit\Requests\Auth;

use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LoginRequestTest extends TestCase
{

    public function test_it_authorizes_the_request()
    {
        $request = new LoginRequest;
        $this->assertTrue($request->authorize());
    }


    public function test_it_validates_required_email_and_password()
    {
        $inputs = ['email' => '', 'password' => ''];
        $request = new LoginRequest;
        $validator = Validator::make($inputs, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertEquals(trans('validation.required', ['attribute' => 'email']), $validator->errors()->first('email'));
        $this->assertEquals(trans('validation.required', ['attribute' => 'password']), $validator->errors()->first('password'));

    }


    public function test_it_validates_email_format()
    {
        $inputs = ['email' => 'invalid-email', 'password' => 'password123'];
        $request = new LoginRequest;
        $validator = Validator::make($inputs, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertEquals(trans('validation.email', ['attribute' => 'email']), $validator->errors()->first('email'));
            }

    public function test_it_ensures_request_is_not_rate_limited()
    {
        RateLimiter::shouldReceive('tooManyAttempts')->once()->andReturn(false);

        $request = new LoginRequest;
        $request->ensureIsNotRateLimited();

        $this->assertTrue(true);
    }


    public function test_it_throttles_when_rate_limited()
    {
        Event::fake();
        RateLimiter::shouldReceive('tooManyAttempts')->once()->andReturn(true);
        RateLimiter::shouldReceive('availableIn')->once()->andReturn(60);
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(trans('auth.throttle', [
            'seconds' => 60,
            'minutes' => ceil(60 / 60),
        ]));

        $request = new LoginRequest;
        $request->ensureIsNotRateLimited();

        Event::assertDispatched(Lockout::class);
    }


    public function test_it_generates_the_correct_throttle_key()
    {
        $request = LoginRequest::create('/login', 'POST', ['email' => 'test@example.com']);
        $request->setLaravelSession(app('session')->driver());
        $request->server->set('REMOTE_ADDR', '127.0.0.1');

        $this->assertEquals('test@example.com|127.0.0.1', $request->throttleKey());
    }
}
