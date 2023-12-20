<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Jalno\AAA\Http\Controllers\TypesController;
use Jalno\AAA\Http\Controllers\UsersController;

Route::prefix('v1')->middleware(['api', 'auth'])->group(function() {
	Route::get('user', fn() => [
		'data' => Auth::user(),
	]);

	$users = Route::apiResource('users', UsersController::class);
	if ($usersOnly = config('jalno-aaa.routes.users.only')) {
		$users->only($usersOnly);
	}
	if ($usersExcept = config('jalno-aaa.routes.paths.users.except')) {
		$users->except($usersExcept);
	}

	$types = Route::apiResource('types', TypesController::class);
	if ($typesOnly = config('jalno-aaa.routes.paths.types.only')) {
		$types->only($typesOnly);
	}
	if ($typesExcept = config('jalno-aaa.routes.paths.types.except')) {
		$types->except($typesExcept);
	}
});
