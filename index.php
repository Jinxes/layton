<?php

$app->get->admin->user->show('/user/:id')->middleware(
    Jwt::class, ReloadToken::class
);

$app->group->api(function ($route) {
    // Show all projects
});