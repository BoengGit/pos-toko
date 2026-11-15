<?php

test('the root route redirects to dashboard', function () {
    $response = $this->get('/');

    $response->assertRedirect('/dashboard');
});
