<?php

namespace App\Http\Controllers;

use App\Support\FormState;
use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * The answer to a form submit. A script-enhanced submit (X-Enhance header) gets the re-rendered form back and swaps
     * it in place; without JavaScript the state is flashed to the session and the visitor goes back to the page.
     */
    protected function formResponse(Request $request, string $name, string $view, FormState $state, array $data = [])
    {
        if ($request->header('X-Enhance')) {
            return response(view($view, ['state' => $state] + $data)->render());
        }
        session()->flash("forms.{$name}", $state->toArray());

        return redirect()->to(url()->previous().'#'.$name.'-form');
    }

    /** Success that moves on to another page (sign-in, new shipment...). The script follows the X-Redirect header. */
    protected function formRedirect(Request $request, string $url)
    {
        if ($request->header('X-Enhance')) {
            return response('', 204)->header('X-Redirect', $url);
        }

        return redirect()->to($url);
    }
}
