<?php

namespace dnj\AAA\Tests\Feature;

use dnj\AAA\Models\User;
use dnj\AAA\Rules\ModelExists;
use dnj\AAA\Tests\TestCase;

class ModelExistsRuleTest extends TestCase
{
    public function testExisting(): void
    {
        $model = User::factory()->create();
        $called = false;
        $rule = new ModelExists($this->getUserManager());
        $rule('user', $model->getId(), function ($message) use (&$called) {
            $called = true;
        });
        $this->assertFalse($called);
    }

    public function testNonExisting(): void
    {
        $called = false;
        (new ModelExists($this->getUserManager()))->__invoke('user', -1, function ($message) use (&$called) {
            $called = true;
        });
        $this->assertTrue($called);
    }

    public function testInvalidId(): void
    {
        $called = false;
        $rule = new ModelExists($this->getUserManager());
        $rule('user', [-1], function ($message) use (&$called) {
            $called = true;
            $this->assertStringContainsString('numeric or string', $message);
        });
        $this->assertTrue($called);
    }
}
