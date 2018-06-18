<?php
/**
 * User: marcus-campos
 * Date: 15/06/18
 * Time: 15:49
 */

namespace Sympla\Search\Http\Controllers;

class DocumentationController extends Controller
{
    /**
     * DocumentationController constructor.
     */
    public function __construct()
    {
        $this->middlewareHandle();
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $storage = app('Storage');
        $docArray = json_decode($storage::disk('local')
            ->get('the-brick/doc.json'));

        $lastModified = date('d M Y', $storage::disk('local')
            ->lastModified('the-brick/doc.json'));

        if(!$docArray) {
            $docArray = [];
        }

        return view('negotiate::index', compact('docArray', 'lastModified'));
    }

    /**
     *
     */
    private function middlewareHandle()
    {
        $authMiddlware = config('the-brick-search.documentation.auth_middleware');
        if (!empty($authMiddlware)) {
            $this->middleware($authMiddlware);
        }
    }
}