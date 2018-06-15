<?php
/**
 * User: marcus-campos
 * Date: 15/06/18
 * Time: 15:42
 */

Route::group(['prefix' => 'documentation'], function () {
    Route::get('/', '\Sympla\Search\Http\Controllers\DocumentationController@index');
});