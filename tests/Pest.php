<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific
| TestCase class. By default, that class is "PHPUnit\Framework\TestCase". Of
| course, you may need to change it using the "uses()" function to bind a
| different class or a trait to your test.
|
*/

uses(
    HostUK\TreesForAgents\Tests\TestCase::class
)->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain
| conditions. The "expect()" function gives you access to a set of "expectations"
| that you can use to assert different things. Of course, you may extend the
| Expectation API at any time.
|
*/

expect()->extend('toBeAgent', function () {
    return $this->toBeInstanceOf(\HostUK\TreesForAgents\Support\AgentIdentity::class)
        ->and($this->value->isAgent())->toBeTrue();
});

expect()->extend('toNotBeAgent', function () {
    return $this->toBeInstanceOf(\HostUK\TreesForAgents\Support\AgentIdentity::class)
        ->and($this->value->isNotAgent())->toBeTrue();
});

expect()->extend('toBeProvider', function (string $provider) {
    return $this->toBeAgent()
        ->and($this->value->provider)->toBe($provider);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code
| specific to your project that you don't want to repeat in every file. Here
| you can also expose helpers as global functions to help you reduce the
| number of lines of code in your test files.
|
*/

function createRequest(array $headers = [], ?string $userAgent = null): Illuminate\Http\Request
{
    $request = Illuminate\Http\Request::create('/');

    if ($userAgent !== null) {
        $request->headers->set('User-Agent', $userAgent);
    }

    foreach ($headers as $key => $value) {
        $request->headers->set($key, $value);
    }

    return $request;
}
