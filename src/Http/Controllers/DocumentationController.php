<?php
/**
 * User: marcus-campos
 * Date: 15/06/18
 * Time: 15:49
 */

namespace Sympla\Search\Http\Controllers;

class DocumentationController
{
    public function index()
    {
        $storage = app('Storage');
        $docArray = json_decode($storage::disk('local')
            ->get('the-brick/doc.json'));

        return view('negotiate::index', compact('docArray'));
    }
}