<?php

it('shows the german product page instead of the laravel welcome screen', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Price Broker', false)
        ->assertSee('Filialen senden Preise', false)
        ->assertSee('/ready', false)
        ->assertSee('/admin', false)
        ->assertDontSee("Let's get started", false);
});

it('can switch the product page to english', function () {
    $this->get('/?lang=en')
        ->assertOk()
        ->assertSee('Stores submit prices', false)
        ->assertSee('Open admin', false);
});
